<?php

class Companies_Bootstrap extends Zend_Application_Module_Bootstrap {

    public function initResourceLoader()  {
        $loader = $this->getResourceLoader();
        $loader->addResourceType('helper', 'helpers', 'Helper');
    }

    public function activeInitHelpers() {
        //Zend_Controller_Action_HelperBroker::addHelper( new Qlick_Controller_Helper_Comments );
        //Zend_Controller_Action_HelperBroker::addHelper( new Companies_Helper_HandleFilter() );
        Zend_Controller_Action_HelperBroker::addHelper( new Companies_Helper_CompanyProfile() );
        Zend_Controller_Action_HelperBroker::addHelper(new Qlick_Controller_Helper_ClearJson());
        //Zend_Controller_Action_HelperBroker::addHelper( new Qlick_Controller_Helper_Lent() );
    }

    protected function _initRouters() {
        $this->bootstrap('frontcontroller');
        $router = $this->frontController->getRouter();
        
        $defaultRoute = new Zend_Controller_Router_Route_Hostname(
            Zend_Registry::get('server_name'),
            array(
                'module' => 'companies'
            )
        );        
        
        $router->addRoute('companies',
                $defaultRoute->chain(new Zend_Controller_Router_Route(
                        'lugares',
                        array('module'=>'companies', 'controller'=>'index', 'action'=>'index')
                ))
        );
        
        $router->addRoute('createcompany', new Zend_Controller_Router_Route('createcompany', array('module' => 'companies', 'controller' => 'company', 'action'=>'new')));
        //$router->addRoute('companiescatalog', new Zend_Controller_Router_Route('companies/catalog', array('module' => 'companies', 'controller' => 'company', 'action'=>'catalog')));
        //$router->addRoute('allvacancies', new Zend_Controller_Router_Route('companies/vacancies', array('module' => 'companies', 'controller' => 'company', 'action'=>'allvacancies')));        
        //$router->addRoute('companytype', new Zend_Controller_Router_Route('companies/activities', array('module' => 'companies', 'controller' => 'company', 'action'=>'activitytype')));
        $router->addRoute('uploadcompany', new Zend_Controller_Router_Route('companies/upload', array('module' => 'companies', 'controller' => 'company', 'action'=>'upload')));
        $router->addRoute('uploadcompanyavatar', new Zend_Controller_Router_Route('companies/uploadavatar', array('module' => 'companies', 'controller' => 'company', 'action'=>'uploadavatar')));
        //$router->addRoute('uploaddish', new Zend_Controller_Router_Route('menus/upload', array('module' => 'companies', 'controller' => 'menu', 'action'=>'upload')));
        //$router->addRoute('uploadtour', new Zend_Controller_Router_Route('tours/upload', array('module' => 'companies', 'controller' => 'tour', 'action'=>'upload')));
        //$router->addRoute('uploadroom', new Zend_Controller_Router_Route('hotel/upload', array('module' => 'companies', 'controller' => 'hotel', 'action'=>'upload')));
//        $router->addRoute('companybytype',
//             $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
//                'lugares?type=(\w+)',
//                array('module'=>'companies', 'controller'=>'index', 'action'=>'index'),
//                array(1 => 'type'),
//                'lugares?type=%s'
//             ))
//        );
        
        // события с ЧПУ
        $router->addRoute(
            'companybytype',
            $defaultRoute->chain( new Zend_Controller_Router_Route('lugares/:company_type', array('module' => 'companies', 'controller' => 'index', 'action' => 'index')) )
        );
        
//        $router->addRoute('companybysphere',
//             new Zend_Controller_Router_Route_Regex(
//                'companies?sphere=(\d+)',
//                array('module'=>'companies', 'controller'=>'index', 'action'=>'index'),
//                array(1 => 'sphereId'),
//                'companies?sphere=%d'
//             )
//        );        
        $router->addRoute('company',
             $defaultRoute->chain(new Zend_Controller_Router_Route_Regex(
                'company(\d+)/?(index|new|edit|confirm|profile|visitors|lents|photos|map|music|events|video|contacts|delete)?',
                array('module'=>'companies', 'controller'=>'company', 'action'=>'profile'),
                array(1 => 'companyId', 2 => 'action'),
                'company%d/%s'
             ))
        );
        
        // события с ЧПУ
        $router->addRoute(
            'company_seo',
            $defaultRoute->chain( new Zend_Controller_Router_Route('lugares/:company_type/:company_url/:action', array('module' => 'companies', 'controller' => 'company', 'action' => 'profile')) )
        );

    }

}