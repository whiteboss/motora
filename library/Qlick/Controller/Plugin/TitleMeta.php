<?php

class Qlick_Controller_Plugin_TitleMeta extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {        
//        $uri = array();
//        $uri['module'] = $request->getModuleName();
//        $uri['controller'] = $request->getControllerName();
//        $uri['action'] = $request->getActionName();
//
//        $sitemap = new Application_Model_Table_SiteMap();
//        $this->page_data = $sitemap->getNodeInfoByUri( $uri );
//
//        Zend_Registry::set('page_data', $this->page_data);
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
            // Set title, keywords and description
            $layout = Zend_Layout::getMvcInstance();
            $view   = $layout->getView();

            //$view->headTitle($this->page_data['title']);
            $view->headTitle()->append('Motora.cl, pimp my ride');
            //$view->headMeta($this->page_data['description'], 'description');
    }
}

?>
