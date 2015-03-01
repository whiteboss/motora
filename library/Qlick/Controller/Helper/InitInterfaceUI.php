<?php
/**
 * 
 * Хелпер отвечает за общую инициализацию интерфейса
 *
 */

class Qlick_Controller_Helper_InitInterfaceUI extends Zend_Controller_Action_Helper_Abstract {
	protected $view;
	
	public function initInterfaceUI() {
		if (null === ($controller = $this->getActionController())) {
			return;
		}

		$view = $this->getView();
		
		$view->jQuery()
			->setUiLocalPath($view->baseUrl('/resources/jquery/js/jquery-ui-1.8.9.custom.min.js'))->uiEnable()
                        ->addStylesheet($view->baseUrl('/resources/jquery/css/ui-lightness/jquery-ui-1.8.9.custom.css'));
	}
	
	public function getView() {
		if (null !== $this->view) {
			return $this->view;
		}
		$controller = $this->getActionController();
		$view = $controller->view;
		if (!$view instanceof Zend_View_Abstract) {
			return;
		}
		$this->view = $view;
		return $view;
	}
	
	public function direct() {
		return $this->initInterfaceUI();
	}
}