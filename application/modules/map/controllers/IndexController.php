<?php

class Map_IndexController extends Zend_Controller_Action
{

    protected $userAdmin = 1;
    
    public function init()
    {
//        $this->getResponse()
//                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->_helper->initInterface();

        $this->_helper->layout->setLayout('map');
    }
    
    public function indexAction()
    {
        // action body        
//        $this->getResponse()
//                ->setHeader('Content-Type', 'text/html; charset=utf-8' );

        $this->_helper->initInterface('1.8.3');

        $this->view->headScript()
            ->appendFile( '/js/map/view.js' )
            ->appendFile( '/js/map/markerclusterer_packed.js' );

        if ($this->view->isGodOfProject())
            $this->view->headScript()
                ->appendFile( '/js/map/marker.js' );
        
        //$section = $this->_getParam('section', null); // id категории
        $section = (int) $this->_getParam('section', null); // id секции
        $categories = (array) $this->_getParam('category', null); // ids категорий
        
        if (!is_null($section))
            $this->view->topSection = array($section);
        
        if (!is_null($categories)) {
            $this->view->section = $categories;        
        }
        
        if ($section == 0 && count($categories) == 0)
            $this->view->topSection = 0;//array('2');
                
        $mapManager = new Application_Model_Table_Map();
        
        $mapCategoryManager = new Application_Model_Table_MapCategories();
        //$mapParentCategories = $mapCategoryManager->getParentCategories();
        $mapSubCategories = Companies_Model_Company::$header_types; //$mapCategoryManager->getSubCategories();
        $markers = $mapManager->fetchAll();
        $last24HourMarkers = array();
        foreach($markers as $marker)
        {
            if(time() - strtotime($marker->date) <= 60*60*24) $last24HourMarkers[$marker->id] = $marker->id;
        }
        $this->view->last24HourMarkers = $last24HourMarkers;
        
        $location = $this->_getParam("location");
        $this->view->location = $location ? $location : "(-33.468752, -70.641986)";
        
        if (isset($location))
            $this->view->map_zoom = 18;
        else
            $this->view->map_zoom = 14;
        
        //$this->view->mapCategories = $mapParentCategories;
        $this->view->mapSubCategories = $mapSubCategories;
    }

    public function xmlAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        // XML-related routine
        $dom = new DOMDocument('1.0', 'utf-8');

        $node = $dom->createElement("markers");
        $parnode = $dom->appendChild($node);

        $raw_category = $this->getRequest()->getParam( 'catIds' );
        
        if (is_array($raw_category)) {
            
            $category = array();
            foreach ($raw_category as $key => $item) {
                $category[] = $item['value']; 
            }

            $mapManager = new Application_Model_Table_Map();
            $items = $mapManager->getListByCategory( $category );
            if ( empty($items) ) return;

            foreach ($items as $item) {
                $node = $dom->createElement("marker");
                //$rating = $item->getRating();
                $newnode = $parnode->appendChild($node);
                $newnode->setAttribute("id",            $item['id']);
                $newnode->setAttribute("catId",         $item['catId']);
                //$newnode->setAttribute("title",         $item['title']);
                $newnode->setAttribute("lat",           $item['lat']);
                $newnode->setAttribute("lng",           $item['lng']);
                //$newnode->setAttribute("description",   $item['description']);
                //$newnode->setAttribute("address",       $item['address']);
                $newnode->setAttribute("icon",          "/sprites/map/large/" . $item['catId'] . ".png");
                //$newnode->setAttribute("rating",        $rating);
                $newnode->setAttribute("date",          $item['date']);
            }

            $output = $dom->saveXML();

            // Setting up headers and body
            $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                    ->setBody($output);
            
        }

    }
    
    public function searchAction()
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $request = $this->getRequest();
        $search_str = $request->getParam('name');        
        
        //$validator = new Zend_Validate_Alnum();
        $filter = new Zend_Filter_StripTags(); 
        $search_str = $filter->filter($search_str);
        //if ($validator->isValid($search_str)) {
            
            // value contains only allowed chars
            $table = new Application_Model_Table_Map();
            $rows = $table->GetObjectByName($search_str);
            if (count($rows) > 0) {
                $data = array();
                foreach ($rows as $row) {
                    //$coordinates = explode(",", str_replace("(", "", str_replace(")","",$row->coordinates)));
                    $data['id'] = $row->id;
                    $data['lat'] = trim($row->lat);
                    $data['lng'] = trim($row->lng);
                }
                $this->getResponse()->setBody($this->view->json($data));
            } else {
                return false;
            }
        //}

        return false;
    }

    public function getaddmarkerformAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

