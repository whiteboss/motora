<?php

/**
 * 
 * Контроллер объявлений авто
 * 
 */
class Auto_CarController extends Zend_Controller_Action {
    
    public function init() {
        $this->logger = Zend_Registry::get('logger');
    }    

    /**
     * Инициализация
     * @return void
     */
    public function preDispatch() {
        if ($this->view->isGodOfProject()) {
            $this->view->headScript()
                    ->appendFile('/js/auto/util.js');
        }
    }

    /**
     * Карточка объявления
     * @return void
     */
    public function viewAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->_helper->initInterface();
        $this->_helper->initInterfaceUI();
        $this->view->headScript()
                //->appendFile( '/resources/ulslide/jquery.mousewheel.js' )
                ->appendFile('/resources/ulslide/jquery.ulslide.js')
                //->appendFile('/js/auto/card.js')
                ->appendFile('/js/comments.js');

        //$this->_helper->handleFilter();
        $carId = (int) $this->getRequest()->getParam('carId');
        if (!empty($carId)) {
            $table = new Auto_Model_Table_CarAds();
            $items = $table->find($carId);
            if (count($items) > 0) {
                $item = $items->current();
                $this->view->item = $item->count();
                //$this->view->items = $table->getSimilarCars($item, 10);
                
//                if ($this->view->getLogged()) {
//                    $commentForm = $this->_helper->comments->commentform('car', $carId);
//                    $this->view->commentForm = $commentForm;
//                }
//                $commentsList = $this->_helper->comments->commentslist('car', $carId);
//                $this->view->commentsList = $commentsList;
            } else {
                //throw new Exception('Неизвестный идентификатор car' . $carId);
                $this->_helper->FlashMessenger( 'Объявление не найдено' );
                $this->_redirect( $this->view->url(array(''), 'carfilter') );
            }
        } else {
            //throw new Exception('Не задан идентификатор car');
            $this->_redirect( $this->view->url(array(''), 'carfilter') );
        }
    }

    /**
     * Список объявлений
     * @return void
     */
    public function filterAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
        $this->_helper->initInterfaceUI();
        
        $from = (int) $this->_getParam("from");
        if ($from > 0) {
            $this->view->from = $from + 1;
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setRender ('morecars');
        }    

        $this->_helper->handleFilter();
        
    }
    
    public function devsAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
        $this->_helper->initInterfaceUI();
        
        $this->_helper->handleFilter();
        
        $table = new Auto_Model_Table_CarMarks();
        $this->view->brands = $table->getAll();
        
    }
    
    public function companiesAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->_helper->initInterface();
        $this->_helper->initInterfaceUI();
        
        $this->_helper->handleFilter();
        
        $tableSpheres = new Companies_Model_Table_Spheres();
        $this->view->spheres = $tableSpheres->getCatalog(1);
        
        $table = new Companies_Model_Table_Companies(); 
        $this->view->companies = $table->getListBySphere(array_keys($tableSpheres->getSubs(1)));
        
    }
    
    public function lentsAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->_helper->initInterface();
        $this->_helper->initInterfaceUI();
        
        $this->_helper->handleFilter();    
        
        $table = new Feed_Model_Table_Feed();
        $lents = $table->getAutoLents(); // + $company->getEventGalleries(); // + $company->getEvents(); 
        if (count($lents) > 0) $this->_helper->lent->sksort($lents, 'date');
        $this->view->lents = $lents;
        
    }

    /**
     * Минимальные и макс значения для слайдеров
     * @return void
     */
    public function rangesAction() {
        $this->_helper->viewRenderer->setNoRender();
        $series_ids = $this->getRequest()->getParam('series');
        //throw new Exception($series_ids);
        if (is_null($series_ids)) {
            $body_ids = array(); // $this->getRequest()->getParam('body');
            $mark_ids = (array) $this->getRequest()->getParam('mark');
            $table = new Auto_Model_Table_CarSeries();
            $items = $table->getListByBodyBrand($body_ids, $mark_ids);
            if (empty($items))
                return;
            
            $series_ids = array_keys($items);
        } else {
            //if (!is_array($series_ids)) return;
            $table = new Auto_Model_Table_CarAds();
            //$items = $table->getListByModel($series_ids);
            $items = $table->getListBySeries($series_ids);
        }
        
        $names = array('price', 'year', 'mileage', 'engine_volume');
        
        if (empty($items)) {
            // обнуляем
            foreach ($names as $name) {
                $data[$name][0] = 0;
                $data[$name][1] = 0;
            }            
        } else {
            // Ищем минимальные и максимальные значения параметров для всех объявлений
            $first = array_pop($items);            
            foreach ($names as $name) {
                $data[$name] = array((int) $first[$name], (int) $first[$name]);
                foreach ($items as $item) {
                    if ($item[$name] < $data[$name][0])
                        $data[$name][0] = (int) $item[$name]; // min
                    if ($item[$name] > $data[$name][1])
                        $data[$name][1] = (int) $item[$name]; // max
                }
                // макс. значения ставим свои из-за гемора с ползунками
                //$data[$name][1] = (int)Auto_Model_CarAd::$limited_value_filter[$name];
                //$data[$name][2] = (int)Auto_Model_CarAd::$default_value_filter[$name];
            }
        }    
        
        $this->getResponse()->setBody(
                $this->view->json($data)
        );
    }

    /**
     * Количество объявлений
     * @return void
     */
    public function countAction() {
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        //if ($request->isGet() && ($request->getParam('body') || $request->getParam('brand'))) {
        if ($request->isGet() && ($request->getParam('mark'))) {
            $data = $request->getQuery();
            $form = new Auto_Form_CarSearch();
            if ($form->isValid($data)) {
                if (!isset($data['body']))
                    $data['body'] = array();
                if (!isset($data['mark']))
                    $data['mark'] = array();
                if (empty($data['serie'])) {
//                    // если список моделей не задан - ищем по всем моделям кузовов и марок
//                    $table = new Auto_Model_Table_CarSeries();
//                    $items = $table->getListByBodyBrand($data['body'], $data['brand']);
//                    if (empty($items))
//                        return;
//                    $data['model'] = array_keys($items);
                    $data['serie'] = array();
                }
                $table = new Auto_Model_Table_CarAds();
                $items = $table->filter($data);
                $this->getResponse()->setBody(
                        $this->view->json(array('count' => count($items)))
                );
            }
        }
    }

    /**
     * Добавление нового объявления
     * @return void
     */
    public function newAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
        
        $this->view->headScript()
                ->appendFile('/resources/uploadify/jquery.uploadify.js')
                ->appendFile('/js/photoUploader.js')
                //->appendFile('/js/auto/newitem.js')
                ->appendFile('/js/charCount.js');

        $this->view->headLink()
                ->appendStylesheet('/resources/uploadify/uploadify.css');

        $form = $this->view->addform = new Auto_Form_NewCar();
        $form->setTemplate('/forms/new_car');
        /* $bodytable = new Auto_Model_Table_CarBody();
          $form->body->setMultiOptions( $bodytable->getAll() ); */
        
        if ($this->view->getLogged()) {
            $user = $this->view->getUserByIdentity();  
            $form->removeElement('figli');
        } else {
            $user = NULL;
        }

        $request = $this->getRequest();
        if ($request->isPost()) {    
            $data = $request->getPost();
            if ($form->isValid($data)) {
                
                $table = new Auto_Model_Table_CarAds();
                
                $data = $form->getValues(); 
                
                // проверка на одинаковые контактные данные
                // чтоб объявы не плодились
                // зареганные пусть плодят, если че потом ограничим
                if (!$this->view->getLogged()) {
                    $contacts = array();
                    if (!empty($data['phone'])) $contacts['phone'] = $data['phone'];
                    if (!empty($data['email'])) $contacts['email'] = $data['email'];
                    if (!empty($data['skype'])) $contacts['skype'] = $data['skype'];                
                    $similar_cars = $table->searchSpammersObjects($contacts);

                    if (count($similar_cars) > 0) {
                        $this->view->errors = 'Объявление с такими контактными данными уже существует';
                        $form->setDefaults($data);
                        return;
                    } 
                }    
                
                $files = json_decode($data['photos']); // фото
                if (count($files) > 0 ) {
                    $min_photo = false;
                    foreach ($files as $key => $file) {
                        $path = Zend_Registry::get('upload_path');
                        // Первую фотку сделаем маленькую, для листинга
                        if (!is_null($file)) {
                            if (!$min_photo) {
                                if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/cars/thumbs/min_' . $file, 144, 191, true)) {
                                    throw new Exception('Ошибка при уменьшении изображения');
                                }
                                $min_photo = true;
                                $data['min_photo'] = "min_$file";
                            }

                            if (is_file($path . '/tmp/' . $file)) {
                                rename($path . '/tmp/' . $file, $path . '/cars/' . $file);
                            }
                        } else {
                            unset($files[$key]);
                        }
                    }
                    $data['photos'] = json_encode(array_slice($files, 0, Auto_Model_CarAd::$max_photos));
                }

                if (!is_null($user)) $data['id_user'] = $user->id;
                
                $car = $table->createRow($data);
                $id = $car->save();
                //$this->logger->log('Добавлено объявление: ' . $this->view->url(array('carId' => $id), 'car'), Zend_Log::NOTICE, $this->getRequest());
                $this->_helper->FlashMessenger(array('system_ok' => 'Ad publicado'));
                $this->_redirect($car->getLink($car->getModel()));
                
            } else {
                $form->setDefaults($data);
            }
            
        } else {
            // заполним значение контактов, если пользователь под своим именем
            if (!is_null($user)) {
                $form->getElement('email')->setValue($user->email);
                $form->getElement('skype')->setValue($user->skype);
            }
        }
    }
    
    public function editAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->_helper->initInterface();
        $this->view->headScript()
                ->appendFile('/resources/uploadify/jquery.uploadify.js')
                ->appendFile('/js/photoUploader.js')
                ->appendFile('/js/auto/newitem.js')
                ->appendFile('/js/charCount.js');

        $this->view->headLink()
                ->appendStylesheet('/resources/uploadify/uploadify.css');

        $request = $this->getRequest();        

        $table = new Auto_Model_Table_CarAds();        
        
        $id = (int) $request->getParam('carId');
        $cars = $table->find($id);
        if (count($cars) > 0) {
            $car = $cars->current();
        } else {
            //throw new Exception('Неизвестный идентификатор объявления');           
            $this->_helper->FlashMessenger( 'Объявление не найдено' );
            $this->_redirect( $this->view->url(array(''), 'carfilter') );
        }
        
        $form = $this->view->addform = new Auto_Form_NewCar();
        $form->setTemplate('/forms/edit_car');
        
        if ($this->view->GetLogged()) $form->removeElement('figli');
        
        $modelManager = new Auto_Model_Table_CarSeries();
        $brandIds[] = $car->getModel()->getBrand()->id;
        $items = $modelManager->getListByBodyBrand( array(), $brandIds, false );
        $form->getElement('id_model')->setMultiOptions( $items );
        $form->getElement('id_model')->setValue($car->id_model);
        
        $author = $car->getAuthor();
        $identity = $this->view->getUserByIdentity();        
        
        if (!is_null($author) || $this->view->isGodOfProject()) {                            
            if ((!is_null($identity) && !is_null($author) && $author->id == $identity->id) || $this->view->isGodOfProject()) {
        
                if ($request->isPost()) {
                    if ($form->isValid( $request->getPost() )) {

                        $data = $form->getValues();

                        if ($data['photo'] && $data['photo'] != $car->photo) {                         
                            
                            $files = json_decode( $data['photo'], true ); // фото
                            $photos = $car->getPhotos();
                            if (!is_null($photos)) 
                                $delete_photos = array_diff($photos, $files); // найдем разницу
                            else
                                $delete_photos = array();                            
                            
                            $path = Zend_Registry::get('upload_path');
                            
                            if (count($files) > 0 && count($files) <= Auto_Model_CarAd::$max_photos) {
                                $min_photo = false;
                                foreach ($files as $key => $file) {
                                    // Первую фотку сделаем маленькую, для листинга
                                    if (!is_null($file)) {
                                        if (!$min_photo) {
                                            if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/cars/thumbs/min_' . $file, 144, 191, true)) {
                                                throw new Exception('Ошибка при уменьшении изображения');
                                            }
                                            $min_photo = true;
                                            $data['min_photo'] = "min_$file";
                                        }

                                        if (is_file($path . '/tmp/' . $file)) {
                                            rename($path . '/tmp/' . $file, $path . '/cars/' . $file);
                                        } else {
                                            unset($files[$key]);
                                        }
                                    } else {
                                        unset($files[$key]);
                                    }
                                }
                                //$data['photo'] = json_encode($files);
                            }
                            
                            if (!is_null($photos)) 
                                $data['photo'] = json_encode( array_slice(array_values($files + array_diff($photos, $delete_photos)), 0, Auto_Model_CarAd::$max_photos) ); // новые фотки + актуальные старые
                            else
                                $data['photo'] = json_encode( array_slice(array_values($files), 0, Auto_Model_CarAd::$max_photos) ); // новые фотки
                            
                            // раз все загрузили, удалим старье
                            //$photos = $post->getPhotos();                        
                            foreach ($delete_photos as $photo) {                            
                                if ( is_file($path.'/cars/'.$photo)) unlink($path.'/cars/'.$photo);            
                            }
                            
                        } else {
                            unset($data['photo']);
                        }
                        

                        $car->setFromArray($data);
                        $carId = $car->save();
                        
                        // очистим память
                        unset($data);
                        unset($delete_photos);
                        unset($photos);
                        unset($files);
                        
                        $this->logger->log('Добавлено объявление: ' . $this->view->url(array('carId' => $carId), 'car'), Zend_Log::NOTICE, $this->getRequest());
                        $this->_helper->FlashMessenger('Объявление отредактировано');
                        $this->_redirect($this->view->url(array('carId' => $carId, 'action' => 'view'), 'car'));
                        return;
                        
                    } else {
                        $form->setDefaults($data);
                    }
                    
                } else {
                    $form->setDefaults( $car->toArray() );
                }
                
            } else {
                $this->_redirect($this->view->url(array('carId' => $id, 'action' => 'view'), 'car'));         
            }
            
        } else {
            $this->_redirect($this->view->url(array('carId' => $id, 'action' => 'view'), 'car')); 
        }
                
    }    

    /**
     * Удаление
     * @return void
     */
    public function deleteAction() {
        $table = new Auto_Model_Table_CarAds();
        $carId = (int) $this->_getParam('carId');
        
        $cars = $table->find($carId);
        if (count($cars) > 0) {
            $car = $cars->current();
            $user = Zend_Auth::getInstance()->getIdentity();
            if ($user->id == $car->id_user || $this->view->isGodOfProject()) {                
                $photos = $car->getPhotos();
                $path = Zend_Registry::get('upload_path');
                foreach ($photos as $photo) {
                    if ( is_file($path.'/cars/'.$photo)) unlink($path.'/cars/'.$photo);                
                }  
                if (!is_null($car->min_photo)) {
                    if ( is_file($path.'/cars/thumbs/'.$car->min_photo)) unlink($path.'/cars/thumbs/'.$car->min_photo);
                }    
                $car->delete();                
                
                $this->_helper->FlashMessenger('Объявление было удалено');
                $this->_redirect( $this->view->url(array(''), 'auto') );
                return;
            }
        }
        $this->_redirect( $this->view->url(array('carId' => $carId, 'action' => 'view'), 'car') );        
    }

    /**
     * Загрузка изображений
     * @return void
     */
    public function uploadAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $name = $this->_helper->uploader(Zend_Registry::get('upload_path') . "/tmp");
        $path = Zend_Registry::get('upload_path') . "/tmp/$name";
        if (!is_file($path)) {
            throw new Exception('Файл не загружен');
        }

        $size = getimagesize($path);
        if ($size[0] >= $size[1]) {
            if ($size[0] < 900)
                return false;
            if (!$this->_helper->uploader->resize($path, 900, true, false, 51)) {
                throw new Exception('Ошибка при уменьшении изображения');
            }
        } else {
            if ($size[1] < 900)
                return false;
            if (!$this->_helper->uploader->resize($path, 900, false, false, 51)) {
                throw new Exception('Ошибка при уменьшении изображения');
            }
        }

        $this->getResponse()->setBody($name);
    }
    
    protected function getRemoteFileSize($url) { 
       $parsed = parse_url($url); 
       $host = $parsed["host"]; 
       $fp = @fsockopen($host, 80, $errno, $errstr, 20); 
       if(!$fp)return false; 
       else { 
           @fputs($fp, "HEAD $url HTTP/1.1\r\n"); 
           @fputs($fp, "HOST: $host\r\n"); 
           @fputs($fp, "Connection: close\r\n\r\n"); 
           $headers = ""; 
           while(!@feof($fp))$headers .= @fgets ($fp, 128); 
       } 
       @fclose ($fp); 
       $return = false; 
       $arr_headers = explode("\n", $headers); 
       foreach($arr_headers as $header) { 
           $s = "Content-Length: "; 
           if(substr(strtolower ($header), 0, strlen($s)) == strtolower($s)) { 
               $return = trim(substr($header, strlen($s))); 
               break; 
           } 
       } 
//       if($return){ 
//                  $size = round($return / 1024, 2); 
//                  $sz = "KB"; // Size In KB 
//            if ($size > 1024) { 
//                $size = round($size / 1024, 2); 
//                $sz = "MB"; // Size in MB 
//            } 
//            $return = "$size $sz"; 
//       } 
       return $return; 
    }     
    
    //------------------------------------------------------------
    // Функция парсера CSV-файла
    //------------------------------------------------------------
    // На входе: $file_name - имя файла для парсинга
    //           $separator - разделитель полей, по умолчанию ';'
    //           $quote - ограничитель строк, по умолчанию '"'
    // На выходе: массив значений всего файла
    //------------------------------------------------------------    
    protected function parse_csv($file_name, $separator=';', $quote='"') {
        // Загружаем файл в память целиком
        if (!$f = fopen($file_name, 'r')) {
            throw new Exception('Не могу открыть файл');
        }
        $str = fread($f, $this->getRemoteFileSize($file_name));
        //fwrite($f, '');
        fclose($f);

        // Убираем символ возврата каретки
        $str = trim(str_replace("\r",'',$str))."\n";

        $parsed = Array();    // Массив всех строк
        $i = 0;               // Текущая позиция в файле
        $quote_flag = false;  // Флаг кавычки
        $line = Array();      // Массив данных одной строки
        $varr = '';           // Текущее значение

        while($i<=strlen($str)) {
            // Окончание значения поля
            if ($str[$i]==$separator && !$quote_flag) {
                $varr=str_replace("\n","\r\n",$varr);
                $line[]=$varr;
                $varr='';
            }
            // Окончание строки
            elseif ($str[$i]=="\n" && !$quote_flag) {
                $varr=str_replace("\n","\r\n",$varr);
                $line[]=$varr;
                $varr='';
                $parsed[]=$line;
                $line=Array();
            }
            // Начало строки с кавычкой
            elseif ($str[$i]==$quote && !$quote_flag) {
                $quote_flag=true;
            }
            // Кавычка в строке с кавычкой
            elseif ($str[$i]==$quote && $str[($i+1)]==$quote && $quote_flag) {
                $varr.=$str[$i];
                $i++;
            }
            // Конец строки с кавычкой
            elseif ($str[$i]==$quote && $str[($i+1)]!=$quote && $quote_flag) {
                $quote_flag=false;
            }
            else {
                $varr.=$str[$i];
            }
            $i++;
        }
        return $parsed;
    }

    public function syncAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
