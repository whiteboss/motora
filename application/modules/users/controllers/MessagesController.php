<?php

class Users_MessagesController extends Zend_Controller_Action {

    protected $_user;

    public function preDispatch() {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $user = $this->_helper->userProfile($auth->getIdentity()->id);
            $this->_user = $auth->getIdentity();
        } else {
            //throw new Exception("Вы не авторизованы");
            $this->_redirect( $this->view->url(array(), 'signin') );
        }
    }

    public function indexAction() {
        $this->_forward('inbox');
    }
    
    protected function sendEmail($user)
    {

        $who = $this->view->getUserByIdentity();
        $mal = new Zend_Mail( 'UTF-8' );
        $mal->setBodyHtml('
                Hola, '.$user->username.'!
                <br /><br />
                Вам поступило сообщение от <a href="' . $this->view->url(array('userId' => $who->id), 'profile') . '">' . $who->getFullName() . '</a>
                <br /><br />
                Atentamente, Qlick.cl<br />
                <a href="http://www.qlick.cl/">http://qlick.cl<br />
                ')
                ->setFrom('no-reply@qlick.cl', 'Qlick.cl')
                ->addTo($user->email)
                ->setSubject('Mensaje de amistad en Qlick.cl')
                ->send();

    }    

    public function sendAction() {
        
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

        $this->view->headScript()
                ->appendFile( '/js/jquery.autocomplete.js' )
                ->appendFile( '/js/users/messages.js' )
                ->appendFile( '/js/charCount.js' );
        $this->view->headLink()
                ->appendStylesheet('/css/jquery.autocomplete.css');

        $this->_helper->initInterface();
        $user = $this->_helper->userProfile($this->_user->id);
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Escribir un mensaje');
        
        $table = new Application_Model_Table_Users(); 
        $messageManager = new Users_Model_Table_Messages(); 

        $request = $this->getRequest();
        $id_message = (int) $request->getParam('messageId');
        $id_user = (int) $request->getParam('userId');
        
        $form = new Users_Form_MessageForm();
        $form->trackReferrer($request);
        $form->setTemplate('/forms/newmessage');

        if ($id_message != 0) {
            $messages = $messageManager->find($id_message)->current();            
            $user = $table->find($messages->id_user_from)->current();
            $this->view->message = $messages->message;
            $form->getElement('userTo')->setValue($user->username);
            $form->getElement('id_user_to')->setValue($messages->id_user_from);
            $form->getElement('sendMessage')->setLabel('Responder');
        }
        
        if ($id_user != 0) {
            $user = $table->find($id_user)->current();
            $form->getElement('userTo')->setValue($user->username);
            $form->getElement('id_user_to')->setValue($id_user);
        }
            
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($form->isValid($data)) {
                $table = new Users_Model_Table_Messages();
                
                $data = $form->getValues();

                $data['id_user_from'] = $this->_user->id;
                $data['message'] = nl2br($data['message']);
                $row = $table->createrow($data);
                $id = $row->save();
                
                $table = new Application_Model_Table_Users();
                $user = $table->find($data['id_user_to'])->current();
                
                // сообщим по почте
                if (!is_null($user->email)) $this->sendEmail($user);
                
                $this->_helper->FlashMessenger(array('system_ok' => 'Mensaje enviado'));
                $this->_redirect( $this->view->url(array('action' => 'outbox'), 'usermessages') );
                return;
                
            } else {
                $form->setDefaults($data);
            }
        }
        
        $this->view->form = $form;
    }

    public function inboxAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

//        $this->view->headScript()
//                ;
        
        $this->view->headLink()
                ->appendStylesheet('/css/jquery.autocomplete.css');

        $this->_helper->initInterface();
        $user = $this->_helper->userProfile($this->_user->id);
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Mensajes');
        
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
        
        $request = $this->getRequest();
        $touserId = (int) $request->getParam('touserId');        
        
        $messages = $user->getInboxMessages();
        $newMessageCount = 0;
        foreach ($messages as $message) {
            if ($message->is_read == 0) {
                $newMessageCount++;
                $message->is_read = 1;
                $message->save();
            }    
        }

        $table = new Users_Model_Table_Friends();
        $users = $table->getAllFriends($this->_user->id);
        $table = new Application_Model_Table_Users();
        $friendArr = array();
        $usersJson = "";
        foreach ($users as $key => $friend) {
            if ($key != 0) $usersJson .= ",";
            $friend = $table->find($friend->id_user_from)->current();
            $friendArr[] = "{ id: \"{$friend->id}\", name: \"{$friend->username}\" }";
            //$usersJson .= //json_encode($friendArr);
        }

        $this->view->users = implode(",\n", $friendArr);
        $this->view->messWord = array('mensaje no leído', 'mensajes no leído', 'mensajes no leído');

        $this->view->messages = $messages;
        $this->view->newMessageCount = $newMessageCount;

        $form = new Users_Form_MessageForm();
        $form->setTemplate('/forms/newmessage');
        
        if (!is_null($touserId)) {
            $form->getElement('id_user_to')->setValue($touserId);
            $this->view->touserId = $touserId;
        }    
        
        $this->view->message_form = $form;        
            
    }

    public function outboxAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

