<?php

/**
 * 
 * Сотрудник
 * 
 */
class Application_Model_User extends Application_Model_Abstract {

    protected $_user;
    
    public static $user_per_lazypage = 50; // 7 * 7

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullName();
    }

    /**
     * Возвращает данные в массиве колонка=>значение
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['id'] = (int) $data['id'];
        return $data;
    }
    
    public function getSex()
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        switch ($this->sex) {
                case 1:  return '<a href="' . $viewRenderer->view->url(array('sex' => 'hombres'), 'peoples') . '">Hombre</a>';
                case 2:  return '<a href="' . $viewRenderer->view->url(array('sex' => 'mujeres'), 'peoples') . '">Mujer</a>';
        }
    }  
    
    public function getAge() {
        if (!is_null($this->year_birth)) {
            if($this->month_birth > date('m') || $this->month_birth == date('m') && $this->day_birth > date('d'))
                return !is_null($this->year_birth) ? (date('Y') - $this->year_birth - 1) . ' años' : NULL;
            else
                return !is_null($this->year_birth) ? (date('Y') - $this->year_birth) . ' años' : NULL;
        } else {
            return NULL;
        }
    }

        // расчет рейтинга пользователя
    // http://habrahabr.ru/company/darudar/blog/143188/#habracut
    public function calcrating()
    {
         $table = new Application_Model_Table_Rating();
         $karma = $table->calcUserKarma($this);        
    }
    
    public function getKarma($date = null)
    {
        $table = new Users_Model_Table_Karma();
        $select = $table->select( Zend_Db_Table::SELECT_WITH_FROM_PART )->setIntegrityCheck(false);
        if (!is_null($date))
            $select->where('date < ?', $date);
        
        $select->where('id_user = ?', $this->id)->order('date DESC')->limit(1);
        //throw new Exception($select);
        
        $karma = $table->fetchRow($select);
        if ($karma) return $karma->karma; else return 0;
    }    
    
    public function getStartKarma()
    {
        $table = new Users_Model_Table_Karma();
        $select = $table->select( Zend_Db_Table::SELECT_WITH_FROM_PART )->setIntegrityCheck(false)
                ->where('id_user = ?', $this->id)->order('date ASC')->limit(1);
        //throw new Exception($select);
        
        $karma = $table->fetchRow($select);
        if ($karma) {
            return $karma->karma;
        } else {
            // если кармы не было, но сделаем инициализацию на 01.01.2011
            $karma = $table->createRow( array('id_user' => $this->id, 'karma' => 0, 'date' => date('2011-01-01')) );
            $karma->save();
            return 0;
        }
            
    } 

    /**
     * Возвращает запись пользователя
     * @return Zend_Db_Table_Row пользователь
     */
    public function getUser()
    {
        if (!isset($this->_user)) {
            $this->_user = $this->findParentRow('Application_Model_Table_Users');
            if (!$this->_user)
                throw new Companies_Model_Exception('Пользователь не существует');
        }
        return $this->_user;
    }

    /**
     *
     * @param boolean $getOther Возвращать введенное пользователем название города если имеется
     */
    public function getCityFromName($getOther = true)
    {
        if (!empty($this->city_other) && $getOther) {
            return $this->city_other;
        }

        if (!empty($this->city_id)) {
            $cityTable = new Application_Model_Table_Cities();
            $userCity = $cityTable->getCity($this->city_id);

            return $userCity;
        }
        
        return NULL;
    }

    /**
     *
     * @param boolean $getCount 
     */
    public function getPosts($count = false)
    {
        $items = array();
        if (!$count)
            $rows = $this->findDependentRowset('Post_Model_Table_Post', null, $this->select()->where('rubric_id <> 1')->order('date DESC'));
        else
            $rows = $this->findDependentRowset('Post_Model_Table_Post', null, $this->select()->where('rubric_id <> 1')->order('date DESC'), array('id'));  

        foreach ( $rows as $row ) {
            $items[ $row->id ] = $row;
        }
        return $items;       
    }
    
    public function getFeeds($count = false)
    {
        $items = array();
        if (!$count)
            $rows = $this->findDependentRowset('Feed_Model_Table_Feed');
        else
            $rows = $this->findDependentRowset('Feed_Model_Table_Feed', null, null, array('id'));   
        
        foreach ( $rows as $row ) {
            $items[ $row->id ] = $row;
        }
        return $items;
    }
    
    public function getActividad()
    {
        $items = array();

        //throw new Exception($select);
        $item = array();
        $rows = $this->getWalkedAndCreatedEvents();
        
//        $rows = $table->fetchAll( $select );        
        foreach ( $rows as $row ) {
            $date = $row->start_date;
            $item['content_type'] = 'event';
            $item['date'] = $date;
            $item['row'] = $row;
            $items[] = $item;
        }
        unset($item);        
        
        
        // МУЗЫКА
        $table = new Music_Model_Table_Musics();
        $select = $this->select()->from($table, array('id', 'id_category', 'id_user', 'name', 'photo', 'info', 'photo', 'mp3', 'mp3files', 'author', 'viewed', 'date'))
                ->setIntegrityCheck(false) 
                ->where( 'id_user = ?', $this->id )
                ;

        //throw new Exception($select);
        $item = array();
        $rows = $table->fetchAll( $select );        
        foreach ( $rows as $row ) {
            $date = $row->date;
            //$row = $row->toArray();
            $item['content_type'] = 'music';
            $item['date'] = $date;
            $item['row'] = $row;
            $items[] = $item;
        }
        unset($item);
        
        // ПОСТЫ
        $table = new Post_Model_Table_Post();
        $select = $this->select()->from($table, array('id', 'rubric_id', 'name', 'photo', 'description', 'viewed', 'date'))
                ->setIntegrityCheck(false) 
                ->where( 'status = 1' )
                ->where( 'rubric_id <> 1' )
                ->where( 'user_id = ?', $this->id )
                ;

        //throw new Exception($select);
        $item = array();
        $rows = $table->fetchAll( $select );        
        foreach ( $rows as $row ) {
            $date = $row->date;
            //$row = $row->toArray();
            $item['content_type'] = 'post';
            $item['date'] = $date;
            $item['row'] = $row;
            $items[] = $item;
        } 
        unset($item);
        
         // ВИДЕО
        $table = new Post_Model_Table_Post();
        $select = $this->select()->from($table, array('id', 'rubric_id', 'name', 'photo', 'description', 'video', 'viewed', 'date'))
                ->setIntegrityCheck(false)
                ->where( 'rubric_id = 1' )
                ->where( 'status = 1' )
                ->where( 'user_id = ?', $this->id )
                ;

        //throw new Exception($select);
        $item = array();
        $rows = $table->fetchAll( $select );        
        foreach ( $rows as $row ) {
            $date = $row->date;
            //$row = $row->toArray();
            $item['content_type'] = 'video';
            $item['date'] = $date;
            $item['row'] = $row;
            $items[] = $item;
        }
        unset($item);
        
        // КОНТОРЫ
        $table = new Companies_Model_Table_Companies();
        $select = $this->select()->from($table, array('id', 'type', 'name', 'description', 'photo', 'phone', 'address', 'signup_date'))
                ->setIntegrityCheck(false)
                ->joinLeft( array('ce' => 'companies_employer'), 'ce.id_company = companies_company.id', array('id_user') )
                ->where( 'companies_company.is_confirmed = 1' )
                ->where( 'ce.id_user = ?', $this->id )
                ;

        //throw new Exception($select);
        $item = array();
        $rows = $table->fetchAll( $select );        
        foreach ( $rows as $row ) {
            $date = $row->signup_date;
            //$row = $row->toArray();
            $item['content_type'] = 'company';
            $item['date'] = $date;
            $item['row'] = $row;
            $items[] = $item;
        } 
        unset($item);
        
        // ФОТООТЧЕТЫ
        $table = new Events_Model_Table_PhotoReports();
        $select = $this->select()->from($table, array('id', 'event_id', 'user_id', 'photos', 'counter'))
                ->setIntegrityCheck(false) 
                ->where( 'is_confirmed = 1' )
                ->where( 'user_id = ?', $this->id )
                ;

        //throw new Exception($select);
        $item = array();
        $rows = $table->fetchAll( $select );        
        foreach ( $rows as $row ) {
            $date = $row->getEvent()->getDateEnd('Y-m-d');
            //$row = $row->toArray();
            $item['content_type'] = 'photoreport';
            $item['date'] = $date;
            $item['row'] = $row;
            $items[] = $item;
        }
        unset($item);
        
        return $items;        
        
    }

    /**
     *
     * @param boolean $getCount 
     */
    public function getEvents($count = false)
    {
        $items = array();
        if (!$count)
            $rows = $this->findDependentRowset('Events_Model_Table_Events', null, $this->select()->order('start_date DESC'));
        else
            $rows = $this->findDependentRowset('Events_Model_Table_Events', null, $this->select()->order('start_date DESC'), array('id'));   
        
        foreach ( $rows as $row ) {
            $items[ $row->id ] = $row;
        }
        return $items;
    }
    
    public function getVideos($count = false)
    {
        $items = array();
        if (!$count)
            $rows = $this->findDependentRowset('Post_Model_Table_Post', null, $this->select()->where('rubric_id = 1'));
        else
            $rows = $this->findDependentRowset('Post_Model_Table_Post', null, $this->select()->where('rubric_id = 1'), array('id'));  

        foreach ( $rows as $row ) {
            $items[ $row->id ] = $row;
        }
        return $items;
    } 
    
    public function getMusics($count = false)
    {
        $items = array();
        if (!$count)
            $rows = $this->findDependentRowset('Music_Model_Table_Musics', null, $this->select()->order('date DESC'));
        else
            $rows = $this->findDependentRowset('Music_Model_Table_Musics', null, null, array('id'));  

        foreach ( $rows as $row ) {
            $items[ $row->id ] = $row;
        }
        return $items;
    }    
    
    // используется в табе "события" профиля пользовтеля
    public function getWalkedAndCreatedEvents()
    {
        $data = array();
        $table = new Events_Model_Table_Events();
        $select = $table->select()->from($table, array('id', 'id_company', 'url', 'name', 'photo', 'description', 'place_name', 'place_address', 'start_time', 'end_date', 'end_time', 'counter', 'start_date'))
                ->setIntegrityCheck(false)
                ->joinLeft( 'events_walks', 'events_event.id = events_walks.event_id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `events_walks` WHERE `events_walks`.event_id = `events_event`.id) AS num_attending'))
                ->where( 'events_walks.user_id = ? OR events_event.author_id = ?', $this->id )
                ->where( 'events_event.is_confirmed = 1' ) 
                //->where( 'events_event.start_date < NOW()' )
                ->order( 'events_event.start_date DESC' )
                ;

        //throw new Exception($select);
        $rows = $table->fetchAll( $select );
        foreach ( $rows as $row ) {
                $data[$row->id] = $row;
        }
        return $data;    
    }

    // События которые посетил пользователь
    // используется для создания фоторепортажа
    public function getWalkedEvents()
    {
        $data = array();
        $table = new Events_Model_Table_Events();
        $select = $table->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
                ->setIntegrityCheck(false)
                ->joinInner( 'events_walks', 'events_event.id = events_walks.event_id', false)
                ->where( 'events_walks.user_id = ?', $this->id )
                ->where( 'events_event.is_confirmed = 1' )
                ->where( 'events_event.start_date < NOW()' );

        $rows = $table->fetchAll( $select );
        foreach ( $rows as $row ) {
                $data[$row->id] = $row;
        }
        return $data;
    }
    
    public function getEventsWithoutPhotoreport()
    {
        $data = array();
        $table = new Events_Model_Table_Events();
        $select = $table->select() 
                ->from($table, array('id', 'name'))
                ->setIntegrityCheck(false)
                ->joinLeft( 'events_photoreport', 'events_event.id <> events_photoreport.event_id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `events_photoreport` WHERE `events_event`.id = `events_photoreport`.event_id) AS photoreport_count') )                
                ->where( 'events_event.is_confirmed = 1' )
                ->where( 'TO_DAYS(events_event.end_date) - TO_DAYS(NOW()) < 0' );
        //throw new Exception($select);
        $rows = $table->fetchAll( $select );
        foreach ( $rows as $row ) {
            if ($row->photoreport_count == 0)
                $data[$row->id] = $row;
        }
        return $data;
    }    

    /**
     *
     * @param boolean $getCount 
     */