//        $db = new PDO('mysql:dbname=concept3ru; host=77.222.61.159', 'concept3ru', 'monaco'); 
//        $statement = $db->prepare('SELECT * FROM cars where sync = 0');
//        $statement->execute();
//        $new_cars = $statement->fetchAll();
//        
//        foreach ($new_cars as $car) {
//            print $car->firm. ' ' . $car->model . ' ' . $car->year;
//        }

//        СИНХРОНИЗАЦИЯ через csv файл
//        на первое время норм, потом переделать на xml-сервис
//      
        if ($this->view->isGodOfProject()) {
        
            set_time_limit(0); // чтоб не было тупежа

            // concept
            $cars = $this->parse_csv("http://conceptsakh.ru/hiroshima/sync_qlick.txt");        
            //$cars = $this->parse_csv("ftp://Mirage:d1pfbvjltqcndb2t@217.148.193.17/html/hiroshima/sync_qlick.txt");        
            $path = Zend_Registry::get('upload_path');        

            if (count($cars) > 0) {
                $table = new Auto_Model_Table_CarAds();
                $model = new Auto_Model_Table_CarSeries();

                // только через транзацию
                //$db = $table->getAdapter()->beginTransaction();                
                
                $i = 0;
                $errors = 0;
                foreach ($cars as $car) {
                    // смотрим есть ли модель и нет ли уже в базе тачки с id_sync
                    //throw new Exception($car[3]);
                    if (!is_null($id_model = $model->getModelByName($car[3]))) {
                        //throw new Exception($id_model);
                        
                        if ($car[0] == 'add' || $car[0] == 'upd') {
                            $data['is_new']         = (int) $car[1];
                            $data['id_sync']        = (int) $car[2];
                            $data['id_model']       = (int) $id_model;
                            $data['year']           = (int) $car[4];
                            $data['mileage']        = (int) $car[5];
                            $data['price']          = (int) $car[6];
                            $data['photo']          = $car[7];
                            $data['engine_type']    = (int) $car[8];
                            $data['engine_volume']  = (int) $car[9];
                            $data['gearbox']        = (int) $car[10];
                            $data['trasmission']    = (int) $car[11];
                            $data['description']    = $car[12];

                            $data['id_user']        = 36;  
                            $data['phone']          = '+7 (4242) 42-33-12';
                        } else {
                            $data['id_sync']        = (int) $car[1];    
                        }    

                        switch ($car[0]) {
                            case 'add' : { 

                                if (is_null($table->getCarByIdSync($car[2]))) {

                                    $path_photo = 'http://www.conceptsakh.ru/data/' . $data['photo'];
                                    $local_photo = $path . '/tmp/' . $data['photo'];

                                    if (@fopen($path_photo, 'r')) {
                                        if(@copy($path_photo, $local_photo)) {
                                            // грузим фотки
                                            $ext = strtolower(substr(strrchr($data['photo'], '.'), 1));
                                            $file = time() . md5($data['photo']) . '.' . $ext;
                                            if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/cars/thumbs/min_' . $file, 144, 191, true)) {
                                                throw new Exception('Ошибка при уменьшении изображения');
                                            }
                                            $data['min_photo'] = 'min_' . $file;

                                            $size = getimagesize($local_photo);
    //                                        if ($size[0] >= 612) {
    //                                            if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/cars/' . $file, 459, 612)) {
    //                                                throw new Exception('Ошибка при уменьшении изображения');
    //                                            }
    //                                            $data['photo'] = '[' . json_encode($file) . ']';
    //                                        } else {
    //                                            unset($data['photo']);
    //                                        } 
                                            if ($size[0] >= $size[1]) {
                                                if ($size[0] >= 900) {
                                                    if (!$this->_helper->uploader->resizeToPath($local_photo, $path . '/cars/' . $file, 900)) {
                                                        throw new Exception('Ошибка при уменьшении изображения');
                                                    }                                                    
                                                } else {
                                                    copy($local_photo, $path . '/cars/' . $file); 
                                                }   
                                                $data['photo'] = '[' . json_encode($file) . ']';
                                            } else {
                                                unset($data['photo']);    
                                            }    
                                        }

                                        @unlink($local_photo);

                                    } else {
                                        unset($data['min_photo']);
                                        unset($data['photo']);
                                    }      
                                } else {
                                    unset($data['min_photo']);
                                    unset($data['photo']);
                                }        

                                $car = $table->createRow($data);
                                $id = $car->save();                             
                                $i++;
                                $this->logger->log('auto. Добавлено объявление от Концепта: ' . $this->view->url(array('carId' => $id), 'car'), Zend_Log::NOTICE, $this->getRequest());
                            } break;
                            // изменение данных
                            case 'upd' : {

                                $car = $table->getCarByIdSync($data['id_sync']);
                                if (!is_null($car)) {
                                    unset($data['photo']); // если тачка уже есть в базе, то фотку не грузим
                                    $car->setFromArray($data);
                                    $id = $car->save();
                                    $this->logger->log('auto. Изменено объявление от Концепта: ' . $this->view->url(array('carId' => $id), 'car'), Zend_Log::NOTICE, $this->getRequest());
                                } else {
                                    // если не нашли, добавим тогда хули делать
                                    if (is_null($table->getCarByIdSync($car[2]))) {

                                        $path_photo = 'http://www.conceptsakh.ru/data/' . $data['photo'];
                                        $local_photo = $path . '/tmp/' . $data['photo'];

                                        if (fopen($path_photo, 'r')) {
                                            if(copy($path_photo, $local_photo)) {
                                                // грузим фотки
                                                $ext = strtolower(substr(strrchr($data['photo'], '.'), 1));
                                                $file = time() . md5($data['photo']) . '.' . $ext;
                                                if (!$this->_helper->uploader->resizeWithCropToPath($local_photo, $path . '/cars/thumbs/min_' . $file, 144, 191, true)) {
                                                    throw new Exception('Ошибка при уменьшении изображения');
                                                }
                                                $data['min_photo'] = 'min_' . $file;

                                                $size = getimagesize($local_photo);
                                                if ($size[0] >= $size[1]) {
                                                    if ($size[0] >= 900) {
                                                        if (!$this->_helper->uploader->resizeToPath($local_photo, $path . '/cars/' . $file, 900)) {
                                                            throw new Exception('Ошибка при уменьшении изображения');
                                                        }                                                        
                                                    } else {
                                                        copy($local_photo, $path . '/cars/' . $file);    
                                                    }    
                                                    $data['photo'] = '[' . json_encode($file) . ']';
                                                } else {
                                                    unset($data['photo']);    
                                                }    
                                            } else {
                                                print 'не найдена фотка ' . $path_photo;
                                            }

                                            @unlink($local_photo);

                                        } else {
                                            unset($data['min_photo']);
                                            unset($data['photo']);
                                        }      
                                    } else {
                                        unset($data['min_photo']);
                                        unset($data['photo']);
                                    }        

                                    $car = $table->createRow($data);
                                    $id = $car->save();                             
                                    $i++;
                                    $this->logger->log('auto. Добавлено объявление от Концепта: ' . $this->view->url(array('carId' => $id), 'car'), Zend_Log::NOTICE, $this->getRequest());

                                }   

                            } break;
                            case 'del' : {
                                $car = $table->getCarByIdSync($data['id_sync']);
                                if (!is_null($car)) {
                                    $photos = $car->getPhotos();
                                    if (!is_null($photos)) {
                                        $path = Zend_Registry::get('upload_path');
                                        foreach ($photos as $photo) {
                                            if ( is_file($path.'/cars/'.$photo)) unlink($path.'/cars/'.$photo);                
                                        }  
                                    }    
                                    if (!is_null($car->min_photo)) {
                                        if ( is_file($path.'/cars/thumbs/'.$car->min_photo)) unlink($path.'/cars/thumbs/'.$car->min_photo);
                                    } 
                                    $id = $car->id;
                                    $car->delete();
                                    $this->logger->log('auto. Удалено объявление от Концепта: ' . $this->view->url(array('carId' => $id), 'car'), Zend_Log::NOTICE, $this->getRequest());
                                }
                            } break;    
                        }

                        unset($data);

                    } else {
                        $errors++;
                        $this->logger->log('auto. Не найдена модель от Концепта id' . $car[2], Zend_Log::WARN, $this->getRequest());    
                    }                    
                }

                if ($errors == 0) {
                    //$clean = @fopen('http://www.conceptsakh.ru/hiroshima/sync_clean.php', 'r');
                    //fclose($clean);  
                    $this->_helper->FlashMessenger('Импортировано автомобилей: ' . $i. ' из ' . count($cars));
                } else {
                    //$table->getAdapter()->rollback();
                    $this->_helper->FlashMessenger('Возникли ошибки при иммпорте, проверьте логи');
                }

                //print 'Импортировано: ' . $i. ' из ' . count($cars);
                
                $this->_redirect($this->view->url(array(), 'auto'));

            }



        }
        
    }    

    
}