//        $mapCategoryManager = new Application_Model_Table_MapCategories();
//        //$mapCategories = $mapCategoryManager->getParentCategories();
//        $mapSubCategories = $mapCategoryManager->getSubCategories(1);
//
//        $iconMarker = array();
//        $dir = Zend_Registry::get('upload_path') . "/../sprites/map/large/";
//        if (is_dir($dir)) {
//            if ($dh = opendir($dir)) {
//                while (($file = readdir($dh)) !== false) {
//                    if($file!="." && $file!=".." && $file!=".svn")
//                        $iconMarker[] = "$file";///files/map/
//                }
//                closedir($dh);
//            }
//        }
//        sort($iconMarker);

        $this->getResponse()->setBody($this->view->mapAddMarkerForm()); //, $iconMarker));
    }


    public function addmarkerAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $request = $this->getRequest();
        
        $form = new Map_Form_EditMarker();

        //$params = $this->_getAllParams();
        
        if ($request->isPost() && $this->view->isGodOfProject()) {
        
            $data = $request->getPost();
            if ($form->isValid($data)) {
                
                $data = $form->getValues();

                $mapManager = new Application_Model_Table_Map();
                
                $row = $mapManager->createRow($data);
                $id = $row->save();
                
                $company = $row->getCompany();

                $icon = "/sprites/map/large/" . $company->type . ".png";
                //$result = array("markerId"=>$id, 'category_id'=>$data['category_id'], "company_id"=>$data['company_id'], "icon" => $icon, "lat" => $data['lat'], "lng" => $data['lng']);
                $result = array("markerId"=>$id, "company_id"=>$data['company_id'], "icon" => $icon, "lat" => $data['lat'], "lng" => $data['lng']);

                $this->getResponse()->setBody( json_encode($result) );
                
            } else {
                throw new Exception('invalid data');
            }
            
        } else {
            throw new Exception('no data');
        }
       
    }
    
    public function editmarkerAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $request = $this->getRequest();
        
        $form = new Map_Form_EditMarker();

        $mapManager = new Application_Model_Table_Map();
      
        $markerId = (int) $this->getRequest()->getParam ( 'markerId' );

        $marker = $mapManager->find($markerId);
        if (count($marker) > 0) {
            $row = $marker->current();
        } else {
            throw new Exception('no marker');
        }
        
        if ($request->isPost() && $this->view->isGodOfProject()) {
        
            $data = $request->getPost();
            if ($form->isValid($data)) {
                
                $data = $form->getValues();
                $data['lat'] = $row->lat;
                $data['lng'] = $row->lng;

                $row->setFromArray($data);
                $row->save();

                $icon = "/sprites/map/large/" . $data['icon'];
                $result = array("markerId" => $row->id, 'category_id' => $data['category_id'], "icon" => $icon, "lat" => $data['lat'], "lng" => $data['lng']);

                $this->getResponse()->setBody( json_encode($result) );
                
            } else {
                throw new Exception('invalid data');
            }
            
        } else {
            throw new Exception('no data');
        }        
