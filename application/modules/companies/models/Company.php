<?php

/**
 * 
 * Компания
 * 
 */
class Companies_Model_Company extends Companies_Model_Abstract {

    /**
     * Типы компаний. Строковые (чтобы можно было свободно менять порядок) идентификаторы исп. в логике
     * @var array
     */
    public static $_types = array(
        'proj' => 'proyectos',
        'club' => 'clubes',
        'rest' => 'restaurantes',
        'cafe' => 'cafés',
        'bar' => 'bares',
        'cine' => 'cines',
        'teatro' => 'teatros',
        'concierto' => 'salas de conciertos',
        'eventos' => 'centros de eventos',
        'art' => 'galerías de arte',
        'mall' => 'malls',
        'cultural' => 'centros cultural',
        'hotel' => 'hoteles',
        'boutique' => 'boutiques',
        'belleza' => 'belleza',
        'sport' => 'sport',
        'otro' => 'otros'
    );
    public static $header_types = array(
        'proj' => 'Proyectos',
        'club' => 'Clubes',
        'rest' => 'Restaurantes',
        'cafe' => 'Cafés',
        'bar' => 'Bares',
        'cine' => 'Cines',
        'teatro' => 'Teatros',
        'concierto' => 'Salas de conciertos',
        'eventos' => 'Centros de Eventos',
        'art' => 'Galerías de arte',
        'mall' => 'Malls',
        'cultural' => 'Centros cultural',
        'hotel' => 'Hoteles',
        'boutique' => 'Boutiques',
        'belleza' => 'Belleza',
        'sport' => 'Sport',
        'otro' => 'Otros'
    );
    public static $fun_types = array(
        'proj' => 'Proyectos',
        'club' => 'Clubes',
        'cafe' => 'Cafés',
        'bar' => 'Bares',
        'cine' => 'Cines',
        'teatro' => 'Teatros',
        'concierto' => 'Salas de conciertos',
        'eventos' => 'Centros de Eventos',
        'art' => 'Galerías de arte'
    );
    public static $food_types = array(
        'rest' => 'Restaurantes',
        'cafe' => 'Cafés',
        'bar' => 'Bares'
    );
    public static $shop_types = array(
        '1' => 'Accesorios',
        '2' => 'Colmado',
        '3' => 'Para los animales',
        '4' => 'Cosmetica',
        '5' => 'Belleza y salud',
        '6' => 'Muebles',
        '7' => 'Ropas',
        '8' => 'Salon de matrimonio',
        '9' => 'Deporte y turismo',
        '10' => 'Construcción',
        '11' => 'Recuerdos y regalos',
        '12' => 'Electronica',
        '13' => 'Joyeria',
    );
    public static $company_per_lazypage = 40;
    public static $shop_per_lazypage = 9;
    public static $limit_photos = 15;
    public static $best_dishes = 8;

    /**
     * @return string
     */
    public function __toString() {
        return (string) $this->name;
    }
    
