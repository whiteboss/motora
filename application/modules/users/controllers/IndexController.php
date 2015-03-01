<?php

class Users_IndexController extends Zend_Controller_Action
{

    protected $user_id;

    public function init()
    {
        $this->logger = Zend_Registry::get('logger');         
    }
    
    public function updateavatarAction()
    {       
        
        // проверка аватарки
        function remoteFileExists($url) {
            $curl = curl_init($url);

            //don't fetch the actual page, you only want to check the connection is ok
            curl_setopt($curl, CURLOPT_NOBODY, true);

            //do request
            $result = curl_exec($curl);
            $ret = false;

            //if request did not fail
            if ($result !== false) {
                //if request was ok, check response code
                $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  
                if ($statusCode == 200) {
                    $ret = true;   
                }
            }

            curl_close($curl);
            return $ret;
        }        
                
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();   
        
        if ($this->view->isGodOfProject()) {
            
            $userId = (int) $this->getRequest()->getParam ( 'userId' );
            $user = $this->_helper->userProfile($userId);
            
            // грузим фотку
            $url = 'http://graph.facebook.com/'.$user->uid.'/picture?type=large';
            $rray=get_headers($url);
            $hd = $rray[5];                
            $path_photo = substr($hd,strpos($hd,'http'));

            $path = Zend_Registry::get('upload_path');
            $file = time() . md5($user->uid) . '.jpg';
            $local_photo = $path . '/tmp/' . $file;  

            //die();

            $avatar_exists = remoteFileExists($path_photo);
            if ($avatar_exists) {

                // CURL copy
                $ch = curl_init($path_photo);
                $fp = fopen($local_photo, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);                    

                //if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/user/' . $file, 64, 64)) {
                //    throw new Exception('Error al hacer zoom');
                //}
                if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/user/119_' . $file, 119, 119)) {
                    throw new Exception('Ошибка при уменьшении изображения1');
                }

                if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/user/105_' . $file, 105, 105)) {
                    throw new Exception('Ошибка при уменьшении изображения1');
                }

                if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/user/64_' . $file, 64, 64)) {
                    throw new Exception('Ошибка при уменьшении изображения1');
                }

