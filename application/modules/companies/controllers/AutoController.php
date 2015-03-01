<?php
/**
 * 
 * Контроллер резюме
 * 
 */

class Companies_AutoController extends Zend_Controller_Action {	
	
//	/**
//	 * Инициализация
//	 * @return void
//	 */
	
	public function indexAction() {
		$this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );
		$this->_helper->initInterface();
		$companyId = ( int ) $this->getRequest()->getParam ( 'companyId' );
		if ( !empty($companyId) ) {
			$company = $this->_helper->companyProfile($companyId);
                        $this->view->active = 'auto';

                        $this->view->headTitle()->append('Компания ' . $company->name);
                        $this->view->headTitle()->append('Автомобили');
                        if (!is_null($company->description))
                            $this->view->headMeta()->setName( 'description', $company->description );                        
                        
			$this->view->items = $company->getAutos();
		}
	}


}