//        $this->view->headScript()
//                ;
        
        $this->view->headLink()
                ->appendStylesheet('/css/jquery.autocomplete.css');

        $this->_helper->initInterface();
        $user = $this->_helper->userProfile($this->_user->id);
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Mensajes');
        
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
        
        $this->view->messWord = array('mensaje', 'mensajes', 'mensajes');

        $table = new Users_Model_Table_Friends();
        $users = $table->getAllFriends($this->_user->id);
        $table = new Application_Model_Table_Users();
        $friendArr = array();
        $usersJson = "";
        foreach ($users as $key => $friend) {
            if ($key != 0) $usersJson .= ",";
            $friend = $table->find($friend->id_user_from)->current();
            $friendArr[] = "{ id: \"{$friend->id}\", name: \"{$friend->username}\" }";
            //$usersJson .= //json_encode($friendArr);
        }

        $this->view->users = implode(",\n", $friendArr);

        $this->view->messages = $user->getOutboxMessages();
        $form = new Users_Form_MessageForm();
        $form->setTemplate('/forms/newmessage');
        $this->view->message_form = $form;
    }

    public function getformAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender ();

        $req = $this->getRequest();
        $id_user = (int) $req->getParam('id_user');

        $form = new Users_Form_MessageForm();
        $form->trackReferrer($req);
        $form->setTemplate('/forms/newmessage');

        $table = new Application_Model_Table_Users();

        if ($id_user != 0) {
            $user = $table->find($id_user)->current();
            $form->getElement('userTo')->setValue($user->username);
            $form->getElement('id_user_to')->setValue($id_user);
        }

        $this->getResponse()->setBody($form->render());
    }

    public function viewAction() {

        $this->_helper->initInterface();
        $this->view->headScript()
                ->appendFile( '/js/users/messages.js' )
                ->appendFile( '/js/charCount.js' );
        
        $user = $this->_helper->userProfile($this->_user->id);

        $req = $this->getRequest();
        if (!is_null($id = $req->getParam('id'))) {
            $table = new Users_Model_Table_Messages();
            $form = new Users_Form_MessageForm();
            $form->setTemplate('/forms/replymessage');
        } else {
            throw new Exception('Desconocidos identificadores');
        }
        $rows = $table->find($id);
        if (count($rows) > 0) {
            $this->view->message = $rows->current();
            $message = $rows->current();
            $message->setFromArray(array("is_read" => 1));
            $message->save();
            $form->getElement('id_user_to')->setValue($rows->current()->id_user_from);
            $this->view->form = $form;
        } else {
            throw new Exception('Desconocido identificador');
        }
    }

    public function deleteinboxAction() {
        $req = $this->getRequest();
        if (!is_null($id = $req->getParam('messageId'))) {
            $table = new Users_Model_Table_Messages();
        } else {
            throw new Exception('Desconocidos identificadores');
        }
        $rows = $table->find($id);
        if (count($rows) > 0) {
            $row = $rows->current();
            // проверим на хитрожопость, вдруг не свое удаляет
            if ($row->id_user_to == $this->_user->id) {
                $row->delete();
                $this->_helper->FlashMessenger(array('system_message' => 'El mensaje ha sido borrado'));
                $this->_redirect($this->view->url(array('userId' => $this->_user->id, 'action' => 'inbox'), 'usermessages'));
            } else {
                $this->_redirect($this->view->url(array('userId' => $this->_user->id, 'action' => 'inbox'), 'usermessages'));
            }
            return;
        } else {
            throw new Exception('Desconocido identificador');
        }
    }

    public function deleteoutboxAction() {
        $req = $this->getRequest();
        if (!is_null($id = $req->getParam('messageId'))) {
            $table = new Users_Model_Table_Messages();
        } else {
            throw new Exception('Desconocido identificador');
        }
        $rows = $table->find($id);
        if (count($rows) > 0) {
            $row = $rows->current();
            // проверим на хитрожопость, вдруг не свое удаляет
            if ($row->id_user_from == $this->_user->id) {
                $row->delete();
                $this->_helper->FlashMessenger(array('system_message' => 'El mensaje ha sido borrado'));
                $this->_redirect($this->view->url(array('userId' => $this->_user->id, 'action' => 'outbox'), 'usermessages'));
            } else {
                $this->_redirect($this->view->url(array('userId' => $this->_user->id, 'action' => 'outbox'), 'usermessages'));
            }
            return;
        } else {
            throw new Exception('Desconocido identificador');
        }
    }

    static function pluralForm($n, $forms) {
        return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
    }

    public function getmessagesAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $messageType = $this->_getParam("messageType");
        $page = (int)$this->_getParam("page");
        $user = $this->_helper->userProfile($this->_user->id);

        if ($messageType == 1)
            $messages = $user->getInboxMessages();
        else if ($messageType == 2)
            $messages = $user->getOutboxMessages();
        else if ($messageType == 3)
            $messages = $user->getUnreadMessages();

        $messagesManager = new Users_Model_Table_Messages();
        $inboxCount = $messagesManager->getCountMessages(array("userId" => $this->_user->id, "messageType" => 1));
        if ($inboxCount->current()->messageCount>0) $inboxCount = "(".$inboxCount->current()->messageCount.")"; else $inboxCount = "";