                if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/user/21_' . $file, 21, 21)) {
                    throw new Exception('Ошибка при уменьшении изображения1');
                }
                
                @unlink($local_photo);                
                
                // удалим старые аватарки
                if (is_file($path . '/user/119_' . $user->avatar)) @unlink($path . '/user/119_' . $user->avatar);
                if (is_file($path . '/user/105_' . $user->avatar)) @unlink($path . '/user/105_' . $user->avatar);
                if (is_file($path . '/user/64_' . $user->avatar)) @unlink($path . '/user/64_' . $user->avatar);
                if (is_file($path . '/user/21_' . $user->avatar)) @unlink($path . '/user/21_' . $user->avatar);
                
                $user->avatar = $file;    
                $user->save();
                
                $this->getResponse()->setBody('Actualizé');

            } else {
                $this->getResponse()->setBody('No se puede reconocer avatar');
            }            
            
        }        
        
    }        

    public function fbfriendsAction()
    {
        
        function sksort(&$array, $subkey="id", $sort_ascending=false) {

            if (count($array))
                $temp_array[key($array)] = array_shift($array);

            foreach($array as $key => $val){
                $offset = 0;
                $found = false;
                foreach($temp_array as $tmp_key => $tmp_val)
                {
                    if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
                    {
                        $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                                    array($key => $val),
                                                    array_slice($temp_array,$offset)
                                                  );
                        $found = true;
                    }
                    $offset++;
                }
                if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
            }

            if ($sort_ascending) $array = array_reverse($temp_array);

            else $array = $temp_array;
        } 
    
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            
            
            $userId = $auth->getIdentity()->id;
            $user = $this->_helper->userProfile($userId);

            if (!is_null($user->uid)) {

                // инстанциируем объект FB API, используя APP ID и APP SECRET
                $fb = new Qlick_Api_Facebook_Facebook(array(
                    'appId' => Zend_Registry::get('FB_app_ID'),
                    'secret' => Zend_Registry::get('FB_app_secret'),
                    'cookie' => true
                ));

                try{
                    $res = $fb->api('/' . $user->uid . '/friends', 'GET', array('access_token' => $_SESSION['FACEBOOK_ACCESS_TOKEN']));
                } catch (Exception $e){
                    $res = $e->getMessage();
                }

                if (is_array($res['data']) && count($res['data']) > 0) {                    
                    sksort($res['data'], 'name', true);                    
                    $friends[] = array('id' => $user->uid, 'name' => 'Yo, ' . $user->username);
                    foreach ($res['data'] as $value) {
                        $friends[] = array('id' => $value["id"], 'name' => $value["name"]);   
                    }
                    //die(print_r($friends));
                    $this->getResponse()->setBody(json_encode($friends));
                } else {
                    throw new Exception($res); 
                }
                
//                // для тестов на ебанной работе
//                $friends = array($user->uid => 'Yo', '687726741' => 'Matilde Mingo Guilisasti', '641852682' => 'Vincenzo Rulli', '1270222904' => 'DjRoberto Caceres');
//                $this->getResponse()->setBody(json_encode($friends));

            } else {
                $this->getResponse()->setBody(json_encode(array()));
            }
            
        } else {
            throw new Exception('Вы не авторизованы');
        }
        
    }    
    
    public function peoplesAction()
    {
        
        $this->_helper->initInterface();
//        $this->view->headScript()
//                ->appendFile( '/js/jquery.mousewheel.js' )
//                ->appendFile( '/js/jquery.jscrollpane.min.js' );
        
        $this->view->headTitle()->append('Usuarios');
        
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
        
        $from = (int) $this->_getParam("from");
        
        $sex_param = $this->_getParam("sex");
        if (isset($sex_param)) {
            switch ($sex_param) {
                case 'hombres'   : $this->view->headTitle()->append('Hombres'); $sex = 1; break;
                case 'mujeres'    : $this->view->headTitle()->append('Mujeres'); $sex = 2; break;
                default : $sex = 0;
            } 
        } else {
            $sex = 0;
        }
        
        $this->view->sex = $sex;
                
        $table = new Application_Model_Table_Users(); 
        
        if ($from > 0) { 
            // ajax
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setRender ('morepeoples');
            $users = $table->getAllUsers($from, 0, $sex);
            $this->view->from = $from + 1;
            $this->view->users = $users;
        } else {
            //$this->view->headScript()->appendFile( '/js/users/friends.js' );
            $users = $table->getAllUsers(0, Application_Model_User::$user_per_lazypage, $sex);
            if (count($users) > 0) {
                $this->view->users = $users;
            }            
        }
        
    }

    public function calcrateAction()
    {        
        $this->_helper->viewRenderer->setNoRender();
        $this->view->layout()->disableLayout();
        
        $this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );

        $userId = (int) $this->getRequest()->getParam ( 'userId' );
        $table = new Application_Model_Table_Users();
        if ($userId > 0) {
            $users = $table->find($userId);
            if (count($users) > 0) {
                $user = $users->current();
                $user->calcrating();
            }
        } else {
            $users = $table->getAllUsers();
            foreach ($users as $user) {
                $user->calcrating();    
            }
        }    
        
        $this->_redirect( $this->view->url(array(), 'people') );
    }    
    

    public function indexAction()
    {
        $this->_forward('signup');
    }

    public function postsAction()
    {
        $this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );

        $this->_helper->initInterface();
//        $this->view->headScript()
//                ->appendFile( '/js/jquery.mousewheel.js' )
//                ->appendFile( '/js/jquery.jscrollpane.min.js' );

        $userId = (int) $this->getRequest()->getParam ( 'userId' );
        $user = $this->_helper->userProfile($userId);
        $this->view->partial()->setObjectKey('user');
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Publicaciones'); 
        
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
        
        //$posts = new Post_Model_Table_Post();
        $userPosts = $user->getPosts();
        $this->view->posts = $userPosts;
    }
    
    public function eventsAction()
    {
        $this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );

        $this->_helper->initInterface();
//        $this->view->headScript()
//                ->appendFile( '/js/jquery.mousewheel.js' )
//                ->appendFile( '/js/jquery.jscrollpane.min.js' );

        $userId = ( int ) $this->getRequest()->getParam ( 'userId' );
        $user = $this->_helper->userProfile($userId);
        $this->view->partial()->setObjectKey('user');
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Eventos');        
        
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
        
        $events = $user->getWalkedAndCreatedEvents();
        $this->view->events = $events;
        
    }
    
    public function videosAction()
    {
        $this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );

        $this->_helper->initInterface();
        $this->view->headScript()
                ->appendFile( '/js/mediaelement-and-player.min.js' );        

        $userId = ( int ) $this->getRequest()->getParam ( 'userId' );
        $user = $this->_helper->userProfile($userId);
        $this->view->partial()->setObjectKey('user');
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Video');  
        
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

        $items = $user->getVideos();
        $this->view->videos = $items;
    }  
    
    public function musicsAction()
    {
        $this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );

        $this->_helper->initInterface();
        $this->view->headScript()
                ->appendFile( '/js/mediaelement-and-player.min.js' );        

        $userId = ( int ) $this->getRequest()->getParam ( 'userId' );
        $user = $this->_helper->userProfile($userId);
        $this->view->partial()->setObjectKey('user');
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Música'); 
        
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

        $items = $user->getMusics();
        $this->view->items = $items;
    }    
    
    public function galleriesAction()
    {
        $this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );

        $this->_helper->initInterface();