//        
//        
//        $row->setFromArray($params);
//        $row->save();
//        $params['lat'] = $row->lat;
//        $params['lng'] = $row->lng;
//        $id = $params['markerId'];
//        $params['date'] = $row->date;
//        $params['company_id'] = $row->company_id;
//
//        $icon = "/sprites/map/large/".$params['icon'];
//        $result = array('result'=>1, "markerId"=>$id, 'category_id'=>$params['category_id'], "company_id"=>$params['company_id'], "icon" => $icon, "lat" => $params['lat'], "lng" => $params['lng']);
//
//        $this->getResponse()->setBody( json_encode($result) );
//        //$this->view->result= $result;        
//        //$this->renderScript('ajax.phtml');         
    }    

    public function movemarkerAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $request = $this->getRequest();
        
        $markerId = (int) $this->_getParam('markerId');
        $lat = (real) $this->_getParam('lat');
        $lng = (real) $this->_getParam('lng');
        
        if ($request->isPost() && $this->view->isGodOfProject()) {

            $mapManager = new Application_Model_Table_Map();
            $user = Zend_Auth::getInstance()->getIdentity();

            $marker = $mapManager->find($markerId);
            if (!$marker) {
                throw new Exception('Desconocido identificador');
            } else {
                $row = $marker->current();
                $row->lat = $lat;
                $row->lng = $lng;
                $row->save();            
            }
            
        } else {
            throw new Exception('no data');    
        }
    }
    
    public function deletemarkerAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $markerId = (Int) $this->_getParam('id');
        
        $table = new Application_Model_Table_Map();        
        $rows = $table->find($markerId);
        if (count($rows) > 0) {
            $marker = $rows->current();
            
            //$author = $marker->getAuthor();
            if ($this->view->isGodOfProject()) {
                //$identity = $this->view->getUserByIdentity();                
                //if ((!is_null($identity) && $author->id == $identity->id) || $this->view->isGodOfProject()) {
                    $marker->delete();  
                    $this->getResponse()->setBody(1);
                //}
            }
        }
        
