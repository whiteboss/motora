<?php
/**
 * 
 * Основной контроллер
 * 
 */

class Companies_IndexController extends Zend_Controller_Action {

	public function indexAction()
        {
            
            $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
            
            $this->_helper->initInterface();
            //$this->_helper->handleFilter();
            $this->view->headScript()
                ->appendFile( '/js/companies/filter.js' );

            $type = (String) $this->getRequest()->getParam('company_type');
//            $company_type = NULL;
            if (!empty($type)) {
                $filter = new Zend_Filter_StripTags();
                $company_type = $filter->filter(mb_ereg_replace('-', ' ', $type)); 
                if (!in_array($company_type, Companies_Model_Company::$_types)) $this->_redirect($this->view->url(array(), 'companies'));
                $this->view->company_type = $company_type;
            }
            //$sphereId = (int) $this->getRequest()->getParam('sphere');
            $from = (int) $this->_getParam("from");
            
//            $table = new Companies_Model_Table_Companies();            
//            $this->view->popular_companies = $table->getLugaresPopulares(11);            
//            $table = new Post_Model_Table_Post();
//            $this->view->popular_posts = $table->getPosts(null, 0, 10, false);
            
            $table = new Companies_Model_Table_Companies();
            
            // EVENTS
            // right block
            $eventManager = new Events_Model_Table_Events();
            $items = array();
            $events = $eventManager->getComingEvents();
            if (count($events) > 5) {
                srand((float) microtime() * 10000000);
                $random_events = array_rand($events, 5);
                $items = array();
                foreach ($random_events as $key=>$event)
                    $items[$key] = $events[$event];

                $this->view->r_events = $items;
                unset($items);
            } else {
                $this->view->r_events = $events;
            }
            // ------
            
            
            if (!empty($type)) {
//                $company_types = Companies_Model_Company::getHeaderTypes();
//                if (array_key_exists($type, $company_types)) {
                    $this->view->headTitle()->append(ucfirst($company_type) . ' de Santiago');
                    //$this->view->header = $company_types[$type];
                    if ($from > 0) {
                        $this->_helper->layout->disableLayout();                        
                        $this->view->from = $from + 1;
                        $companies = $table->getListByType(array($company_type), $from);
                        if (count($companies) > 0) {
                            $this->_helper->viewRenderer->setRender ('morecompanies');
                            $this->view->companies = $companies;                            
                        } else {
                            $this->_helper->viewRenderer->setNoRender();                       
                        }    
                    } else {
                        $this->view->companies = $table->getListByType(array($company_type), 0, Companies_Model_Company::$company_per_lazypage);
                    }    
                    return;
//                }
            }

            $this->view->headTitle()->append('Lugares & empresas de Santiago');
            if ($from > 0) {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setRender ('morecompanies');
                $this->view->from = $from + 1;
                $this->view->companies = $table->getCompanies($from);
            } else {
                $this->view->companies = $table->getCompanies(0, Companies_Model_Company::$company_per_lazypage);
            }
	}
        
}