<?php

class Users_Bootstrap extends Zend_Application_Module_Bootstrap
{

    	public function initResourceLoader()  {
            $loader = $this->getResourceLoader();
            $loader->addResourceType('helper', 'helpers', 'Helper');
	}
        
        protected function _initRouters()
        {
            $this->bootstrap('frontcontroller');
            $router = $this->frontController->getRouter(); 
            
            $defaultRoute = new Zend_Controller_Router_Route_Hostname(
                Zend_Registry::get('server_name'),
                array(
                    'module' => 'users'
                )
            );            
            
            $router->addRoute('signup', $defaultRoute->chain(new Zend_Controller_Router_Route_Static('signup', array('module'=>'users','controller'=>'index','action'=>'signup')) ) );
            $router->addRoute('userconfirm', new Zend_Controller_Router_Route('users/confirm/uid/:uid', array('module'=>'users','controller'=>'index','action'=>'confirm')));
            $router->addRoute('signin', $defaultRoute->chain(new Zend_Controller_Router_Route('signin', array('module'=>'users','controller'=>'index','action'=>'signin')) ) );            
            $router->addRoute('userrestore', $defaultRoute->chain(new Zend_Controller_Router_Route_Static('users/restore', array('module'=>'users','controller'=>'index','action'=>'passwordrestore')) ) );            
            $router->addRoute('uploaduseravatar', new Zend_Controller_Router_Route_Static('users/uploadavatar', array('module'=>'users','controller'=>'index','action'=>'uploadavatar')));            
            $router->addRoute('calcrating', $defaultRoute->chain(new Zend_Controller_Router_Route_Static('users/calcrating', array('module'=>'users','controller'=>'index','action'=>'calcrate')) ) );            
            $router->addRoute('auth',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'auth/?(facebook)?',
                    array('module' => 'users', 'controller' => 'index', 'action' => 'auth', 'social' => 'facebook'),
                    array(1 => 'social'),
                    'auth/%s'
                ))
            );
            
            $router->addRoute('logout',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'logout/?(facebook)?',
                    array('module' => 'users', 'controller' => 'index', 'action' => 'logout', 'social' => 'facebook'),
                    array(1 => 'social'),
                    'logout/%s'
                ))
            );
            
            $router->addRoute('profile',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'user(\d+)/?(profile|restore|calcrate|updateavatar)?',
                    array('module' => 'users', 'controller' => 'index', 'action' => 'profile'),
                    array(1 => 'userId', 2 => 'action'),
                    'user%d/%s'
                ))
            );
            
            $router->addRoute('peoples', 
                    $defaultRoute->chain(new Zend_Controller_Router_Route(
                            'peoples/:sex', 
                            array('module'=>'users','controller'=>'index','action'=>'peoples', 'sex'=>'all')
                    ))
            );
            
            $router->addRoute('usermessages',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'messages/?(inbox|outbox|send)?',
                    array('module' => 'users', 'controller' => 'messages', 'action' => 'inbox'),
                    array(1 => 'action'),
                    'messages/%s'
                ))
            );
            
            $router->addRoute('replyto',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'messages/reply(\d+)',
                    array('module' => 'users', 'controller' => 'messages', 'action' => 'send'),
                    array(1 => 'messageId'),
                    'messages/reply%d'
                ))
            );

            $router->addRoute('usermessagesto',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'messages/to(\d+)',
                    array('module' => 'users', 'controller' => 'messages', 'action' => 'send'),
                    array(1 => 'userId'),
                    'messages/to%d'
                ))
            );
            
            $router->addRoute('usermessage',
                new Zend_Controller_Router_Route_Regex(
                    'message(\d+)/(view|deleteinbox|deleteoutbox)',
                    array('module' => 'users', 'controller' => 'messages', 'action' => 'view'),
                    array(1 => 'messageId', 2 => 'action'),
                    'message%d/%s'
                )
            );
            
            // ЛЕНТЫ
            $router->addRoute('userfeeds',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'user(\d+)/feeds',
                    array('module' => 'users', 'controller' => 'index', 'action' => 'feeds'),
                    array(1 => 'userId'),
                    'user%d/feeds'
                ))
            );
            
            // ДРУЗЬЯ
            //$router->addRoute('friends', new Zend_Controller_Router_Route('friends', array('module'=>'users','controller'=>'friends','action'=>'friends')));
            $router->addRoute('userfriends',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'user(\d+)/friends',
                    array('module' => 'users', 'controller' => 'friends', 'action' => 'friends'),
                    array(1 => 'userId'),
                    'user%d/friends'
                ))
            );
            $router->addRoute('userfriend',
                new Zend_Controller_Router_Route_Regex(
                    'friend(\d+)/?(accept|remove)?',
                    array('module' => 'users', 'controller' => 'friends'),
                    array(1 => 'friendId', 2 => 'action'),
                    'friend%d/%s'
                )
            );
            
            $router->addRoute('friendaccept',
                new Zend_Controller_Router_Route_Regex(
                    'request(\d+)/accept',
                    array('module' => 'users', 'controller' => 'friends', 'action' => 'accept'),
                    array(1 => 'requestId'),
                    'request%d/accept'
                )
            );
            
            $router->addRoute('frienddiscard',
                new Zend_Controller_Router_Route_Regex(
                    'request(\d+)/discard',
                    array('module' => 'users', 'controller' => 'friends', 'action' => 'profilediscard'),
                    array(1 => 'requestId'),
                    'request%d/discard'
                )
            );
            
            $router->addRoute('userFBfriends', new Zend_Controller_Router_Route('users/fbfriends', array('module'=>'users','controller'=>'index','action'=>'fbfriends')));
           
            // ФОТО
            //$router->addRoute('mygalleries', new Zend_Controller_Router_Route('galleries', array('module'=>'users','controller'=>'index','action'=>'galeeries')));
            $router->addRoute('options', $defaultRoute->chain(new Zend_Controller_Router_Route('options/:action', array('module'=>'users','controller'=>'options','action'=>'edit'))) );
            //$router->addRoute('uploadavatar', new Zend_Controller_Router_Route_Static('uploadavatar', array('module'=>'users','controller'=>'options','action'=>'upload')));
            //$router->addRoute('posts', new Zend_Controller_Router_Route('posts', array('module'=>'users','controller'=>'index','action'=>'posts')));
            $router->addRoute('userposts',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'user(\d+)/posts',
                    array('module' => 'users', 'controller' => 'index', 'action' => 'posts'),
                    array(1 => 'userId'),
                    'user%d/posts'
                ))
            );
            //$router->addRoute('myevents', new Zend_Controller_Router_Route('myevents/:action', array('module'=>'users','controller'=>'index','action'=>'events')));
            $router->addRoute('userevents',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'user(\d+)/events',
                    array('module' => 'users', 'controller' => 'index', 'action' => 'events'),
                    array(1 => 'userId'),
                    'user%d/events'
                ))
            );
            
            $router->addRoute('uservideos',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'user(\d+)/videos',
                    array('module' => 'users', 'controller' => 'index', 'action' => 'videos'),
                    array(1 => 'userId'),
                    'user%d/videos'
                ))
            );
            
            $router->addRoute('usermusics',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'user(\d+)/musics',
                    array('module' => 'users', 'controller' => 'index', 'action' => 'musics'),
                    array(1 => 'userId'),
                    'user%d/musics'
                ))
            );            
            
            //$router->addRoute('mygalleries', new Zend_Controller_Router_Route('mygalleries/:action', array('module'=>'users','controller'=>'index','action'=>'galleries')));
            $router->addRoute('usergalleries',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'user(\d+)/galleries',
                    array('module' => 'users', 'controller' => 'index', 'action' => 'galleries'),
                    array(1 => 'userId'),
                    'user%d/galleries'
                ))
            );
            //$router->addRoute('mycompanies', new Zend_Controller_Router_Route('mycompanies/:action', array('module'=>'users','controller'=>'index','action'=>'companies')));
            $router->addRoute('usercompanies',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'user(\d+)/companies',
                    array('module' => 'users', 'controller' => 'index', 'action' => 'companies'),
                    array(1 => 'userId'),
                    'user%d/companies'
                ))
            );
            
            $router->addRoute('userresume',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                    'user(\d+)/resume/?(view|new|edit|delete|open)?',
                    array('module' => 'users', 'controller' => 'resume', 'action' => 'view'),
                    array(1 => 'userId', 2 => 'action'),
                    'user%d/resume/%s'
                ))
            );            
            // ****            
            //$router->addRoute('search', new Zend_Controller_Router_Route_Static('users/search', array('module'=>'users','controller'=>'index','action'=>'search')));

        }

        public function activeInitHelpers() {
            Zend_Controller_Action_HelperBroker::addHelper( new Users_Helper_UserProfile() );
	}

}