//        $this->view->headScript()
//                ->appendFile( '/js/jquery.mousewheel.js' )
//                ->appendFile( '/js/jquery.jscrollpane.min.js' );

        $userId = ( int ) $this->getRequest()->getParam ( 'userId' );
        $user = $this->_helper->userProfile($userId);
        $this->view->partial()->setObjectKey('user');
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Fotoreportajes'); 
        
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

        $galleries = $user->getPhotoReports();
        $this->view->userGalleries = $galleries;
    }

    public function companiesAction()
    {
        $page = $this->_getParam("page", 0);
        $this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );

        $this->_helper->initInterface();
//        $this->view->headScript()
//                ->appendFile( '/js/jquery.mousewheel.js' )
//                ->appendFile( '/js/jquery.jscrollpane.min.js' );

        $userId = ( int ) $this->getRequest()->getParam ( 'userId' );
        $user = $this->_helper->userProfile($userId);
        $this->view->partial()->setObjectKey('user');
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Lugares');
        
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

        $userCompanies = $this->view->user->getCompanies();
        //$this->view->pagination = true;
        $this->view->userCompanies = $userCompanies;
    }
    
    // регистрация
    public function signupAction()
    {
        $this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );
        $this->_helper->layout->setLayout('register');
        $this->_helper->initInterface();
        $this->view->headScript()
                        ->appendFile( '/js/users/signup.js' )
                        ->appendFile( '/js/charCount.js' );

        $form = new Users_Form_SignupForm();
        $form->setTemplate('/forms/signup');
        $signup_url = $this->view->url(array('module'=>'users','controller'=>'index', 'action'=>'signup'), null, true);
        $form->setAction($signup_url);
        $request = $this->getRequest();        
        if ($request->isPost()) {
            $data = $request->getPost();
            
            if (preg_match("/boss/i", $data['username'])) {
                    $this->_helper->FlashMessenger(array('system_error' => 'Ja-ja. Nombres como Boss es inapropiado.'));
                    $this->_redirect($this->view->url(array(), 'signup'));                    
            }
            
            if ( $form->isValid( $data ) ) {
                
                $data = $form->getValues();

                $table = new Application_Model_Table_Users();
                $year = (int) date("Y");
                if ($data["year_birth"] > $year) {
                    $data["year_birth"] = $year - 14;
                }
                if ($data['year_birth'] == 0) unset($data['year_birth']);
                
                $salt = $table->generateSalt();
                $data['salt'] = $salt;
                $data["password"] = md5(md5($salt).md5($data["password"]));
                if (date('Y-m-d') < '2013-01-01')
                    $data['rate'] = 10;
                
                $row = $table->createRow( $data );
                
		$id = $row->save();                
                $user = $table->fetchRow( $table->select()->where("id = ?", $id) );
                
                // инициализируем карму
                $table = new Users_Model_Table_Karma();
                // до 1 сентября даем бонус
                if (date('Y-m-d') < '2013-01-01')
                    $karma = 10;
                else
                    $karma = 0;
                
                $karmaManager = $table->createRow( array('id_user' => $user->id, 'karma' => $karma, 'date' => $user->signup_date) );
                $karmaManager->save();
                
		//$this->sendEmail( $user );
                //$this->_helper->FlashMessenger(array('system_ok' => 'Вы успешно зарегистрировались, на электронный адрес было выслано подтверждение'));
                //$data = null;
                //$this->setRequest(null);
                //$this->_redirect('signin');
                $this->_forward('signin');
                return;
            } else {
                $form->setDefaults($data);
            }
        }
        $this->view->form = $form;
    }
    
    public function restoreAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8' ); 
        
        $userId = ( int ) $this->getRequest()->getParam ( 'userId' );
        if ( empty($userId) ) throw new Exception( 'No se especifica el identificador' );
        
        $table = new Application_Model_Table_Users();
        $restores = new Users_Model_Table_Restores();
        $rows = $table->find( $userId );
        if ( count($rows) > 0 ) {
            $user = $rows->current();
            $restore = $restores->getRestore($userId);
            if (!is_null($restore)) {
                $user->salt = $restore->salt;
                $user->password = md5(md5($restore->salt).md5($restore->generated_password));
                $user->save();
                $restore->delete();
            }    
            $this->_helper->FlashMessenger(array('system_message' => 'La nueva contraseña se establece')); 
            $this->_redirect( $this->view->url(array(''), 'signin') );
            return;
        } else {
            throw new Exception('Desconocido identificador');
        }
    }

    public function passwordrestoreAction()
    {
        $this->_helper->initInterface();
        $this->view->headScript()
                        ->appendFile( '/js/charCount.js' );
        
        $this->view->headTitle()->append('Recuperación de la contraseña');
        
        $request = $this->getRequest();

        $users = new Application_Model_Table_Users();
        $form = new Users_Form_RestoreForm();
        $form->trackReferrer($request);
        $form->setTemplate('/forms/restore');
        
        if ($this->view->getLogged()) {
            $userId = $this->view->GetUserByIdentity()->id;
            $this->_redirect( $this->view->url(array('userId' => $userId), 'profile') );
        }            
        
        if ($request->isPost()) {
            
            if ($form->isValid($request->getPost())) {
                
                $data = $form->getValues();
                
                $table = new Users_Model_Table_Restores();
                
                if (!is_null($data['email'])) {
                    
                    $user = $users->getUserByEmail($data['email']);
                    if (!is_null($user)) {
                        // сделаем запрос
                        $restore = $table->getRestore($user->id);
                        if (!is_null($restore)) {
                            $this->_helper->FlashMessenger(array('system_message' => 'Por favor, consultar su correo electrónico.<br />Usted ya ha enviado una solicitud para cambiar la contraseña'));
                            $this->_redirect( $this->view->url(array(), 'signin') );    
                        }
                        
                        $data['id_user'] = $user->id;
                        $new_password = $this->generatePassword();
                        $salt = $users->generateSalt();
                        $data['salt'] = $salt;
                        $data['generated_password'] = $new_password;
                        $row = $table->createrow($data);
                        $row->save();
                        //$user->salt = $salt;
                        //$user->password = $data['generated_password'];
                        //$user->save();
                        // отправим уведомление
                        $this->sendNotificationRestorePassword($user, $new_password);
                        
                        $this->_helper->FlashMessenger(array('system_message' => 'Hemos enviado una nueva contraseña a su dirección de correo electrónico'));
                        $this->_redirect( $this->view->url(array(), 'signin') );
                        
                    } else {
                        $this->_helper->FlashMessenger(array('system_error' => 'Usuario con este correo electrónico que no está registrada'));    
                        $this->_redirect( $this->view->url(array(), 'userrestore') );
                    }
                    
                }

            }
        }
        $this->view->form = $form;        
    }    
    
    protected function generatePassword()
    {
        $arr = array('a','b','c','d','e','f',
                     'g','h','i','j','k','l',
                     'm','n','o','p','r','s',
                     't','u','v','x','y','z',
                     'A','B','C','D','E','F',
                     'G','H','I','J','K','L',
                     'M','N','O','P','R','S',
                     'T','U','V','X','Y','Z',
                     '1','2','3','4','5','6',
                     '7','8','9','0'
                     //,'(',')',
                     //'[',']','!','?','&','^',
                     //'%','@','*','$','<','>',
                     //'/','|','+','-','{','}',
                     //'`','~'
                     );
        // Генерируем пароль
        $pass = "";
        for($i = 0; $i < 9; $i++)
        {
          // Вычисляем случайный индекс массива
          $index = rand(0, count($arr) - 1);
          $pass .= $arr[$index];
        }
        return $pass;        
    }

        /**
     * Отправляет e-mail сообщение пользователю для сброса пароля
     * @param Zend_Db_Table_Row $user
     */
    protected function sendNotificationRestorePassword($user, $password)
    {
            $link = $this->view->url(array('userId' => $user->id, 'action' => 'restore'), 'profile');
            $mal = new Zend_Mail( 'UTF-8' );
            //Уважаемый '.$user->lastname.' '.$user->firstname.', ссылка для подтверждения: <a href="' . $link .  '">' . $link . '</a>'
            $mal->setBodyHtml('
            Hola, ' . $user->username . '!
	    <br /><br />
	    Usted consigue la recuperación de la contraseña en el sitio Qlick.cl.<br />
	    Hemos generado una nueva contraseña. Para activarlo, haga clic en el enlace e introduzca la contraseña ' . $password . ':
	    <a href="' . $link . '" target="_blank">' . $link . '</a>
	    <br /><br />
	    Le recomendamos que cambie la contraseña.<br />
	    Para ello, vaya a su Perfil / Ajustes.<br />
	    <br /><br />
	    Atentamente, Qlick.cl<br />
	    <a href="http://www.qlick.cl/">http://qlick.cl</a>
	    ')
            ->setFrom('no-reply@qlick.cl', 'Qlick.cl')
            ->addTo($user->email)
            ->setSubject('Recuperación de la contraseña')
            ->send();
    }    

    /**
     * Отправляет e-mail сообщение пользователю для активации аккаунта
     * @param Zend_Db_Table_Row $user
     */
    protected function sendEmail($user)
    {

            $link = 'http://' . $_SERVER['HTTP_HOST'] . $this->view->url(array('uid' => $user->id), 'userconfirm'); //users/confirm/uid/' . $user->id;
            $mal = new Zend_Mail( 'UTF-8' );
            //Уважаемый '.$user->lastname.' '.$user->firstname.', ссылка для подтверждения: <a href="' . $link .  '">' . $link . '</a>'
            $mal->setBodyHtml('
                    Hola, '.$user->username.'!
		    <br /><br />
		    Has registrado correctamente en sitio Qlick.cl!<br />
		    Estamos muy contentos y esperamos que se conviertan en buenos amigos.<br />
		    <br /><br />
		    Para confirmar su correo electrónico, usted tiene que hacer clic en el enlace o cópielo en su navegador:<br />
		    <a href="'.$link.'">'.$link.'</a><br />
		    <br /><br />
		    Atentamente, Qlick.cl<br />
		    <a href="http://www.qlick.cl/">http://qlick.cl<br />
		    ')
                    ->setFrom('no-reply@qlick.cl', 'Qlick.cl')
                    ->addTo($user->email)
                    ->setSubject('Bienvenido a Qlick.cl!')
                    ->send();

    }

    public function validateformAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->view->layout()->disableLayout();

        $f = new Users_Form_SignupForm();
        $f->isValid($_POST);
        $json = $f->getMessages();
        header('Content-type: application/json');
        echo Zend_Json::encode($json);
    }

    /**
     * Подтверждение
     * @return void
     */
    public function confirmAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8' );
        $id = ( int ) $this->getRequest()->getParam ( 'uid' );
        if ( empty($id) ) throw new Exception( 'No se especifica el identificador' );
        $table = new Application_Model_Table_Users();
        $rows = $table->find( $id );
        if ( count($rows)>0 ) {
            $user = $rows->current();
            if ( $user->id == $id ) {
                    $user->is_confirmed = 1;
                    $user->save();
                    $this->_helper->FlashMessenger(array('system_ok' => 'Ha activado su cuenta ' . $user->username));
                    $this->_redirect( $this->view->url(array('userId' => $user->id, 'action' => 'profile'), 'profile') );
                    return;
            }
        } else {
            throw new Exception('Desconocido identificador');
        }
    }

    public function signinAction()
    {

        $this->_helper->initInterface();
        $this->view->headScript()
                        //->appendFile( '/js/auth.js' )
                        ->appendFile( '/js/charCount.js' );
        
        $request = $this->getRequest();
        
        // если юзер залогинен отправим его на профиль
        if ($this->view->GetLogged())
            $this->_redirect( $this->view->url(array('userId' => $this->view->GetUserByIdentity()->id, 'action' => 'profile'), 'profile') );

        $users = new Application_Model_Table_Users();
        $form = new Users_Form_SigninForm();
        $form->trackReferrer($request);
        $form->setTemplate('/forms/signin');        

//        $session = Zend_Registry::get('session');
//        if (!empty($session)) {
//            $form->getElement('email')->setValue($session->email);
//        }

        //if ($this->getRequest()->isPost()) {
        //var_dump($_SESSION);
        
        if ($request->isPost()) {
            
            $data = $request->getPost();
            if ($form->isValid($data)) {
                
                $data = $form->getValues();
                $auth = Zend_Auth::getInstance();
                $authAdapter = new Zend_Auth_Adapter_DbTable($users->getAdapter(), 'users');
                $authAdapter->setIdentityColumn('email')
                        ->setCredentialColumn('password')
                        ->setCredentialTreatment('MD5(CONCAT(MD5(salt),MD5(?)))')
                        ->setIdentity($data['email'])
                        ->setCredential($data['password']);
                $result = $auth->authenticate($authAdapter);
                if ($result->isValid()) {
                    
                    $storage = new Zend_Auth_Storage_Session();
                    $currentUser = $authAdapter->getResultRowObject(null, 'password');
                    
                    $delta_signup = ceil( (time() - mktime(
                                date('H', strtotime($currentUser->signup_date)), date('i', strtotime($currentUser->signup_date)), 0,
                                date('m', strtotime($currentUser->signup_date)), date('d', strtotime($currentUser->signup_date)), date('Y', strtotime($currentUser->signup_date))) ) / 86400 ) ;                    
                
                    // если не активировали в течении суток, то "ты кто такой? давай, досвидания"
                    if ($currentUser->is_confirmed == 1 || $delta_signup <= 1) {                        
                        $storage->write($currentUser);
                        $user = $storage->read();
                        
                        // отметим дату входа
                        $signin_user = $users->find($user->id)->current();
                        $signin_user->last_signin = date_create()->format( 'Y-m-d H:i:s' );
                        $signin_user->save();
                        //throw new Exception($currentUser->id);
                        
                        $this->logger->log( 'вход', Zend_Log::INFO, $this->getRequest() );
                        
                        $this->_redirect( $form->getReferrer( $this->view->url(array('userId' => $user->id, 'action' => 'profile'), 'profile') ) );
                    } else {
                        $auth->clearIdentity();
                        $this->_helper->FlashMessenger(array('system_message' => 'Tienes que activar su perfil'));
                        $this->_redirect($this->view->url(array(''), 'signin'));
                        return;
                    }
                    //$this->_redirect( $this->view->url( array('module'=>'users','controller'=>'index','action'=>'profile'), null, true ) );
                } else {                    
                    $this->_helper->FlashMessenger(array('system_error' => 'No válido nombre de usuario o contraseña'));                    
                    $this->_redirect($this->view->url(array('email' => $data['email']), 'signin', true));
                }
            } else {
                $form->setDefaults($data);
            }
        }
        $this->view->form = $form;
    }
    
    public function authAction()
    {
        function encodestring($string) 
        { 
            $replace = array ("," => "", "." => "", " " => " ", "а" => "a",  
            "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "zh",  
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l", "м" => "m",  
            "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t",  
            "у" => "u", "ф" => "f", "х" => "h", "ц" => "ts", "ч" => "ch", "ш" => "sh",  
            "щ" => "sch", "ъ" => "'", "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
            "А" => "a",  
            "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D", "Е" => "E", "Ж" => "ZH",  
            "З" => "Z", "И" => "I", "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M",  
            "Н" => "N", "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",  
            "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH", "Ш" => "SH",  
            "Щ" => "SCH", "Ъ" => "'", "Ы" => "YI", "Ь" => "", "Э" => "E", "Ю" => "YU", "Я" => "YA" ); 

            return $str = iconv ( "UTF-8", "UTF-8//IGNORE", strtr ( $string, $replace ) );
        }
        
        // проверка аватарки
        function remoteFileExists($url) {
            $curl = curl_init($url);

            //don't fetch the actual page, you only want to check the connection is ok
            curl_setopt($curl, CURLOPT_NOBODY, true);

            //do request
            $result = curl_exec($curl);
            $ret = false;

            //if request did not fail
            if ($result !== false) {
                //if request was ok, check response code
                $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  
                if ($statusCode == 200) {
                    $ret = true;   
                }
            }

            curl_close($curl);
            return $ret;
        }

        $this->_helper->layout->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8' );        
        $this->_helper->initInterface();

        $request = $this->getRequest();
        $social = $this->getRequest()->getParam ( 'social' );

        $site = Zend_Registry::get('server_name');
        
        $services = array(
            'facebook' => array(
                'class' => 'Qlick_Sauth_services_FacebookOAuthService',
                'client_id' => Zend_Registry::get('FB_app_ID'),
                'client_secret' => Zend_Registry::get('FB_app_secret'),
                'redirectUrl' => 'http://'.$site.'/auth/facebook/',
                'cancelUrl' => 'http://'.$site.'/cancel/',
                'scope' => 'publish_stream'
            )
        );

        $eauth = new Qlick_Sauth_SAuth();
        $eauth->setServices($services);
        
        $authIdentity = $eauth->getIdentity($social);  
        if ($authIdentity->authenticate()) {
            $user_data = $authIdentity->getAttributes();
            //$user_data['identity'] = $social;

//            var_dump(print_r($user_data));
//            die();

            $table = new Application_Model_Table_Users();
            $storage = new Zend_Auth_Storage_Session();

            $user = $table->getUserByUid($user_data['id']);
            if (is_null($user)) {

                $data = array();
                $data['uid'] = $user_data['id']; 
                $data['identity'] = $social;
//                var_dump(print_r($user_data));
//                die();
                if (empty($user_data['name'])) {
                    // у пользователя нет ника, сделаем это за него                    
                    $username = '';
                    if (preg_match('/^[а-я]+$/iu', $user_data['first_name'])) $username = encodestring($user_data['first_name']); else $username = $user_data['first_name'];                    
                    if (preg_match('/^[а-я]+$/iu', $user_data['last_name'])) $username .= ' ' . encodestring($user_data['last_name']); else $username .= ' ' . $user_data['last_name'];                    
                    $data['username'] = $username;
                } else {                
                    if (preg_match('/^[а-я]+$/iu', $user_data['name'])) 
                        $data['username'] = encodestring($user_data['name']);
                    else
                        $data['username'] = $user_data['name'];
                }
                
                $data['firstname'] = $user_data['first_name'];
                $data['lastname'] = $user_data['last_name'];
                $data['sex'] = $user_data['gender'] == 'male' ? 1 : 2;
                $data['is_confirmed'] = 1;
                $data['facebook'] = $user_data['link'];
                
//                // дата рождения
//                if (!empty($user_data['bdate'])) {
//                    $bdate = explode('.', $user_data['bdate']);
//                    $data['day_birth'] = $bdate[0];
//                    $data['month_birth'] = $bdate[1];
//                    $data['year_birth'] = $bdate[2];
//                }
                
                // грузим фотку
                $url = 'http://graph.facebook.com/'.$user_data['id'].'/picture?type=large';
                $rray=get_headers($url);
                $hd = $rray[5];                
                $path_photo = substr($hd,strpos($hd,'http'));
                
                $path = Zend_Registry::get('upload_path');
                $file = time() . md5($user_data['id']) . '.jpg';
                $local_photo = $path . '/tmp/' . $file;  
                
                //die();
                
                $avatar_exists = remoteFileExists($path_photo);
                if ($avatar_exists) {
                    
                    // CURL copy
                    $ch = curl_init($path_photo);
                    $fp = fopen($local_photo, 'wb');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);                    
                     
                    //if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/user/' . $file, 64, 64)) {
                    //    throw new Exception('Error al hacer zoom');
                    //}
                    if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/user/119_' . $file, 119, 119)) {
                        throw new Exception('Ошибка при уменьшении изображения1');
                    }

                    if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/user/105_' . $file, 105, 105)) {
                        throw new Exception('Ошибка при уменьшении изображения1');
                    }

                    if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/user/64_' . $file, 64, 64)) {
                        throw new Exception('Ошибка при уменьшении изображения1');
                    }

                    if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/user/21_' . $file, 21, 21)) {
                        throw new Exception('Ошибка при уменьшении изображения1');
                    }
                    $data['avatar'] = $file;    
                    @unlink($local_photo);

                } else {
                    //throw new Exception('Нет аватарки');
                    $this->logger->log('No se puede reconocer avatar: ' . $path_photo, Zend_Log::NOTICE, $this->getRequest());
                }
                
                $row = $table->createRow( $data );
                $id = $row->save();
                
                $user = $table->fetchRow( $table->select()->where("id = ?", $id) );
