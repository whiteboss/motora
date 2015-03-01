<?php
/**
 * 
 * Хелпер отвечает за общую инициализацию интерфейса
 *
 */

class Qlick_Controller_Helper_InitInterface extends Zend_Controller_Action_Helper_Abstract {
	protected $view;
	
	public function initInterface($version = '1.7.2') {
		if (null === ($controller = $this->getActionController())) {
			return;
		}
//		$this->getResponse()
//			->setHeader('Content-Type', 'text/html; charset=utf-8' );
		$view = $this->getView();
		$view->headMeta()
			->setHttpEquiv('Content-Type', 'text/html; charset=utf-8' );
                
		$view->jQuery()
			->setLocalPath($view->baseUrl('/resources/jquery/js/jquery-' . $version . '.min.js'))->enable();
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
	
	public function direct($version = '1.7.2') {
		return $this->initInterface($version);
	}
}