//    public function getPhotoReports($getCount = false) {
    public function getPhotoReports($count = false)
    {
        $data = array();
        if (!$count) {            
            //$table = new Events_Model_Table_Events();
            $table = new Events_Model_Table_PhotoReports();
            $data = array();
            $select = $table->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
                    ->setIntegrityCheck(false)
                    ->where( 'user_id = ?', $this->id )
                    ->where( 'is_confirmed = 1' )
                    ->order( 'date DESC' );
            
            $rows = $table->fetchAll( $select );
        } else {
            $rows = $this->findDependentRowset('Events_Model_Table_PhotoReports', null, $this->select()->where('is_confirmed = 1'), array('id'));     
        }
        
        foreach ( $rows as $row ) {
                $data[$row->id] = $row;
        }
        return $data;
            
    }

    /**
     *
     * @param boolean $getCount
     */
    public function getCompanies($getCount = false)
    {
        $data = array();
//        $table = new Companies_Model_Table_Employers();
        $table = new Companies_Model_Table_Companies();
        $select = $table->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
                ->setIntegrityCheck(false)
                ->joinInner( 'companies_employer', 'companies_company.id = companies_employer.id_company', array('companies_employer.position'))
                ->where( 'companies_employer.id_user = ?', $this->id )
                ->where( 'companies_employer.is_confirmed = 1' )
                ->order( 'companies_company.signup_date DESC' );

        $rows = $table->fetchAll( $select );
        foreach ( $rows as $row ) {
                $data[$row->id] = $row;
        }
        return $data;
    }

    // возвращает список компаний в которых состоит пользователь у которых еще нет своей ленты
    public function getCompaniesHasNoFeed($getCount = false)
    {
        $data = array();
//        $table = new Companies_Model_Table_Employers();
        $table = new Companies_Model_Table_Companies();
        $select = $table->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
                ->setIntegrityCheck(false)
                ->joinInner( 'companies_employer', 'companies_company.id = companies_employer.id_company', null)
                ->joinLeft( 'feed_feeds', 'companies_company.id <> feed_feeds.company_id', null)
                ->where( 'companies_employer.id_user = ?', $this->id );
        //throw new Exception($select);
        $rows = $table->fetchAll( $select );
        foreach ( $rows as $row ) {
                $data[$row->id] = $row;
        }
        return $data;
    }

    // возвращает список лент компаний в которых состоит пользователь
    public function getFeedUserCompanies()
    {
        $data = array();
//        $table = new Companies_Model_Table_Employers();
        $table = new Feed_Model_Table_Feed();
        $select = $table->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
                ->setIntegrityCheck(false)
                ->joinLeft( 'companies_employer', 'feed_feeds.company_id = companies_employer.id_company', null)
                ->where( 'companies_employer.id_user = ?', $this->id );

        //throw new Exception($select);
        $rows = $table->fetchAll( $select );
        foreach ( $rows as $row ) {
                $data[$row->id] = $row;
        }
        return $data;
    }

    // возвращает список лент которые создал пользователь
    public function getUserFeed()
    {
        $data = array();
        $rows = $this->findDependentRowset('Feed_Model_Table_Feed', 'Users', $this->select()->where('company_id is NULL')->order("create_date DESC"));
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }
    
    // возвращает резюме
    public function getResume()
    {
        //$items = array();
        $rows = $this->findDependentRowset('Users_Model_Table_Resumes', 'User');
        foreach ( $rows as $row ) {
            return $row;
        }
        return NULL;
    }    

    public function employment()
    {
        $emp = new Companies_Model_Table_Employers();
        $q = $this->select()->setIntegrityCheck(false)
                        ->from(array("ce" => "companies_employer"), array("ce.position", "ce.id_company"))
                        ->joinInner(array("c" => "companies_company"), "ce.id_company = c.id", array("companyName" => "name"));

        $q->where("ce.id_user = ?", $this->id);

        return $emp->fetchAll($q);
    }

    /**
     * Отметка просмотра страницы
     */
    public function hit()
    {
        $this->getTable()->update(array('hits' => new Zend_Db_Expr("hits + 1")), "id = {$this->id}");
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @return string
     */
    public function getUserName($class='')
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer'); 
        //return '<a class="black line-hov" href="' . $viewRenderer->view->url(array('userId' => $this->id), 'profile') . '">' . $this->username . '</a>';
        return '<a ' . $class . 'href="' . $viewRenderer->view->url(array('userId' => $this->id), 'profile') . '">' . $this->username . '</a>';
    }

    /**
     * @return string
     */
    public function getAvatar($width = 64, $heigth = 64, $link = true, $class ='', $thumb = '')
    {
        if ($this->avatar && !is_null($this->avatar) && trim($this->avatar) != "") {
            if (file_exists(Zend_Registry::get('upload_path')."/user/".$thumb.$this->avatar))
                $avatarImg = $this->avatar;
            else
                // Если запись есть, а файла фактически нет
                $avatarImg = "globallogo.png";
        } else {
            $avatarImg = "globallogo.png";
        }
        
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        
        if ($link)
            $avatar = '<a href="' . $viewRenderer->view->url(array('userId' => $this->id), 'profile') . '"><img ' . $class . 'src="/files/user/' . $thumb . $avatarImg . '" width="' . $width . '" heigth="' . $heigth . '" /></a>';
        else
            $avatar = "<img " . $class . "src='/files/user/" . $thumb . $avatarImg . "' width='" . $width . "' heigth='" . $heigth . "' />";            
        
        return $avatar;
    }
    
    public function getProfileAvatar()
    {
        if ($this->avatar && !is_null($this->avatar) && trim($this->avatar) != "") {
            if (file_exists(Zend_Registry::get('upload_path')."/user/".$this->avatar))
                $avatarImg = $this->avatar;
            else
                // Если запись есть, а файла фактически нет
                $avatarImg = "globallogo.png";
        } else {
            $avatarImg = "globallogo.png";
        }
        $avatar = '<img class="prf-a" src="/files/user/' . $avatarImg . '" width="64" height="64" alt="" />';
        return $avatar;
    }    

    public function getInboxMessages()
    {
        $data = array();
        $rows = $this->findDependentRowset('Users_Model_Table_Messages', 'Inbox', $this->select()->where('id_user_to = ?', $this->id)->where("delete_user_to = 0")->order("id DESC"));
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getOutboxMessages()
    {
        $data = array();
        $rows = $this->findDependentRowset('Users_Model_Table_Messages', 'Outbox', $this->select()->where('id_user_from = ?', $this->id)->where("delete_user_from = 0")->order("id DESC"));
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getFriends($friendType, $count = false)
    {
        $data = array();
        
        if (!$count)
            $rows = $this->findDependentRowset('Users_Model_Table_Friends', 'MyFriends', $this->select()->where('id_user_from = ?', $this->id)->where('status = ?', $friendType)->order('date_request DESC'));
        else
            $rows = $this->findDependentRowset('Users_Model_Table_Friends', 'MyFriends', $this->select()->where('id_user_from = ?', $this->id)->where('status = ?', $friendType)->order('date_request DESC'), array('id'));
            
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }
    
    public function getFriendsRequest()
    {
        $data = array();
        $rows = $this->findDependentRowset('Users_Model_Table_Friends', 'FriendRequest', $this->select()->where('id_user_to = ?', $this->id)->where('status = 0')->order('date_request DESC'));
        //throw new Exception($this->id);
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }

    public function getPhotos()
    {
        $data = array();
        return $data;
    }

    public function getOptions()
    {
        $data = array();
        return $data;
    }

    public function getUnreadMessages()
    {
        $data = array();
        $rows = $this->findDependentRowset('Users_Model_Table_Messages', 'Inbox', $this->select()->where('id_user_to = ?', $this->id)->where('is_read = 0')->order("id DESC"));
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;
    }
    
    public function getFriendRequests()
    {
        $data = array();
        $rows = $this->findDependentRowset('Users_Model_Table_Friends', 'FriendRequest', $this->select()->where('id_user_to = ?', $this->id)->where('status = 0'));
        foreach ($rows as $row) {
            $data[$row->id] = $row;
        }
        return $data;    
    }

        // return true/false
    // есть ли контакты у пользователя
    public function issetContacts()
    {
        if (!$this->www && !$this->skype && !$this->googleplus && !$this->livejournal && !$this->facebook && !$this->twitter && !$this->icq)
            return false;
        else
            return true;
    }

}