//
                // инициализируем карму
                $table = new Users_Model_Table_Karma();
                // до конца года даем бонус
                if (date('Y-m-d') < '2013-01-01')
                    $karma = 10;
                else
                    $karma = 0;

                $karmaManager = $table->createRow( array('id_user' => $user->id, 'karma' => $karma, 'date' => $user->signup_date) );
                $karmaManager->save();
            }
            
            $storage->write($user);
            parse_str($authIdentity->getAuthToken(), $access_token);
            $_SESSION['FACEBOOK_ACCESS_TOKEN'] = $access_token['access_token'];
            //$this->_redirect( $this->view->url(array('userId' => $user->id, 'action' => 'profile'), 'profile') );
            $this->_redirect( $request->getParam('referrer', $request->getServer('HTTP_REFERER')) );

            //$javascript = '<script type="text/javascript"> if (window.opener) {';
            //$javascript.='window.opener.jQuery(\'#comment-contact-social\').append(\'<img src="'.$user_data['photo'].'"/><br/><a target="_blank" href="'.$user_data['url'].'">'.$user_data['name'].'</a>\');';
            //$javascript.='window.opener.jQuery(\'#auth-services-box\').html(\'<a href="/logout/'.$user_data['identity'].'" class="auth-link logout '.$user_data['identity'].'"><span class="auth-icon '.$user_data['identity'].'"></span><span class="auth-title">Выход</span></a>\');';
            //$javascript.= '}  setTimeout( window.close(), 600); </script>';
        } else {
            $this->_helper->FlashMessenger(array('system_error' => 'No válido nombre de usuario o contraseña'));
            $this->_redirect($this->view->url(array(''), 'signin'));    
        }
        
    }    

    public function logoutAction()
    {
        $request = $this->getRequest();
        $auth = Zend_Auth::getInstance();
        $user_id = $auth->getIdentity()->id;
        $url = $request->getParam('referrer', $request->getServer('HTTP_REFERER'));        
        //$this->_forward('/signin');        
        $this->logger->log('выход', Zend_Log::INFO, $request);        
        
//        // если facebook
//        if (!is_null($auth->getIdentity()->uid)) {
//            $social = $this->getRequest()->getParam ( 'social' ); 
//            $site = Zend_Registry::get('server_name');
//            $services = array(
//                'facebook' => array(
//                    'class' => 'Qlick_Sauth_services_FacebookOAuthService',
//                    'client_id' => '323432924421071',
//                    'client_secret' => 'e485d656827ff3f3645548c3c02a7fb4',
//                    'redirectUrl' => 'http://'.$site.'/auth/facebook/',
//                    'cancelUrl' => 'http://'.$site.'/cancel/',
//                )
//            );
//
//            $eauth = new Qlick_Sauth_SAuth();
//            $eauth->setServices($services);
//
//            $authIdentity = $eauth->getIdentity($social);
//            if($authIdentity->logout()){
//                
//            }
//        }
        
        $auth->clearIdentity();        
        $this->_redirect($url);
    }

    // главная страница пользователя
    public function profileAction()
    {
        
        function sksort(&$array, $subkey="id", $sort_ascending=false) {

            if (count($array))
                $temp_array[key($array)] = array_shift($array);

            foreach($array as $key => $val){
                $offset = 0;
                $found = false;
                foreach($temp_array as $tmp_key => $tmp_val)
                {
                    if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
                    {
                        $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                                    array($key => $val),
                                                    array_slice($temp_array,$offset)
                                                  );
                        $found = true;
                    }
                    $offset++;
                }
                if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
            }

            if ($sort_ascending) $array = array_reverse($temp_array);

            else $array = $temp_array;
        }        
        
        $this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );

        $this->_helper->initInterface();
        $this->view->headScript()
                ->appendFile( '/js/mediaelement-and-player.min.js' )
                ->appendFile( '/js/events/walks.js' );

        $user_id = ( int ) $this->getRequest()->getParam ( 'userId' );
        $user = $this->_helper->userProfile($user_id);
        
        $this->view->headTitle()->append($user->username);
        $this->view->headTitle()->append('Perfil');
        
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
        
        // АКТИВНОСТЬ
        $table = new Music_Model_Table_Musics();                
        $user_musics = $table->select()
                ->setIntegrityCheck(false)
                ->from(array('m' => 'musics_music'), array('id', '("music") as tbl', 'date'))
                ->where('id_user = ?', $user_id)
        ;
        
        $table = new Events_Model_Table_Events();                
        $user_events = $table->select()
                ->setIntegrityCheck(false)
                //->from(array('e' => 'events_event'), array('id', '("event") as tbl', 'start_date as date'))
                ->from(array('e' => 'events_event'), array('id', '("event") as tbl', 'date'))
                ->where('author_id = ?', $user_id)
                ->where('is_confirmed = 1');

        $table = new Events_Model_Table_PhotoReports();                
        $user_reports = $table->select()
                ->setIntegrityCheck(false)
                //->from(array('f' => 'events_photoreport'), array('id', '("photoreport") as tbl', 'fe.start_date as date'))
                ->from(array('f' => 'events_photoreport'), array('id', '("photoreport") as tbl', 'f.date as date'))
                //->joinLeft(array('fe' => 'events_event'), 'fe.id = f.event_id', NULL)
                ->where('user_id = ?', $user_id)
                ->where('is_confirmed = 1');

        $table = new Post_Model_Table_Post();
        $user_posts = $table->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => 'post_posts'), array('id', '("post") as tbl', 'date'))                            
                ->where('user_id = ?', $user_id)
                ->where( 'status = 1' )
                ->where( 'rubric_id <> 1' );

        $user_videos = $table->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => 'post_posts'), array('id', '("video") as tbl', 'date'))                            
                ->where('user_id = ?', $user_id)
                ->where( 'status = 1' )
                ->where( 'rubric_id = 1' );
        
        $table = new Companies_Model_Table_Companies();                
        $user_companies = $table->select()
                ->setIntegrityCheck(false)
                ->from(array('c' => 'companies_company'), array('id', '("company") as tbl', 'signup_date as date'))
                ->joinLeft( array('ce' => 'companies_employer'), 'ce.id_company = c.id', NULL )
                ->where('ce.id_user = ?', $user_id)
                ->where('c.is_confirmed = 1');

        $db = $table->getAdapter();
        $main_select = $db->select()->from(
            $db->select()->union(array($user_musics, $user_events, $user_reports, $user_posts, $user_videos, $user_companies), Zend_Db_Select::SQL_UNION_ALL)
            )
            ->order('date DESC')
            ;
        
        $items = $db->fetchAll($main_select);
        
//        $items = $user->getActividad();
//        if (count($items) > 0) sksort($items, 'date');
        $this->view->items = $items;
        unset($items);

    }

    public function searchAction()
    {
        $table = new Application_Model_Table_Users();
        $this->view->users = $table->getAllUsers();
    }
    
    public function uploadavatarAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $name = $this->_helper->uploader( Zend_Registry::get('upload_path') . "/tmp" );
        $path = Zend_Registry::get('upload_path') . "/tmp/$name";
        if ( ! is_file( $path ) ) {
                throw new Exception( 'Файл не загружен' );
        }
        $size = getimagesize($path);
        if ($size[0] < 200 || $size[1] < 200) return false; 
        if ($size[0] > 600) {
            if ( ! $this->_helper->uploader->resize( $path, 600 ) ) {
                    throw new Exception( 'Error al hacer zoom' );
                    $size = getimagesize($path);
            }
        }    
        
        $data = array();
        $data['name'] = $name;                  
        $data['width'] = floor(200 / ($size[0] / 600));
        $aspect = round($size[1] / $size[0], 3);
        $data['height'] = floor(200 / ($size[1] / $size[1] + ((600 - $size[0]) * $aspect)) );

        $this->getResponse()->setBody( json_encode($data) );
            
    }    

}

