<?php
class Application_Views_Helper_MainMenu extends Zend_View_Helper_Abstract
{
    public function MainMenu()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $module = $request->getModuleName(); 
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        $rubricId = $this->view->rubricId; //(int) $request->getParam('rubricId');
        
        $output = '<a class="Qinicio" href="' . $this->view->url(array('action' => 'index'), 'main') . '">Inicio</a>';
        
        $output .= '<a href="' . $this->view->url(array(), 'allauto') . '">Vehiculos</a>';
        $output .= '<a href="' . $this->view->url(array(), 'new_ad') . '">+ Ad</a>';

        return $output;

    }
    
}