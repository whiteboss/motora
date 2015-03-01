<?php

/**
 * 
 * Обработка формы фильтра
 *
 */
class Auto_Helper_HandleFilter extends Zend_Controller_Action_Helper_Abstract {

    protected $view;

    public function handleFilter() {
        
        $form = $this->getCarSearchForm();
        $this->getView()->car_search_form = $form;
        $request = $this->getRequest();
        
        $from = (int) $request->getParam("from");
        
        if ($request->isGet()) {            
            $data = $request->getQuery();
            //if ( isset($data['car_search']) ) {
            if ($form->isValid($data)) {
                
                $data = $form->getValues();
                
                $modelId = (int) $request->getParam('modelId');
                if (!empty($modelId))
                    $data['model'] = array($modelId);
                
                $data['from'] = $from;
                
                $this->searchCar($data);
                if (!empty($data['body']) || !empty($data['brand'])) {
                    if (!isset($data['body'])) $data['body'] = array();
                    if (!isset($data['brand'])) $data['brand'] = array();
                    $table = new Auto_Model_Table_CarSeries();
                    $items = $table->getListByBodyBrand($data['body'], $data['brand'], true);
                    if (count($items) > 0) {
                        $this->getView()->car_search_form->model->setMultiOptions($items);
                        //$this->getView()->car_search_form->extra->getDecorator('HtmlTag')->setOption('style', '');
                    }
                } elseif (!empty($data['modelId'])) {
                    //
                    //if (empty($modelId))
                    //    return;
//                    $table = new Auto_Model_Table_CarSeries();
//                    $items = $table->find($data['modelId']);
//                    if (count($items) > 0) {
//                        $model = $items->current();
//                        //$this->getView()->models = array($modelId => $model);
//                        $this->getView()->items = $model->getCars(); //array($modelId => $model->getCars());
//                    } else {
//                        throw new Exception('Неизвестный идентификатор');
//                    }
                }
                //$this->getView()->car_search_form->setDefaults( $data );
            } else {
                //throw new Exception('1');
                $table = new Auto_Model_Table_CarAds();
                $data = array();
                $this->getView()->items = $table->filter( $data );                
            }
        } else {            
            throw new Exception('2');
//            $table = new Auto_Model_Table_CarAds();
//            $data = array();
//            $this->getView()->items = $table->filter( $data, $from );
        }
    }

    /**
     * Возвращает форму поиска авто
     * @return Auto_Form_CarSearch
     */
    public function getCarSearchForm() {
        $form = new Auto_Form_CarSearch();
        $form->setTemplate('/forms/carsearch');
        //$bodytable = new Auto_Model_Table_CarBody();
        //$form->body->setMultiOptions($bodytable->getAll());
        //$brandtable = new Auto_Model_Table_CarMarks();
        //$form->mark->setMultiOptions($brandtable->getAll());
        return $form;
    }

    /**
     * Поиск авто, результаты передаются в объект вида
     * @return void
     */
    public function searchCar(array $data) {
        if (!isset($data['body'])) $data['body'] = array();
        if (!isset($data['brand'])) $data['brand'] = array();
        $table = new Auto_Model_Table_CarSeries();
        if (empty($data['model'])) {
//            // если список моделей не задан - ищем по всем моделям кузовов и марок
//            $models = $table->getListByBodyBrand($data['body'], $data['brand']);
//            $data['model'] = array_keys($models);
            $data['model'] = array();
//        } else {            
//            // список моделей нужен в любом случае
//            $rows = $table->find($data['model']);
//            $models = array();
//            foreach ($rows as $row)
//                $models[$row->id] = $row;
        }
//        //$this->getView()->models = $models;
//        if (empty($models))
//            return;

        $table = new Auto_Model_Table_CarAds();
        // список объявлений
        if ($data['from'] > 0) { 
            // ajax            
            $cars = $table->filter($data, $data['from']);
            $this->getView()->from = $data['from'] + 1;
            $this->getView()->items = $cars;
        } else {
            $this->getView()->items = $table->filter($data, 0, Auto_Model_CarAd::$car_per_lazypage);
        } 
        
    }

//    /**
//     * Поиск запчастей, результаты передаются в объект вида
//     * @return void
//     */
//    public function searchSpares(array $data) {
//        if (empty($data['subcategory'])) {
//            if (empty($data['category']))
//                return;
//            // если подкатегория не задана - ищем по всем подкатегориям
//            $table = new Auto_Model_Table_SparesCatalog();
//            $category = $table->find($data['category'])->current();
//            $data['subcategory'] = array_keys($category->getChildrenList());
//        }
//        $table = new Auto_Model_Table_SparesItem();
//        // список объявлений
//        $this->getView()->items = $table->getListByCategory($data['subcategory']);
//    }

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