<?php

class Map_Bootstrap extends Zend_Application_Module_Bootstrap {

    public function initResourceLoader() {
        $loader = $this->getResourceLoader();
        $loader->addResourceType('helper', 'helpers', 'Helper');
    }

    public function activeInitHelpers() {
        //Zend_Controller_Action_HelperBroker::addHelper( new Qlick_Controller_Helper_Comments );
        Zend_Controller_Action_HelperBroker::addHelper( new Qlick_Controller_Helper_InitInterface() );
    }

    protected function _initRouters() {
        $this->bootstrap('frontcontroller');
        $router = $this->frontController->getRouter();
        
        $defaultRoute = new Zend_Controller_Router_Route_Hostname(
            'map.' . Zend_Registry::get('server_name'),
            array(
                'module' => 'map'
            )
        );
        $router->addRoute('map', $defaultRoute->chain(new Zend_Controller_Router_Route(
                ':action',
                array(
                    'controller'=> 'index',
                    'action' => 'index'   
                ))
        ));
        
        $router->addRoute('subcategory', new Zend_Controller_Router_Route('map/getsubcategory', array('module' => 'map', 'controller' => 'index', 'action'=>'getsubcategory')));
        
        $router->addRoute('mapbysection',
                $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                        'section(\d+)?',
                        array('controller'=> 'index', 'action' => 'index'),
                        array(1 => 'section'),
                        'section%d'
                ))
        );

    }

}

