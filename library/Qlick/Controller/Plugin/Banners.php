<?php

class Qlick_Controller_Plugin_Banners extends Zend_Controller_Plugin_Abstract
{
   
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        
        $uri = array();
        $module = $request->getModuleName();

        if ($module != 'default' && !is_null($module)) 
            $uri['module'] = $module;
        else
            unset($uri['module']);
        $uri['controller'] = $request->getControllerName();
        
        if (($module == 'default' || is_null($module)) && $uri['controller'] == 'index') 
            $uri['action'] = $request->getActionName();
        
//        var_dump(print_r($uri));
//        die();

        $table = new Application_Model_Table_Banners();
        $this->top_banner = $table->getTopBannerByUri( $uri );
        $this->left_banner = $table->getLeftBannerByUri( $uri );
        
        $viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer');
        $viewRenderer->initView();
        $view = $viewRenderer->view;
        $view->top_banner = $this->top_banner;
        $view->left_banner = $this->left_banner;

    }
    
}

?>
