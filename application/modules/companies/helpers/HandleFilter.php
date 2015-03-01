<?php
/**
 * 
 * Обработка формы фильтра
 *
 */

class Companies_Helper_HandleFilter extends Zend_Controller_Action_Helper_Abstract {
	protected $view;

	/**
	 * Поиск компаний, результаты передаются в объект вида
	 * @return void
	*/
	public function handleFilter() {
            //$this->getView()->catalog = Companies_Model_Company::getCatalog();
	}

	/**
	 * Возвращает текущий объект вида
	 * @return Zend_View
	*/
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
		return $this->handleFilter();
	}
}