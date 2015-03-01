<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
        protected function _initAppAutoload()
        {
            $autoloader = new Zend_Application_Module_Autoloader(array(
                'namespace' => 'App',
                'basePath' => dirname(__FILE__),
            ));
            return $autoloader;
        }
        
        /*
        * Инициализация системы логгирования
        */
	protected function _initSyslog()
	{
            $resource = $this->getPluginResource('db');
            $dbAdapter = $resource->getDbAdapter();
            $columnMapping = array(
                'priority'      => 'priority',
                'priorityname'  => 'priorityName',
                'module'        => 'module',
                'controller'    => 'controller',
                'action'        => 'action',
                'message'       => 'message',
                'eventtime'     => 'timestamp',
                'userid'        => 'userid',
                'userip'        => 'userip',
                'userhost'      => 'userhost',
            );
            $writer = new Zend_Log_Writer_Db($dbAdapter, 'log', $columnMapping);

            $logger = new Qlick_Log($writer);
            $logger->setTimestampFormat( "Y-m-d H:m:s" );
            Zend_Registry::set('logger', $logger);
	}  
        
        // авторизация для поддоменов
        protected function _initSession()
        {
            // Запускаем сессию с параметрами
            Zend_Session::start(
                array(
                    'cookie_domain' => '.' . $this->getOption('server_name'),
                    'cookie_httponly' => 'on'
                )
            );

            $auth = Zend_Auth::getInstance();
            return $auth;
        }
        
        protected function _initHelpers()
        {
            $this->bootstrap('frontController');
            //Zend_Controller_Action_HelperBroker::addHelper(new Qlick_LayoutLoader());
            Zend_Controller_Action_HelperBroker::addHelper(new Qlick_Controller_Helper_InitInterface());
            Zend_Controller_Action_HelperBroker::addHelper(new Qlick_Controller_Helper_InitInterfaceUI());
            Zend_Controller_Action_HelperBroker::addHelper(new Qlick_Controller_Helper_Uploader());                        
        }

        // Пропишем пути для тюнингованных общих форм и элементов
        protected function _initModifiedView()
        {
            $this->bootstrap( 'View' );
            $view = $this->getResource( 'View' );
            $view->setScriptPath( realpath( APPLICATION_PATH . '/tuning/elements' ) );
        }

        protected function _initViewHelpers()
        {
            $this->bootstrap('layout');
            $layout = $this->getResource('layout');
            $this->bootstrap('view');
            $view = $layout->getView();
            $view->doctype("XHTML1_RDFA");
            $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
            $view->headTitle()->setSeparator(' — ');
            //$view->headMeta()->setName( 'keywords', 'Chile, Santiago, eventos, restaurantes, clubs, ferias, vida nocturna' );
            $view->headMeta()->setName( 'description', 'Noticias y agenda de vida nocturna, la música, el arte y la moda de Chile' );
        }

        // система сообщений
        protected function _initFlashMessenger()
        {
            //$fm = Zend_Controller_Action_HelperBroker::getStaticHelper("FlashMessenger");            
            //if ( $fm->hasMessages() ) {                
            //    $view = $this->getResource( 'view' );
            //    $view->notifications = $fm->getMessages();
            //}
        }

        protected function _initRouters()
        {

            $router = Zend_Controller_Front::getInstance()->getRouter();

            $indexRoute = new Zend_Controller_Router_Route(
                ':action',
                array(
                    'controller'=> 'index',
                    'action' => 'index'   
                )
            );
            
            $ip = $_SERVER['REMOTE_ADDR'];
            
            if ($ip == '190.46.208.122' || $ip == '188.168.204.83' || $ip == '127.0.0.1') {
                $detect = new Qlick_MobileDetect();
                $deviceType = ($detect->isMobile() ? 'mobile' : 'computer');

                if ($deviceType == 'computer')
                    Zend_Registry::set( 'server_name', $this->getOption('server_name') );
                else
                    Zend_Registry::set( 'server_name', 'm.' . $this->getOption('server_name') );
                
            } else {
                Zend_Registry::set( 'server_name', $this->getOption('server_name') );    
            }    
            
            // основной
            $defaultRoute = new Zend_Controller_Router_Route_Hostname(
                Zend_Registry::get('server_name'),
                //'qlick',    
                array(
                    'module' => 'default',
                    'controller'=> 'index',
                    'action' => 'index'
                )
            );
            $router->addRoute('main', $defaultRoute->chain($indexRoute)); 
            
            $router->addRoute('search',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                   'search/(\w+)?q=(\w+)',
                   array('module'=>'default', 'controller'=>'index', 'action'=>'search'),
                   array(),
                   'search'
                ))
            );
            
            //$routerConfig = new Zend_Config_Ini(realpath( APPLICATION_PATH . '/configs/routes.ini'), 'index');            
            //$router->addConfig($routerConfig, 'index');

        }
	
	protected function _initUploadPath() {
		$path = $this->getOption('upload_path');
		if ( isset($path) ) {
			Zend_Registry::set( 'upload_path', realpath($path) );
		}
	}
        
        protected function _initFBApp() {
		$FB_app_ID = $this->getOption('FB_app_ID');
                $FB_app_secret = $this->getOption('FB_app_secret');
		if ( isset($FB_app_ID) && isset($FB_app_secret) ) {
			Zend_Registry::set( 'FB_app_ID', $FB_app_ID );
                        Zend_Registry::set( 'FB_app_secret', $FB_app_secret );
		}
	}
    
}