    public function getUrl($action = 'profile')
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        if (!is_null($this->url))
            return $viewRenderer->view->url(array('company_url' => $this->url, 'company_type' => $this->getTypeUrl(), 'action' => $action), 'company_seo');
        else
            return $viewRenderer->view->url(array('companyId' => $this->id, 'action' => $action), 'company');        
    }
    
    public function getTypeUrl()
    {
        return mb_strtolower(str_replace(' ', '-', self::$_types[ $this->type ]), 'UTF-8');
    }

    // счетчик просмотров по дням
    public function count() {

        $table = new Companies_Model_Table_Views();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)->setIntegrityCheck(false)->where('id_company = ?', $this->id)->where('date = ?', date('Y-m-d'))->limit(1);

        $counter = $table->fetchRow($select);
        if ($counter) {
            $counter->counter = $counter->counter + 1;
            $counter->save();
        } else {
            $counter = $table->createRow(array('id_company' => $this->id, 'counter' => 1));
            $counter->save();
        }
    }

    // генерация vcard
    public function getvCard() {

        $vcard = "BEGIN:VCARD\r\n";

        $vcard.= "VERSION:3.0\r\n";
        $vcard.= "CLASS:PUBLIC\r\n";
        $vcard.= "N;CHARSET=UTF-8:" . $this->name . "\r\n";
        $vcard.= "ORG;CHARSET=UTF-8:" . $this->name . "\r\n";
        $vcard.= "TEL;TYPE=WORK:" . $this->phone . "\r\n";
        $vcard.= "ADR;TYPE=WORK:" . $this->address . "\r\n";

        if (!empty($this->site) && !is_null($this->site))
            $vcard.= "URL:" . $this->site . "\r\n";

        $emails = $this->getEmails();
        foreach ($emails as $email) {
            $vcard.= "EMAIL;TYPE=INTERNET:" . $email . "\r\n";
            break;
        }

        $vcard.= "END:VCARD";
        //die($vcard);
        return $vcard;
    }

    // получить компании по тому же адресу
    // короче по-сути это комплексы всякие
    public function getComplexes() {
        $data = array();
        $table = new Companies_Model_Table_Companies();
        $select = $this->select()->where('address = ?', $this->address)->where("address LIKE 'ул%' OR address LIKE 'пр%'")->where('id <> ?', $this->id);
        $rows = $table->fetchAll($select);
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getOramas($limit = 0) {
        $data = array();
        if ($limit > 0)
            $rows = $this->findDependentRowset('Orama_Model_Table_Oramas', null, $this->select()->limit($limit));
        else
            $rows = $this->findDependentRowset('Orama_Model_Table_Oramas');

        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    /**
     * Возвращает список сфер деятельности
     * @return array
     */
    public function getSpheres() {
        $items = Array();
        $table = new Companies_Model_Table_LinkTypes();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                //->joinInner( array('company' => 'companies_company'), 'company.id = companies_link_types.id_company', 'id_sphere' )
                ->joinInner('companies_spheres', 'companies_spheres.id = companies_link_types.id_sphere', array('id', 'name'))
                ->where('companies_link_types.id_company = ?', $this->id);
        //->order( 'date DESC' );        
        //throw new Exception($select);

        $rows = $table->fetchAll($select);
        foreach ($rows as $row) {
            $items[$row->id_sphere] = $row;
        }
        return $items;
    }

//    /**
//     * Возвращает список типов деятельности по ид. сферы или все
//     * @param $sphere_id OPTIONAL сфера деятельности компании
//     * @return array
//     */
//    public static function getSphereActivities( $sphere_id = null )
//    {
//            $acts = array();
//            foreach( self::$_activities as $k=>$v ) {
//                    if ( empty($sphere_id) or $v[0] == $sphere_id ) $acts[$k] = $v[1];
//            }
//            return $acts;
//    }
//
//    /**
//     * Возвращает массив сферы деятельности < типы деятельности (вложенные множества)
//     * @return array
//     */
//    public static function getCatalog() {
//            $catalog = array();
//            foreach( self::$_spheres as $k=>$v ) {
//                    $catalog[$k] = array( 'name' => $v, 'subs' => array() );
//            }
//            foreach( self::$_activities as $k=>$v ) {
//                    list($parent, $name) = $v;
//                    if ( isset( $catalog[$parent] ) ) {
//                            $catalog[$parent]['subs'][$k] = $name;
//                    }
//            }
//            return $catalog;
//    }

    /**
     * Возвращает список типов
     * @return array
     */
    public static function getTypes() {
        return self::$_types;
    }

    public static function getHeaderTypes() {
        return self::$header_types;
    }

//    /**
//     * Возвращает название сферы деятельности компании
//     * @return string
//    */
//    public function getActivitySphere() {
////            if ( !is_null($this->industry) and isset( self::$_activities[ $this->industry ] ) ) {
////                    $sphere_id = self::$_activities[ $this->industry ][0];
////                    if ( isset( self::$_spheres[ $sphere_id ] ) )
////                            return self::$_spheres[ $sphere_id ];
////                    else
////                            return '';
////            } else {
////                    return '';
////            }
//        
//        
//    }

    /**
     * Возвращает название типа компании
     * @return string
     */
    public function getType() {
        if (!is_null($this->type) and isset(self::$_types[$this->type]))
            return self::$_types[$this->type];
        else
            return NULL;
    }
    
    public function getHeaderType() {
        if (!is_null($this->type) and isset(self::$header_types [$this->type]))
            return self::$header_types[$this->type];
        else
            return NULL;
    }

    /**
     * Возвращает тип компании
     * @return type 
     */
    public function getRawType() {
        if (!is_null($this->type))
            return $this->type;
        else
            return null;
    }

    public function getEventTab() {
        return '<a href="/company' . $this->id . '/events">' . $this->name . '</a>';
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getPhone() {
        if (!is_null($this->phone))
            return "<nobr>" . $this->phone . "</nobr>";
        else
            return NULL;
    }

    /**
     * @return array
     */
    public function getPhoneAdds() {
        if (!empty($this->phone_add))
            return json_decode($this->phone_add);
        else
            return NULL;
    }

    /**
     * @return array
     */
    public function getEmails() {
        if (!empty($this->email))
            return json_decode($this->email);
        else
            return array();
    }

    /**
     * @return string
     */
    public function getSite() {
        if (empty($this->site)) return null;
        return '<a href="' . $this->site . '" target="_blank">' . str_replace('http://', '', $this->site) . '</a>';
    }

    public function getFacebook() {
        if (empty($this->facebook)) return null;
        return '<a href="' . $this->facebook . '" target="_blank"><img class="Ifacebook2" src="/zeta/0.png" width="66" height="16" alt="" /></a>';
    }

    public function getTweeter() {
        if (empty($this->tweeter)) return null;
        return '<a href="' . $this->tweeter . '" target="_blank"><img class="Itwitter2" src="/zeta/0.png" width="66" height="16" alt="" /></a>';
    }

    /**
     * @return string
     */
    public function getPaymentTypes() {
        return $this->payment_types;
    }

    /**
     * @return string
     */
    public function getHave() {
        return $this->have;
    }

    /**
     * @return string
     */
    public function getCheckout() {
        return $this->checkout;
    }

    /**
     * @return string
     */
    public function getRegime() {
        return $this->regime;
    }

//	/**
//	 * @return string
//	*/
//	public function getDeliveryTime() {
//		return 'c ' . $this->delivery_from . ' до ' . $this->delivery_to;
//	}

    /**
     * @return string
     */
    public function getDeliveryPhone() {
        return $this->delivery_phone;
    }

    /**
     * @return string
     */
    public function getCompanyName() {
        //return '<a class="grey" href="/company' . $this->id . '">' . $this->name . '</a>';
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        return '<a title="Perfil de empresa ' . $this->name . '" class="black" href="' . $this->getUrl() . '</a>';
    }

    /**
     * @return string
     */
    public function getAvatar($class = '', $link = true, $type = '') {
        if (!is_null($this->photo) && trim($this->photo) != "") {
            if (file_exists(Zend_Registry::get('upload_path') . '/company/avatars/' . $type . $this->photo)) {
                $avatarImg = $this->photo;
                $path = Zend_Registry::get('upload_path') . '/company/avatars/' . $type . $this->photo;
                $file = "/files/company/avatars/" . $type . $avatarImg;
            } else {
                // Если запись есть, а файла фактически нет
                $avatarImg = "globallogo.jpg";
                $path = Zend_Registry::get('upload_path') . '/company/avatars/' . $avatarImg;
                $file = "/files/company/avatars/" . $avatarImg;
            }
        } else {
            $avatarImg = "globallogo.jpg";
            $path = Zend_Registry::get('upload_path') . '/company/avatars/' . $avatarImg;
            $file = "/files/company/avatars/" . $avatarImg;
        }

        if (is_file($path)) {

            $size = getimagesize($path);
            if ($link)
                $avatar = "<a href='" . $this->getUrl() . "'><img " . $class . "src='" . $file . "' width='" . $size[0] . "' height='" . $size[1] . "' alt='' /></a>";
            else
                $avatar = "<img " . $class . "src='" . $file . "' width='" . $size[0] . "' height='" . $size[1] . "' alt='' itemprop='logo' />";

            return $avatar;
        } else {
            return '';
        }
    }

//
//    /**
//     *
//     * @return Companies_Model_Photo
//     */
//    public function getMainPhoto() {
//        if (empty($this->photo)) return null;
//        list($first) = explode(',', $this->photo, 2 );
//        return $first;
//    }

    public function getBage() {
        if (empty($this->photos))
            return null;
        $photo = json_decode($this->photos, true);
        return $photo[0];
    }

    /**
     * @return array
     */
    public function getPhotos($limit = 0) {
        if (empty($this->photos))
            return null;
        if ($limit > 0) {
            $items = array();
            $photos = json_decode($this->photos, true);

            if ((count($photos)) < $limit)
                $limit = count($photos);

            for ($i = 0; $i < $limit; $i++) {
                $items[] = $photos[$i];
            }
            return $items;
        } else {
            return json_decode($this->photos, true);
        }
    }

    public function getLocationOnMap() {
        $rows = $this->findDependentRowset('Application_Model_Table_Map', null, $this->select()->limit(1), array('lat', 'lng'));
        if (count($rows) > 0)
            return $rows->current();
        else
            return NULL;
    }

//    // *******
//    // ТУРЫ
//    // ******* 
//
//    public function getCountriesTourRoutes() {
//        $items = Array();
//        $table = new Companies_Model_Table_Tours();
//        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
//                ->setIntegrityCheck(false)
//                ->joinInner(array('company' => 'companies_company'), 'company.id = companies_tours.id_company', 'id')
//                ->joinInner('companies_tour_route', 'companies_tour_route.id_tour = companies_tours.id', 'id_country')
//                ->where('company.id = ?', $this->id);
//        //->order( 'date DESC' );        
//
//        $table = new Application_Model_Table_Countries();
//        $rows = $table->fetchAll($select);
//        foreach ($rows as $row) {
//            $country = $table->find($row->id_country)->current();
//            if ($country)
//                $items[] = $country->name;
//        }
//        return $items;
//
//
////        $rows = $this->fetchAll( $this->select()
////                ->distinct()
////                ->from($this->_name, 'id_country')
////        );
////        $countries = array();
////        foreach( $rows as $row ) {
////            $country = $table->find($row->id_country)->current();
////            if ($country) $countries[] = $country->name;
////        }
////        return $countries;        
//    }
//
//    public function getTours($count = false, $limit = 0) {
//        $data = array();
//
//        if (!$count) {
//            $tours = $this->findDependentRowset('Companies_Model_Table_Tours');
//        } else {
//            $tours = $this->findDependentRowset('Companies_Model_Table_Tours', null, null, array('id'));
//        }
//
//        if ($tours) {
//
//            //throw new Exception(count($feeds));
//
//            foreach ($tours as $row) {
//                $data[$row->id] = $row;
//            }
//        }
//        return $data;
//    }

    // *******
    // ЛЕНТЫ
    // ******* 

    public function getFeeds() {
        $rows = $this->findDependentRowset('Feed_Model_Table_Feed');
        if (count($rows) > 0)
            return $rows->current(); else
            return NULL;
    }

    // *******
    // ВАКАСИИ
    // *******        

    /**
     * @return array
     */
    public function getVacancies($industry_id = null) {
        $data = array();
        if (empty($industry_id)) {
            $rows = $this->findDependentRowset('Companies_Model_Table_Vacancies');
        } else {
            $rows = $this->findDependentRowset('Companies_Model_Table_Vacancies', null, $this->select()->where('industry = ?', $industry_id));
        }
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getPositionVacancies() {
        $data = array();
//		$table = new Companies_Model_Table_Vacancies();
//		$select = $table->select()
//                        ->from("companies_vacancy",array("industry" ,"positionCount"=>"COUNT(industry)"))
//                        ->where('companies_vacancy.id_company = ?', $this->id)
//                        ->group('companies_vacancy.industry');
//        //throw new Exception($select);
//		$rows = $table->fetchAll( $select );
        $rows = $this->findDependentRowset('Companies_Model_Table_Vacancies', null, $this->select()->from("companies_vacancy", array("id", "industry", "positionCount" => "COUNT(industry)"))->group('industry'));
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getCountIndustryVacancy($industry_id) {
        $rows = $this->findDependentRowset('Companies_Model_Table_Vacancies', null, $this->select()->where('industry = ?', $industry_id));
        return (bool) count($rows);
    }

    /**
     * @return array
     */
    public function getResumes() {
        $data = array();
        $rows = $this->findDependentRowset('Companies_Model_Table_Resumes');
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    // *******
    // СОБЫТИЯ
    // *******        
    public function getEvents($count = false, $limit = 0) {
        $data = array();
        if (!$count) {
            //$rows = $this->findDependentRowset('Events_Model_Table_Events');
            $table = new Events_Model_Table_Events();
            $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                    ->setIntegrityCheck(false)
                    ->joinLeft('events_walks', 'events_walks.event_id = events_event.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `events_walks` WHERE `events_walks`.event_id = `events_event`.id) AS num_attending'))
                    ->where('id_company = ? OR id_productor = ?', $this->id)
                    ->where('events_event.is_confirmed = 1')
                    ->where('TO_DAYS(events_event.end_date) - TO_DAYS(NOW()) >= 0 ')
                    ->order('events_event.end_date ASC');

            if ($limit > 0)
                $select->limit($limit);

            $rows = $table->fetchAll($select);
        } else
            $rows = $this->findDependentRowset('Events_Model_Table_Events', null, null, array('id'));

        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getPastEvents($limit = 0) {
        $data = array();

        $table = new Events_Model_Table_Events();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->joinLeft('events_walks', 'events_walks.event_id = events_event.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `events_walks` WHERE `events_walks`.event_id = `events_event`.id) AS num_attending'))
                ->where('id_company = ?', $this->id)
                ->where('events_event.is_confirmed = 1')
                ->where('TO_DAYS(events_event.end_date) - TO_DAYS(NOW()) < 0')
                ->order('events_event.end_date DESC');

        if ($limit > 0)
            $select->limit($limit);

        $rows = $table->fetchAll($select);

        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    // по умолчанию только вечерины
    public function getProximoEvents($limit = 0, $type = 1) {
        $data = array();

        $table = new Events_Model_Table_Events();
        $select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->joinInner('events_event', 'events_event.id_company = companies_company.id', array('id', 'url', 'name', 'photo'))
                ->where('TO_DAYS(start_date) - TO_DAYS(NOW()) >= 0 ')
                ->where('events_event.is_confirmed = 1')
                ->where('id_company = ?', $this->id);

        if ($type > 0)
            $select->where('type = ?', $type);

        $select->group('events_event.name')->order('events_event.start_date DESC');

        if ($limit > 0)
            $select->limit($limit);

        $rows = $table->fetchAll($select);

        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getVisitors($limit = 0) {
        $data = array();

        //$rows = $this->findDependentRowset('Events_Model_Table_Events');
        $table = new Application_Model_Table_Users();
        $select = $table->select()->from(array('u' => 'users'), array('id', 'avatar', 'username', 'rate'))
                //->setIntegrityCheck(false)
                ->joinLeft('events_walks', 'events_walks.user_id = u.id', null)
                ->joinInner('events_event', 'events_walks.event_id = events_event.id', null)
                ->where('events_event.id_company = ?', $this->id)
                ->where('events_event.is_confirmed = 1')
                ->order('rate DESC')
                ->group('id');

        if ($limit > 0)
            $select->limit($limit);

        //throw new Exception($select);

        $rows = $table->fetchAll($select);

        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getEventGalleries($limit = 0) {
        
        $data = array();

        //$rows = $this->findDependentRowset('Events_Model_Table_Events');
        $table = new Events_Model_Table_PhotoReports();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->joinLeft('events_event', 'events_photoreport.event_id = events_event.id', array('start_date as event_date'))
                //->where( 'TO_DAYS(start_date) - TO_DAYS(NOW()) >= 0 ' )
                ->where('events_event.id_company = ?', $this->id)
                ->where('events_event.is_confirmed = 1')
                ->order('events_event.start_date DESC')
                ->group('id');

        if ($limit > 0)
            $select->limit($limit);

        //throw new Exception($select);

        $rows = $table->fetchAll($select);

        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;        
        
//        $data = array();
//        if (!$count)
//            $events = $this->findDependentRowset('Events_Model_Table_Events', null, null, array('start_date'));
//        else
//            $events = $this->findDependentRowset('Events_Model_Table_Events', null, null, array('id'));
//
//        if ($events) {
//            foreach ($events as $event) {
//                if (!$count)
//                    $rows = $event->findDependentRowset('Events_Model_Table_PhotoReports', null, $this->select()->order('start_date DESC'));
//                else
//                    $rows = $event->findDependentRowset('Events_Model_Table_PhotoReports', null, null, array('id'));
//
//                foreach ($rows as $row) {
//                    $data[$row->id] = $row;
//                }
//            }
//        }
//        return $data;
    }

    // *****
    // ПОСТЫ
    // *****

    /**
     * Возвращает список постов ленты компании
     *
     * @param array $params
     */
    public function getListPosts($limit = 0) {

        $table = new Post_Model_Table_Post();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->joinInner(array('company' => 'companies_company'), 'company.id = post_posts.company_id', null)
                ->where('post_posts.rubric_id <> 1')
                ->where('company.id = ?', $this->id)
                ->order('date DESC');

        if ($limit > 0)
            $select->limit($limit);

        //throw new Exception($select);
        $rows = $table->fetchAll($select);
        $items = array();
        foreach ($rows as $row) {
            $items[$row->id] = $row;
        }
        return $items;
    }
    
    public function getListVideos($limit = 0) {

        $table = new Post_Model_Table_Post();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->joinInner(array('company' => 'companies_company'), 'company.id = post_posts.company_id', null)
                ->where('post_posts.rubric_id = 1')
                ->where('company.id = ?', $this->id)
                ->order('date DESC');

        if ($limit > 0)
            $select->limit($limit);

        //throw new Exception($select);
        $rows = $table->fetchAll($select);
        $items = array();
        foreach ($rows as $row) {
            $items[$row->id] = $row;
        }
        return $items;
    }    

    public function getPosts($count = false, $limit = 0) {
        $data = array();

        if (!$count) {
            $feeds = $this->findDependentRowset('Feed_Model_Table_Feed');
        } else {
            $feeds = $this->findDependentRowset('Feed_Model_Table_Feed', null, null, array('id'));
        }

        if ($feeds) {

            //throw new Exception(count($feeds));

            foreach ($feeds as $feed) {

                if (!$count) {
                    $select = $this->select()->order('date DESC');
                    if ($limit > 0)
                        $select->limit($limit);
                    $rows = $feed->findDependentRowset('Post_Model_Table_Post', null, $select);
                } else {

                    $rows = $feed->findDependentRowset('Post_Model_Table_Post', null, null, array('id'));
                }

                foreach ($rows as $row) {
                    $data[$row->id] = $row;
                }
            }
        }
        return $data;
    }

    // *****
    // ОРАМА
    // *****

    public function oramaCount() {
        $rows = $this->findDependentRowset('Orama_Model_Table_Oramas', null, null, array('id'));
        if (count($rows) > 0)
            return count($rows); else
            return NULL;
    }

    // *****
    // ТОВАРЫ
    // *****
    public function getRootCatalogCategories() {
        $data = array();
        $rows = $this->findDependentRowset('Companies_Model_Table_CatalogCategories', null, $this->select()->where('parent_id = 0'));
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getCatalogCategories() {
        $data = array();
        $rows = $this->findDependentRowset('Companies_Model_Table_CatalogCategories');
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getCatalogUnits($category_id) {
        $units = array();
        $rows = $this->findDependentRowset('Companies_Model_Table_Units', null, $this->select()->where('category_id = ?', $category_id)->order('date DESC'));


        // создадим матрицу товаров, если товаров на несколько строк
        if (count($rows) > 4) {
            $vert_size = ceil(count($rows) / 4);
            $col_limit = count($rows) - (($vert_size - 1) * 4) - 1;
            $row_limit = $vert_size - 1;
            $row = 0;
            $col = 0;
            for ($i = 0; $i < count($rows); $i++) {
                if ($row == $vert_size) {
                    $row = 0;
                    $col++;
                }
                // ограничение по столбцам
                if (($col > $col_limit) && ($row == $row_limit)) {
                    $row = 0;
                    $col++;
                }
                $ind = $col + $row * 4;
                $units[$col][$row] = $rows[$ind];
                $row++;
            }
        } else {
            $j = 0;
            foreach ($rows as $row) {
                $units[$j][0] = $row;
                $j++;
            }
        }

        return $units;
    }

    public function getSimpleUnits($category_id, $limit = 0) {
        $items = array();

        $select = $this->select()->where('category_id = ?', $category_id);
        if ($limit > 0)
            $select->limit($limit);

        $select->order('date DESC');

        $rows = $this->findDependentRowset('Companies_Model_Table_Units', null, $select);

        foreach ($rows as $row) {
            $items[$row->id] = $row;
        }
        return $items;
    }

    public function getAllUnits($limit = 0) {
        $items = array();
        $select = $this->select();
        if ($limit > 0)
            $select->limit($limit);

        $rows = $this->findDependentRowset('Companies_Model_Table_Units', null, $select);
        foreach ($rows as $row) {
            $items[$row->id] = $row;
        }
        return $items;
    }

    public function getShopDescription() {
        if (mb_strlen($this->description, 'UTF-8') > 140)
            return mb_substr($this->description, 0, 140, 'UTF-8') . '...';
        else
            return $this->description;
    }

    /**
     * Возвращает разделы меню компании
     * @return array
     */
    public function getMenuCatalogRoot() {
        $data = array();
        //$rows = $this->findDependentRowset('Companies_Model_Table_MenuCatalog', null, $this->select()->from("companies_menu_catalog",array("id","industry" ,"positionCount"=>"COUNT(industry)"))->group('industry'));
        $rows = $this->findDependentRowset('Companies_Model_Table_MenuCatalog');
//		foreach ( $rows as $row ) {
//			if ( empty($row->id_parent) ) $data[$row->id] = $row;
//		}
//		return $data;
        $table = new Companies_Model_Table_MenuCatalog();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                //->joinLeft( 'companies_menu_dish', 'id = companies_menu_dish.id_catalog', 'id_catalog')
//                        ->from(array("c" => "companies_menu_catalog"), array(
//                            "id",
//                            "id_company",
//                            "name"
//                        ))
                ->joinLeft(array("md" => "companies_menu_dish"), "md.id_catalog = companies_menu_catalog.id", array(
                    'COUNT(md.id) as items_count',
                    'MIN(md.price) as min_price',
                    'MAX(md.price) as max_price',
                ))
                ->where('id_company = ?', $this->id)
                ->group('companies_menu_catalog.id');

        $rows = $table->fetchAll($select);
        $data = array();
        foreach ($rows as $row) {
            //if ( empty($row->id_parent) ) $data[$row->id] = $row;
            $data[$row->id] = $row;
        }
        return $data;
    }

//    public function getDishes() {
//            $data = array();
//            //$rows = $this->findDependentRowset('Companies_Model_Table_MenuCatalog', null, $this->select()->from("companies_menu_catalog",array("id","industry" ,"positionCount"=>"COUNT(industry)"))->group('industry'));
//            $rows = $this->findDependentRowset('Companies_Model_Table_MenuCatalog');
////		foreach ( $rows as $row ) {
////			if ( empty($row->id_parent) ) $data[$row->id] = $row;
////		}
////		return $data;
//            $table = new Companies_Model_Table_MenuCatalog();
//            $select = $table->select()
//                    ->from($table, array('id_company'))
//                    ->setIntegrityCheck(false)
//                    ->joinLeft(array("md" => "companies_menu_dish"), "md.id_catalog = companies_menu_catalog.id")
//                    ->where( 'id_company = ?', $this->id );
//                    //->group( 'id_company' );
//            //throw new Exception($select);
//            $rows = $table->fetchAll( $select );
//            foreach ( $rows as $row ) {
//                $data[$row->id] = $row;
//            }
//            return $data;
//    }    
//        // Получает макс. и мин. цену для категории
//        public function getMenuCatalogRootPrices($catalog_id) {
//		$data = array();
//		$table = new Companies_Model_Table_MenuCatalog();
//		$select = $table->select()
//                        ->setIntegrityCheck(false)
//			//->joinLeft( 'companies_menu_dish', 'id = companies_menu_dish.id_catalog', 'id_catalog')
//                        ->from(array("c" => "companies_menu_catalog"), array(
//                            "id",
//                            "id_company",
//                            "name"
//                        ))
//                        ->joinLeft(array("d" => "companies_menu_dish"), "c.id = d.id_catalog", array(
//                            "max_price" => "MAX(d.price)",
//                            "min_price" => "MIN(d.price)"
//                        ))
//			->where( 'id_company = ?', $this->id )
//                        ->where( 'id =?', $catalog_id );
//
//		$rows = $table->fetchAll( $select );
//		$data = array();
//		foreach ( $rows as $row ) {
//                    //if ( empty($row->id_parent) ) $data[$row->id] = $row;
//                    $data[$row->id] = $row;
//		}
//		return $data;
//        }
    // *****
    // БЛЮДА
    // *****        

    /**
     * Возвращает лучшие блюда
     * @param int $max максимально
     * @return array
     */
    public function getBestDishes($max = 8) {
        $table = new Companies_Model_Table_MenuDishes();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->joinLeft('companies_menu_catalog', 'id_catalog = companies_menu_catalog.id', 'id_company')
                ->where('id_company = ?', $this->id)
                ->where('is_best = 1')
                ->limit($max);

        $rows = $table->fetchAll($select);
        $dishes = array();
        foreach ($rows as $row) {
            $dishes[$row->id] = $row;
        }
        return $dishes;
    }

//    // *****
//    // КОМНАТЫ
//    // *****        
//
//    /**
//     * @return array
//     */
//    public function getRooms() {
//        $data = array();
//        $rows = $this->findDependentRowset('Companies_Model_Table_Rooms', null, $this->select()->order('date DESC'));
//        foreach ($rows as $row) {
//            $data[$row->id] = $row;
//        }
//        return $data;
//    }
//
//    public function getRoomsPrices() {
//        $table = new Companies_Model_Table_Rooms();
//        $select = $table->select()
//                ->from("companies_room", array(
//                    "MAX(price) as max_price",
//                    "MIN(price) as min_price"
//                ))
//                ->where("id_company = ?", $this->id)
//                ->limit("1");
//        $rows = $table->fetchAll($select);
//        if (!empty($rows)) {
//            return $rows->current();
//        }
//    }

    // *********
    // СЛУЖЕБНЫЕ
    // *********        

    /**
     * @param int $user_id
     * @return array
     */
    public function isLiked($user_id) {
        $rows = $this->findDependentRowset('Companies_Model_Table_Likes', null, $this->select()->where('id_user = ?', $user_id));
        return (bool) count($rows);
    }

    public function profileLikeCount() {
        $qn = new Qlick_Num();
        $rows = $this->findDependentRowset('Companies_Model_Table_Likes');
        if (count($rows) > 0)
            return count($rows); else
            return NULL;
    }

    public function likeCount() {
        $rows = $this->findDependentRowset('Companies_Model_Table_Likes');
        if (count($rows) > 0)
            return count($rows); else
            return NULL;
    }

    public function getOwner() {
        $data = array();
        //$table = new Companies_Model_Table_Employers();
        $table = new Application_Model_Table_Users();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                //->joinLeft( 'users', 'companies_employer.id_user = users.id', array('id'))
                ->joinLeft('companies_employer', 'companies_employer.id_user = users.id', array('id_company', 'is_owner', 'position'))
                ->where('companies_employer.id_company = ?', $this->id)
                ->where('companies_employer.is_owner = 1')
                ->limit(1)
        ;
        $row = $table->fetchRow($select);
        if ($row)
            return $row; else
            return NULL;
    }

    /**
     * @return array
     */
    public function getEmployers($count = false) {
        $data = array();
        $table = new Companies_Model_Table_Employers();
        $select = $table->select();

        if ($count)
            $select->from($table, array('id'));
        else
            $select->from($table);

        $select->setIntegrityCheck(false)
                ->joinLeft('users', 'companies_employer.id_user = users.id', array('firstname', 'lastname', 'email'))
                ->where('companies_employer.id_company = ?', $this->id)
                ->where('companies_employer.is_confirmed = 1')
                ->where('users.id <> 3');

        $rows = $table->fetchAll($select);
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getPartialScript($par = 'info') {
        $path = 'company/partials';
        if ($par == 'info') {
            switch ($this->type) {
                case 'rest' :
                case 'bar' :
                case 'cafe' :
                case 'shop' :
                    return "$path/infoShop.phtml";
                case 'pizz' :
                    return "$path/infoPizz.phtml";
                case 'inn' :
                    return "$path/infoInn.phtml";
                default:
                    return "$path/infoDef.phtml";
                    break;
            }
        }
        return '';
    }

//    /**
//     * @return string
//     */
//    public function getCustomTabs() {
//        switch ($this->type) {
//            case 'rest' :
//            case 'bar' :
//            case 'cafe' :
//            case 'pizz' : return array('menu' => '<span>Carta</span>');
//            //case 'shop' :
//            case 'recr' : return array('resume' => '<span>Aspirante</span>');
//            case 'tour' : return array('tours' => '<span>Tours</span>');
//            case 'inn' : return array('hotel' => '<span>Habitaciones</span>');
//            //case 'kino' :
//            //case 'thea' :
//            //case 'club' :
//            default:
//                break;
//        }
//        return array();
//    }

    /**
     * @return Zend_Navigation_Page
     */
    public function getPage() {
        $data = array(
            'id' => 'company-' . $this->id,
            'label' => $this->name,
            'title' => $this->name,
            'module' => 'companies',
            'controller' => 'company',
            'action' => 'profile',
            'params' => array('cid' => $this->id),
        );
        return Zend_Navigation_Page::factory($data);
    }

    /**
     * Устанавливает новые данные из массива, если они изменились
     * @param  array $data
     * @return $this
     */
    public function setFromArray(array $data) {
        $data = array_intersect_key($data, $this->_data);
        if (isset($data['phone_add'])) {
            if (empty($data['phone_add']) or $data['phone_add'] == '[]')
                $data['phone_add'] = null;
        }
        if (isset($data['email'])) {
            if (empty($data['email']) or $data['email'] == '[]')
                $data['email'] = null;
        }
        foreach ($data as $columnName => $value) {
            if ($this->_data[$columnName] != $value) {
                $this->__set($columnName, $value);
            }
        }
        return $this;
    }

    /**
     * Возвращает данные в массиве колонка=>значение
     * @return array
     */
    public function toArray() {
        $data = parent::toArray();
        $data['id'] = (int) $data['id'];
        return $data;
    }

}