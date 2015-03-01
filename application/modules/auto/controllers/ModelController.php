<?php

/**
 * 
 * Контроллер моделей
 * 
 */
class Auto_ModelController extends Zend_Controller_Action {

    /**
     * Инициализация
     * @return void
     */
    public function preDispatch() {
        if ($this->view->isGodOfProject()) {
            $this->view->headScript()
                    ->appendFile('/js/auto/admin.js');
        }
    }

    public function marksAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();

        $filter = new Zend_Filter_StripTags();
        $mark_url = $filter->filter($this->getRequest()->getParam('car_mark'));

        if (!is_null($mark_url)) {

            $table = new Auto_Model_Table_CarMarks();
            $mark = $table->getMarkByUrl($mark_url);

            if (!is_null($mark)) {
                $this->view->mark = $mark;
                $table = new Auto_Model_Table_CarSeries();
                $this->view->series = $table->getSeriesByMarks(array($mark->id_car_mark));
            }
        }
    }
    
    public function modelsAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();

        $filter = new Zend_Filter_StripTags();
        $mark_url = $filter->filter($this->getRequest()->getParam('car_mark', null));
        $serie_url = $filter->filter($this->getRequest()->getParam('car_series', null));

        if (!is_null($mark_url) && !is_null($serie_url)) {

            $table = new Auto_Model_Table_CarMarks();
            $mark = $table->getMarkByUrl($mark_url);

            if (!is_null($mark)) {
                $this->view->mark = $mark;
                
                $table = new Auto_Model_Table_CarSeries();
                $serie = $table->getSerieByUrl($serie_url);
                
                $this->view->serie = $serie;
                
                if (!is_null($serie)) {
                    $table = new Auto_Model_Table_CarModels();
                    $this->view->modifications = $table->getModificationsBySeries(array($serie->id_series => $serie->name));
                } else {
                    $this->_helper->FlashMessenger(array('system_ok' => 'No hay versiónes'));
                    $this->_redirect($this->view->url(array('car_mark' => $mark_url), 'car_series'));                        
                }
            }
        }
    }

//    /**
//     * Получение данных по идентификатору
//     * @return void
//     */
//    public function viewAction() {
//
//        $this->getResponse()
//                ->setHeader('Content-Type', 'text/html; charset=utf-8');
//        $this->_helper->initInterface();
//        $this->_helper->initInterfaceUI();
//        $this->view->headScript()
//                ->appendFile('/resources/ulslide/jquery.ulslide.js')
//                ->appendFile('/js/auto/card.js')
//                ->appendFile('/js/comments.js');
//        $this->_helper->handleFilter();
//        $modelId = (int) $this->getRequest()->getParam('modelId');
//        if (!empty($modelId)) {
//            $table = new Auto_Model_Table_CarSeries();
//            $items = $table->find($modelId);
//            if (count($items) > 0) {
//                $model = $items->current();
//                $this->view->model = $model;
//                $this->view->offers = $model->getCountOffers();
//                $this->view->headTitle()->prepend('Модель ' . $model->getBrand() . " " . $model->name);
//                if ($this->view->getLogged()) {
//                    $commentForm = $this->_helper->comments->commentform("carmodel", $modelId);
//                    $this->view->commentForm = $commentForm;
//                }
//                $commentsList = $this->_helper->comments->commentslist("carmodel", $modelId);
//                $this->view->commentsList = $commentsList;
//            } else {
//                throw new Exception('Неизвестный идентификатор');
//            }
//        } else {
//            $this->_redirect($this->view->url(array('brandId' => '6'), 'carbrand'));
//        }
//    }

    /**
     * Элемент формы мультичекбокс со списком моделей для заданных кузовов и марок
     * @return void
     */
    public function getseriesAction() {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

        $request = $this->getRequest();
        $mark_ids = (array) $request->getParam('mark');
//        $with_null = (int) $request->getParam('with_null', 0);

//		if ( !empty( $is_edit ) ) {
//			$element = new Zend_Form_Element_Select( 'serie'); // для формы добавления
//                        $element->setDecorators(array(array('ViewScript', array(
//                            'viewScript'    => 'standart_nulledselect.phtml'
//                        ))));
//		} else {
//			$element = new Zend_Form_Element_MultiCheckbox( 'model');
//                        $element->setDecorators(array(array('ViewScript', array(
//                            'viewScript'    => 'model_multicheckbox.phtml'
//                        ))));
//		}
//		$element->removeDecorator( 'Label' );
        if (!empty($mark_ids)) {
            $table = new Auto_Model_Table_CarSeries();
            $items = $table->getSeriesByMarks($mark_ids, true);
//			$element->setMultiOptions( $items );    
            $this->getResponse()->setBody(
                    //$element->render()
                    json_encode($items)
            );
        }
    }

    public function getmodelsAction() {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

        $request = $this->getRequest();
        $serie_ids = (array) $request->getParam('serie');

        if (!empty($serie_ids)) {
            $table = new Auto_Model_Table_CarModels();
            $items = $table->getModelsBySeries($serie_ids, false, true);
//			$element->setMultiOptions( $items ); 
            $this->getResponse()->setBody(
                    //$element->render()
                    json_encode($items)
            );
        }
    }

    public function getmodificationAction() {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

        $request = $this->getRequest();
        $id_model = (int) $request->getParam('id_model');
        if ($id_model > 0) {
            $table = new Auto_Model_Table_CarModels();
            $rows = $table->find($id_model);
            if (count($rows) > 0) {
                $row = $rows->current();
                $items = $row->getModifications(true);
                if (!is_null($items))
                    $this->getResponse()->setBody(
                            json_encode($items)
                    );
                else
                    $this->getResponse()->setBody(
                            json_encode(array('id' => '0', 'name' => '- Elección de modificación -'))
                    );
            }
        } else {
            $this->getResponse()->setBody(
                    json_encode(array('id' => '0', 'name' => '- Elección de modificación -'))
            );
        }
    }

}