//        $mapManager->delete($mapManager->getAdapter()->quoteInto('id = ?', $markerId));
//        
//        $result = array('result'=>1);
//        $this->view->result= $result;     
    }
    
    public function getsubcategoryAction()
    {
        $this->_helper->layout()->disableLayout();
        $categoryId = (Int) $this->_getParam("categoryId");
        $mapSubcategoriesManager = new Application_Model_Table_MapCategories();
        //$subCategoryParams = array("categoryId"=>$categoryId);
//        if(isset($this->userAdmin) && $this->userAdmin == 2)
//        {
//            if($categoryId == 4)
//                $subCategoryParams["id"] = 24;
//        }
        $subCategoryList = $mapSubcategoriesManager->getSubCategories($categoryId);
        
        
        $icon = array();
        foreach ($subCategoryList as $categories) {
            $icon[$categories['id']] = $categories['icon'];
        }
        $this->view->icon = $icon;
        $this->view->categoryId = $categoryId;
        $this->view->subCategoryList = $subCategoryList;
        //$this->view->subCategoryId = $this->_getParam("subCategoryId");
        //$this->view->address = $this->_getParam("address");        
    }
    
    public function savemapAction()
    {
//        print_r($this->_getAllParams());die();
        $mapManager = new Application_Model_Table_Map();
        $markerKoord = $this->_getParam("marker_koord");
        $markerDelete = $this->_getParam("marker_delete");
        $markerTitle = $this->_getParam("marker_title");
        $markerEdit = $this->_getParam("marker_edit");
        
        if($markerKoord){
            array_unique($markerKoord);
            array_unique($markerTitle);
        foreach ($markerKoord as $key=>$koord) {
            $option = array("category_id"=>1, "coordinates"=>$koord, "title"=>$markerTitle[$key]);
            $row = $mapManager->createRow($option);
            $row->save();
        }
        }

        if($markerDelete){
            array_unique($markerDelete);
            foreach ($markerDelete as $key=>$markerId) {
                $marker = $mapManager->find($markerId);
                $marker->current()->delete();
            }    
        }
        
        if($markerEdit)
        {
            array_unique($markerEdit);
            foreach ($markerEdit as $key=>$marker) {
                $markerEx = explode("_", $marker);
                $row = $mapManager->find($markerEx[1]);
                $rowCurr = $row->current();
                $rowCurr->setFromArray(array("title"=>$markerEx[0]));
                $rowCurr->save();
            }   
        }
        $this->_redirect( $this->vuew->url(array(), 'map') ); // getHelper('redirector')->gotoSimpleAndExit('mapview', 'map');
        
    }
    
    public function geteditmarkerformAction()
    {
        $this->_helper->layout()->disableLayout();
        $markerId = (int) $this->_getParam("markerId");
        
        $markerManager = new Application_Model_Table_Map();
        $markers = $markerManager->find($markerId);
        if (count($markers) > 0) {
            $marker = $markers->current();
        } else {
            throw new Exception("Desconocido identificador");
        }
        
        $mapCategoryManager = new Application_Model_Table_MapCategories();
        $subCategoryList = $mapCategoryManager->getSubCategories();
        
        $iconMarker = array();
        $dir = Zend_Registry::get('upload_path') . "/../sprites/map/large/";
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if($file!="." && $file!=".." && $file!=".svn")
                        $iconMarker[] = "$file";///files/map/
                }
                closedir($dh);
            }
        }
        
        $this->view->iconMarker = $iconMarker;
        $this->view->subCategoryList = $subCategoryList;        

        $this->view->marker = $marker;
    }
    
    public function getmarkerinfoAction()
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $markerId = (int) $this->_getParam("markerId");
        $markerManager = new Application_Model_Table_Map();
        $companyManager = new Companies_Model_Table_Companies();
        $markerRow = $markerManager->find($markerId);
        if (count($markerRow) > 0) {
            
            $marker = $markerRow->current();
        
            if (!is_null($marker->company_id) && $marker->company_id != 0) {
                $company = $marker->getCompany();
            } else {
                $company = NULL;
            }

            //$companyManager = new Companies_Model_Table_Likes();
            if(!is_null($company)) {
                $photo = $company->getBage();

                if (!is_null($photo)) {
                    $result = '<div class="Qballon"><img class="QperfI mb20" src="/files/company/400_' . $photo . '" width="400" alt="" />';
                } else {
                    $result = '<div class="Qballon">';    
                }
                //if (!is_null($bage))
                //    $result = '<a href="' . $this->view->url(array('companyId' => $company->id), 'company') . '"><img src="/files/company/avatars/' . $bage . '" width="289" height="155" style="margin-top: 12px;" alt="' . $company->name . '" /></a><br />';

                $result .= "<h2><a class='black' href='".$company->getUrl()."' >$company->name</a></h2>";
                $result .= "<p class='mb20'>" . $company->description . "</p>"; 

            } else {
                $result = "<h2>$marker->title</h2>";
                $result .= "<p class='mb20'>$marker->description</p>";
            }

            if(!is_null($company)) {
                $result .= "<p>Dirección: ".$company->address."</p>";

//                $menu = $company->getMenuCatalogRoot();
//                if (count($menu) > 0)
//                    $result .= "<p class='mp-d mar-bot2'><a href='" . $this->view->url(array('companyId' => $company->id), 'menu') . "'>Carta</a></p>"; 
//
//                $oramas = $company->getOramas();
//                if (count($oramas) > 0) :
//                    foreach ($oramas as $orama) :
//                        $result .= '<p><a href="' . $this->view->url(array('oramaId' => $orama->id), 'orama') . '">Orama</a> ' . count($oramas) . '</p>';
//                        break;
//                    endforeach;
//                endif;

            } elseif (!is_null($marker->address) && $marker->address != '') {
                $result .= "<p>Dirección: ".$marker->address."</p>";
            }

            $result .= "<p class='f9 grey mb20'>Fecha de publicación: ".$this->view->dateFormatPostList($marker->date)."</p><input type='hidden' id='markerId' value='$marker->id'>";
            if($this->view->isGodOfProject())
                $result .= "<input type='button' value='Удалить' onclick='deleteMarker($marker->id);'/>"; //<input type='button' value='Редактировать' onclick='editMarker($marker->id);'/>";
            $result .= "</div>";

            $this->getResponse()->setBody($result);
            
        }    
            
    }
    
    public function rateAction()
    {
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        
        $user = $this->view->getUserByIdentity();
        $value = (int) $this->_getParam('rating'); 
        $markerId = (int) $this->_getParam('marker_id');
        
        $mapManager = new Application_Model_Table_Map();
        
        if (!is_null($user)) {
            $ratingParams['user_id'] = $user->id;
            
            

            $ratingManager = new Application_Model_Table_Rating();
            // проверим голосовал ли уже этот тип
            $isset_rating = $ratingManager->getRatingWithParams(array('user_id' => $user->id, 'object_id' => $markerId, 'object_type' => 'map'));
            if ($isset_rating > 0) {
                throw new Exception('Вы уже голосовали');
            } else {
                
                $rowsMarkers = $mapManager->find($markerId);
                if (count($rowsMarkers) > 0) {
                    
                    $data = array();
                    $data['user_id'] = $user->id;
                    $data['object_id'] = (int) $this->_getParam('marker_id');
                    $data['object_type'] = 'map';

                    $rowsMarker = $rowsMarkers->current();
                    $rating = array();
                    if ($value > 0) {
                        $data['good'] = 1;
                        $rating['isgood'] = $rowsMarker->isgood + 1;
                    } else {
                        $data['poor'] = 1;
                        $rating['ispoor'] = $rowsMarker->ispoor + 1;
                    }    
                    
                    $row = $ratingManager->createRow($data);
                    $row->save();

                    $sumRating = $rowsMarker->isgood - $rowsMarker->ispoor + ($value == 1 ? 1 : -1);
                    $rowsMarker->setFromArray($rating);
                    $rowsMarker->save();

    //                $result = array('sumRating' =>$sumRating, 'object_id' => $data['object_id']);
    //                $this->view->result= $result;
    //                $this->getResponse()->setBody( json_encode($result) ); 
                } else {
                    throw new Exception('Маркер не найден');
                }    
            }    
   
        }        
        
//        $markerId = (int) $this->_getParam("markerId");
//        $ratingValue = (int) $this->_getParam("rating");
//        
//        $userAuth = Zend_Auth::getInstance()->getIdentity();
//        $ratingManager = new Application_Model_Table_Rating();
//        $mapManager = new Application_Model_Table_Map();
//        $marker = $mapManager->find($markerId);
//        $rating = $ratingManager->getRatingWithParams(array("user_id"=>$userAuth->id, "object_id"=>$markerId, "object_type"=>"map"));
//        if(!$rating->count() && count($marker) > 0)
//        {
//            $ratingParams = array();
//            $ratingParams['object_id'] = $markerId;
//            $ratingParams['object_type'] = "map";
//            $ratingParams['user_id'] = $userAuth->id;
//            $mapParams = array();
//            $rowMarker = $marker->current();
//            if($ratingValue == 1)
//                $mapParams['rating'] = $rowMarker->rating + 1;
//            else 
//                $mapParams['rating'] = $rowMarker->rating - 1;
//
//            if($mapParams['rating'] == 0 && $ratingValue==1)
//                $mapParams['rating'] = 1;
//            else if($mapParams['rating'] == 0 && $ratingValue!=1)
//                $mapParams['rating'] = -1;
//            
//            $rowMarker->setFromArray($mapParams);
//            $rowMarker->save();
//            
//            $rowRating = $ratingManager->createRow($ratingParams);
//            $rowRating->save();
//            
//        }
//        $this->_helper->layout()->disableLayout();
//        $this->renderScript('ajax.phtml');
        
    }
    
}