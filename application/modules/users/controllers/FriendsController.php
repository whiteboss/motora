<?php

class Users_FriendsController extends Zend_Controller_Action
{
    
    protected $_user;

    public function preDispatch()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            //$user = $this->_helper->userProfile($auth->getIdentity()->id);
            $this->_user = $auth->getIdentity();
        } else {
            //throw new Exception("Вы не авторизованы");
            $this->_redirect( $this->view->url(array(''), 'signin') );
        }
    }

    public function init()
    {
        /* Initialize action controller here */
    }
    
    /**
     * Отправляет e-mail сообщение пользователю
     * @param Zend_Db_Table_Row $user
     */
    protected function sendRequestEmail($id, $user)
    {
            $who = $this->view->getUserByIdentity();            
            $link = 'http://' . $_SERVER['HTTP_HOST'] . $this->view->url(array('requestId' => $id), 'friendaccept'); //users/confirm/uid/' . $user->id;
            $mal = new Zend_Mail( 'UTF-8' );            
            //Уважаемый '.$user->lastname.' '.$user->firstname.', ссылка для подтверждения: <a href="' . $link .  '">' . $link . '</a>'
            $mal->setBodyHtml('
                    Hola, '.$user->username.'!
		    <br /><br />
		    <a href="' . $this->view->url(array('userId' => $who->id), 'profile') . '">' . $who->getFullName() . '</a> quiere hacer amigos con usted.
		    <br />
		    <a href="' . $link . '">Añadir como amigo</a>?<br />
		    <br /><br />
		    Atentamente, Qlick.cl<br />
		    <a href="http://www.qlick.cl/">http://qlick.cl<br />
		    ')
                    ->setFrom('no-reply@qlick.cl', 'Qlick.cl')
                    ->addTo($user->email)
                    ->setSubject('Pedido de amistad en Qlick.cl')
                    ->send();

    }   
    
    protected function sendAcceptEmail($user)
    {

            $who = $this->view->getUserByIdentity();
            $mal = new Zend_Mail( 'UTF-8' );
            //Уважаемый '.$user->lastname.' '.$user->firstname.', ссылка для подтверждения: <a href="' . $link .  '">' . $link . '</a>'
            $mal->setBodyHtml('
                    Hola, '.$user->username.'!
		    <br /><br />
		    <a href="' . $this->view->url(array('userId' => $who->id), 'profile') . '">' . $who->getFullName() . '</a> acepta su solicitud de amistad!
		    <br /><br />
		    Atentamente, Qlick.cl<br />
		    <a href="http://www.qlick.cl/">http://qlick.cl<br />
		    ')
                    ->setFrom('no-reply@qlick.cl', 'Qlick.cl')
                    ->addTo($user->email)
                    ->setSubject('Confirmación de amistad en Qlick.cl')
                    ->send();

    }    

    /**
     * Друзья пользователя
     * @return void
     */

    public function friendsAction()
    {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8' );

        $this->_helper->initInterface();
//        $this->view->headScript()
//                ->appendFile( '/js/jquery.mousewheel.js' )
//                ->appendFile( '/js/jquery.jscrollpane.min.js' );
        
        $request = $this->getRequest();

        $userId = (int) $this->_getParam("userId");
        // если идентификатора нет в параметре, значит это собственный юзер
        if ($userId == 0) $userId = $this->_user->id;
        $user = $this->_helper->userProfile($userId);
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Amigos'); 
        
        // EVENTS
        // right block
        $eventManager = new Events_Model_Table_Events();
        $items = array();
        $events = $eventManager->getComingEvents();
        if (count($events) > 5) {
            srand((float) microtime() * 10000000);
            $random_events = array_rand($events, 5);
            $items = array();
            foreach ($random_events as $key=>$event)
                $items[$key] = $events[$event];

            $this->view->r_events = $items;
            unset($items);
        } else {
            $this->view->r_events = $events;
        }
        // ------        
        
        // MUSICS
        $musicManager = new Music_Model_Table_Musics();
        $items = array();
        $most_viewed = $musicManager->getMostViewedMusics(7);
        if (count($most_viewed) >= 7) {
            srand((float) microtime() * 10000000);
            $random_key = array_rand($most_viewed);
            $items[] = $most_viewed[$random_key];
            $this->view->most_viewed_music = $items[0];
        } else {
            foreach ($most_viewed as $key=>$music)
                $items[] = $music; 

            $this->view->most_viewed_music = $items[0];    
        }

        $musics = array_diff($musicManager->getMusics(0, 0, 10), array($items[0]));
        if (count($musics) > 7) {
            srand((float) microtime() * 10000000);
            $random_musics = array_rand($musics, 7);
            $items = array();
            foreach ($random_musics as $key=>$music)
                $items[$key] = $musics[$music];

            $this->view->musics = $items;
            unset($items);
        } else {
            $this->view->musics = $musics;
        }        

        $table = new Users_Model_Table_Friends();
        $this->view->friendCount = (int) $table->CountFriends(array("id_user_from" => $userId, "status" => 1))->current()->friendCount;
        $this->view->friendRequestCount = (int) $table->CountRequestFriends(array("id_user_to" => $userId, "status" => 0))->current()->friendCount;

        $form = new Users_Form_MessageForm();
        $form->trackReferrer($request);
        $form->setTemplate('/forms/newmessage');
        $this->view->message_form = $form;
        
        $this->view->r_friends = $user->getFriendsRequest();
        $this->view->friends = $user->getFriends(1);
        $this->view->myId = $this->_user->id;

    }

    // ajax
    public function getfriendsAction()
    {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->layout()->disableLayout();

        $friendType = (int) $this->_getParam("friendType");
        $page = (int) $this->_getParam("page");
        $userId = (int) $this->_getParam("userId");
        
        if ($userId == 0) $userId = $this->_user->id;
        
        $user = $this->_helper->userProfile($userId);
        $this->view->myId = $this->_user->id;

        if ($friendType == 0) {
            $friends = $user->getFriendsRequest();
        } else {
            $friends = $user->getFriends(1);
        }

//        $paginator = Zend_Paginator::factory($friends);
//        $paginator->setCurrentPageNumber(intval($page));
//        $paginator->setItemCountPerPage(20);
//        if (count($friends) > 20) $this->view->pagination = true;

        $this->view->friends = $friends;
        
        
    }

    // ajax
    public function jsonfriendsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender ();

        $filter = new Zend_Filter_StripTags();
        $query = $filter->filter((string) $this->_getParam("query"));
        
        $table = new Users_Model_Table_Friends();
        $users = $table->getAllFriends($this->_user->id, $query);
        
        $table = new Application_Model_Table_Users();
        $friendArr = array();
        
        foreach ($users as $key => $friend) {            
            $friend = $table->find($friend->id_user_from)->current();
            $friendNames[] = array('value' => $friend->username, 'data' => $friend->id);
            $friendIds[] = $friend->id;
        }

        $this->getResponse()->setBody(json_encode(array('query' => $query, 'suggestions' => $friendNames, 'data' => $friendIds)));
    }

    public function addAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender ();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');        

        $request = $this->getRequest();
        $friend_id = (int) $this->_getParam("friendId");
        
        if ($friend_id != 0) {
            
            $data = array();
            $table = new Users_Model_Table_Friends();
            $users = new Application_Model_Table_Users();
            //проверка на уже существующий запрос
            if (!$table->checkFriendship($this->_user->id, $friend_id)) {
                // т.к. связь векторная, записей будет 2
                
                $friends = $users->find($friend_id);
                if (count($friends) > 0) {
                    $friend = $friends->current();
                } else {
                    throw new Exception('Usuario no encontrado.');
                }
                
                $data['id_user_from'] = $this->_user->id;
                $data['id_user_to'] = $friend_id;
                $row = $table->sendFriendship($data);
                $id = $row->save();
                
                if (!is_null($friend->email)) $this->sendRequestEmail($id, $friend);

//                $my = $request->getParam("type");
//                if ($my == 'my')   
//                    $this->getResponse()->setBody('<span id="friend_discard" rel="' . $friend_id . '" class="lnone grey"><img class="Ianadir" src="/zeta/0.png" width="16" height="16" alt="" /> Enviaste una solicitud. ¿Cancelar?</span>');
//                else    
                    $this->getResponse()->setBody('<span id="friend_discard" rel="' . $friend_id . '" class="lnone grey"><img class="Ianadir" src="/zeta/0.png" width="16" height="16" alt="" /> Enviaste una solicitud. ¿Cancelar?</span>');
                
            } else {
                $this->getResponse()->setBody('Usted ya ha enviado una invitación');
            }
        } else {
            $this->getResponse()->setBody('Error! Inténtelo más tarde');
        }

    }
    
    public function removeAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender ();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');        

        $request = $this->getRequest();
        $friend_id = (int) $this->_getParam("friendId");
        if ($friend_id != 0 && $this->_user->id != 0) {        
            $table = new Users_Model_Table_Friends();    
            if ($table->checkFriendship($this->_user->id, $friend_id, 1)) {
                if ($table->deleteFriendship($this->_user->id, $friend_id))
                    $this->getResponse()->setBody('<span id="friend_request" rel="' . $friend_id . '" class="lnone grey"><img class="Ianadir" src="/zeta/0.png" width="16" height="16" alt="" /> Añadir a amigos</span>');
                else
                    $this->getResponse()->setBody('Ошибка удаления');
                
            } else {
                //$this->getResponse()->setBody('Вы не являетесь друзьями');
                throw new Exception('Нет дружественной связи между ' . $this->_user->id . ' и ' . $friend_id);
            }           
        } else {
            throw new Exception('Не заданы идентификаторы пользователей');
        }
        
    }

    public function acceptAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender ();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

        $request = $this->getRequest();
        if (!is_null($request_id = (int) $this->_getParam("requestId"))) {
            $table = new Users_Model_Table_Friends();
            $items = $table->find($request_id); //getFriendshipRequest($request_id);
            if ( count($items) == 0 ) {
                $this->_helper->FlashMessenger(array('system_error' => 'Запроса на дружбу не обнаружено'));
                $this->_redirect( $this->view->url(array('userId' => $this->_user->id), 'userfriends') );
                return;
            }   
         
            $row = $items->current();
            if ($row->status == 0) {
                $row->status = 1;
                $row->save();
                // добавим векторную дружественную связь
                $data['id_user_from'] = $row->id_user_to;
                $data['id_user_to'] = $row->id_user_from;
                $data['status'] = 1; 
                $row = $table->sendFriendship($data);
                $id = $row->save();

                $table = new Application_Model_Table_Users();
                $users = $table->find($row->id_user_to);
                if (count($users) > 0) {
                    $user = $users->current();
                } else {
                    throw new Exception('Не найден пользователь');
                }

                if (!is_null($user->email)) $this->sendAcceptEmail($user);
            }
            
        }
        $this->_redirect( $this->view->url(array('userId' => $this->_user->id), 'userfriends') );
    }

    public function discardAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender ();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $request = $this->getRequest();
        if (!is_null($id_friend = (int) $this->_getParam("friendId"))) {
            $table = new Users_Model_Table_Friends();
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $friendship_request = $table->checkFriendship($auth->getIdentity()->id, $id_friend, 0);
                if (!$friendship_request) return;
            } else {
                return;
            }
            
            $friendship_request->delete();
            
            $my = $request->getParam("type");
            //if ($my == 'my')           
                $this->getResponse()->setBody('<span id="friend_request" rel="' . $id_friend . '" class="lnone grey"><img class="Ianadir" src="/zeta/0.png" width="16" height="16" alt="" /> Añadir a amigos</span>');
            //else    
            //    $this->getResponse()->setBody('<div id="friend_request" rel="' . $id_friend . '"><img class="coll" src="/sprites/null.png" width="21" height="21" alt="" /><div>Añadir como amigo</div></div>');
            
        } else {
            $this->getResponse()->setBody('Ошибка! Попробуйте повторить запрос позже');
        } 
        //$this->_redirect( $this->view->url(array('userId' => $this->_user->id), 'userfriends') );
    }
    
    public function profilediscardAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender ();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $request = $this->getRequest();
        if (!is_null($request_id = (int) $this->_getParam("requestId"))) {
            $table = new Users_Model_Table_Friends();
            $items = $table->find($request_id);
            if (count($items) > 0) {
                $row = $items->current();
                if ($row->status == 0) $table->deleteFriendship($row->id_user_from, $row->id_user_to);
            }
        } 
        $this->_redirect( $this->view->url(array('userId' => $this->_user->id), 'userfriends') );
    }

}
