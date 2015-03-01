<?php
/**
 * 
 * Bootstrap модуля Auto
 *
 */

class Auto_Bootstrap extends Zend_Application_Module_Bootstrap {

    public function initResourceLoader()  {
        $loader = $this->getResourceLoader();
        $loader->addResourceType('helper', 'helpers', 'Helper');
    }

    public function activeInitHelpers() {
        Zend_Controller_Action_HelperBroker::addHelper( new Auto_Helper_HandleFilter() );
        Zend_Controller_Action_HelperBroker::addHelper( new Qlick_Controller_Helper_Lent() );
        //Zend_Controller_Action_HelperBroker::addHelper( new Auto_Helper_Navigation() );
    }

    protected function _initRouters() {
        $this->bootstrap('frontcontroller');
        $router = $this->frontController->getRouter();
        
        $defaultRoute = new Zend_Controller_Router_Route_Hostname(
            Zend_Registry::get('server_name'),
            array(
                'module' => 'auto'
            )
        );        
        
        $router->addRoute('allauto', 
                $defaultRoute->chain(new Zend_Controller_Router_Route(
                        'vehiculos',
                        array('module'=>'auto', 'controller'=>'car', 'action'=>'filter')
                ))
        );
        $router->addRoute('new_ad', new Zend_Controller_Router_Route('create-ad', array('module' => 'auto', 'controller' => 'car', 'action'=>'new')));
        $router->addRoute('carupload', new Zend_Controller_Router_Route('car/upload', array('module' => 'auto', 'controller' => 'car', 'action'=>'upload')));
//        $router->addRoute('cardev', new Zend_Controller_Router_Route('cars/devs', array('module' => 'auto', 'controller' => 'car', 'action'=>'devs')));
//        $router->addRoute('carcompanies', new Zend_Controller_Router_Route('cars/companies', array('module' => 'auto', 'controller' => 'car', 'action'=>'companies')));
//        $router->addRoute('carlents', new Zend_Controller_Router_Route('cars/lents', array('module' => 'auto', 'controller' => 'car', 'action'=>'lents')));
//        $router->addRoute('carfilter', new Zend_Controller_Router_Route('cars/filter/:modelId', array('module' => 'auto', 'controller' => 'car', 'action'=>'filter')));
//        $router->addRoute('car',
//             new Zend_Controller_Router_Route_Regex(
//                'car(\d+)/?(index|view|new|edit|delete)?',
//                array('module'=>'auto', 'controller'=>'car', 'action'=>'view'),
//                array(1 => 'carId', 2 => 'action'),
//                'car%d/%s'
//             )
//        );
        
        // объявы с ЧПУ
        $router->addRoute(
            'car_ad',
            $defaultRoute->chain( new Zend_Controller_Router_Route('vehiculos/:car_mark/:car_series/ad/:carId/:action', array('module' => 'auto', 'controller' => 'car', 'action' => 'view')) )
        );
        
        $router->addRoute(
            'car_ad_adm',
            $defaultRoute->chain( new Zend_Controller_Router_Route('vehiculos/ad/:carId/:action', array('module' => 'auto', 'controller' => 'car', 'action' => 'view')) )
        );
        
        $router->addRoute('car_series',
             $defaultRoute->chain( new Zend_Controller_Router_Route('vehiculos/:car_mark', array('module' => 'auto', 'controller' => 'model', 'action' => 'marks')) )
        );
        
        $router->addRoute('car_models',
             $defaultRoute->chain( new Zend_Controller_Router_Route('vehiculos/:car_mark/:car_series', array('module' => 'auto', 'controller' => 'model', 'action' => 'models')) )
        );
        
        $router->addRoute('carfilter',
             new Zend_Controller_Router_Route_Regex(
                'cars/filter/?(\d+)?',
                array('module'=>'auto', 'controller'=>'car', 'action'=>'filter'),
                array(1 => 'modelId'),
                'cars/filter'
             )
        );
//        $router->addRoute('carmodel',
//             new Zend_Controller_Router_Route_Regex(
//                'cars/model(\d+)/?(index|view)?',
//                array('module'=>'auto', 'controller'=>'model', 'action'=>'view'),
//                array(1 => 'modelId', 2 => 'action'),
//                'cars/model%d'
//             )
//        ); 

    }

}