//        $outboxCount = $messagesManager->getCountMessages(array("userId" => $this->_user->id, "messageType" => 2));
//        if ($outboxCount->current()->messageCount>0) $outboxCount = "(".$outboxCount->current()->messageCount.")"; else $outboxCount = "";
//        $unreadCount = $messagesManager->getCountMessages(array("userId" => $this->_user->id, "messageType" => 3));
//        if ($unreadCount->current()->messageCount>0) $unreadCount = "(".$unreadCount->current()->messageCount.")"; else $unreadCount = "";

        $this->view->inboxCount = $inboxCount;
//        $this->view->outboxCount = $outboxCount;
//        $this->view->unreadCount = $unreadCount;

        $this->view->messWord = array('mensaje no leído', 'mensajes no leído', 'mensajes no leído');

        $paginator = Zend_Paginator::factory($messages);
        $paginator->setCurrentPageNumber(intval($page));
        $paginator->setItemCountPerPage(10);

        $this->view->messageType = $messageType;
        $this->view->messages = $paginator;
        if (count($messages) > 10) $this->view->pagination = true;
        $this->_helper->layout()->disableLayout();
    }

    public function deletemessageAction() {
        $req = $this->getRequest();
        if (!is_null($id = $this->_getParam("messageId")) && $this->_user->id) {
            $table = new Users_Model_Table_Messages();
            $rows = $table->getMessagesWithParams(array("id" => $id, "userToFrom" => $this->_user->id));
            if (count($rows) > 0) {
                $row = $rows->current();
                if ($row->id_user_from == $this->_user->id)
                    $params = array("delete_user_from" => 1);
                if ($row->id_user_to == $this->_user->id)
                    $params = array("delete_user_to" => 1);
                $row->setFromArray($params);
                $row->save();
                $result = array("result" => 1);
            }
        }

        if (!$result && empty($result))
            $result = array("result" => 0);

        $this->_helper->layout()->disableLayout();
        $this->renderScript('messages/ajax.phtml');
        $this->view->result = $result;
    }

    public function readmessageAction() {
        if (!is_null($id = $this->_getParam("messageId")) && $this->_user->id) {
            $table = new Users_Model_Table_Messages();
            $rows = $table->getMessagesWithParams(array("id" => $id, "userToFrom" => $this->_user->id));
            if (count($rows) > 0) {
                $row = $rows->current();
                $params = array("is_read" => 1);
                $row->setFromArray($params);
                $row->save();
                $result = array("result" => 1);
            }
            $this->view->result = $result;
        }
        $this->_helper->layout()->disableLayout();
        $this->renderScript('messages/ajax.phtml');
    }

}

