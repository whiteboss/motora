<?php
/**
 * 
 * Основной контроллер
 * 
 */

class Auto_IndexController extends Zend_Controller_Action {

	public function indexAction() {
//		$this->_helper->initInterface();
//		$this->_helper->handleFilter();
            $this->_redirect($this->view->url(array(), 'carfilter'));
	}
}