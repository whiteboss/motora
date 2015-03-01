<?php

/**
 * 
 * Контроллер компаний
 * 
 */
class Companies_CompanyController extends Zend_Controller_Action {

    public function init() {
        $this->logger = Zend_Registry::get('logger');
    }

    public function indexAction() {
        $this->_forward('profile');
    }
    
    public function generateurlAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(); 
        
        $table = new Companies_Model_Table_Companies();
        
        $companies = $table->getCompanies();
        foreach ($companies as $company) {
            $company->url = mb_strtolower( preg_replace('/\s+/', '-', preg_replace("/[.,:!'´@]/", '', $company->name) ), 'UTF-8');            
            $company->save();
        }        
        
    }        

    /**
     * Perfil de empresa
     * @return void
     */
    public function profileAction()
    {
//        $this->getResponse()
//                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
        
        $this->view->headScript()
                ->appendFile( '/js/mediaelement-and-player.min.js' )
                ->appendFile( '/js/events/walks.js' )
                ;

        $companyId = (int) $this->getRequest()->getParam('companyId');
        
        $company_url = NULL;
        if ($companyId == 0) {
            $filter = new Zend_Filter_StripTags();
            $company_url = $filter->filter($this->_getParam("company_url"));            
        }
        
        if ($companyId != 0 || !is_null($company_url)) {
            $company = $this->_helper->companyProfile($companyId, $company_url);
            // title и description
            $this->view->headTitle()->append($company->name);
            $this->view->headTitle()->append($company->getHeaderType() . ' de Santiago');
            if (!is_null($company->description))
                $this->view->headMeta()->setName('description', $company->description);
            
            $this->view->vcard = $company->getvCard();

//            $table = new Companies_Model_Table_Companies();
//            $this->view->popular_companies = $table->getLugaresPopulares(11);
//            $table = new Post_Model_Table_Post();
//            $this->view->popular_posts = $table->getPosts(null, 0, 10, false);
            
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
            
            //$this->view->partial()->setObjectKey('company');
            
            // АКТИВНОСТЬ
            $table = new Events_Model_Table_Events();                
            $company_events = $table->select()
                    ->setIntegrityCheck(false)
                    //->from(array('e' => 'events_event'), array('id', '("event") as tbl', 'start_date as date'))
                    ->from(array('e' => 'events_event'), array('id', '("event") as tbl', 'date'))
                    ->where('id_company = ? OR id_productor = ?', $company->id)
                    ->where('is_confirmed = 1');
            
            $table = new Events_Model_Table_PhotoReports();                
            $company_reports = $table->select()
                    ->setIntegrityCheck(false)
                    //->from(array('f' => 'events_photoreport'), array('id', '("photoreport") as tbl', 'fe.start_date as date'))
                    ->from(array('f' => 'events_photoreport'), array('id', '("photoreport") as tbl', 'fe.date as date'))
                    ->joinLeft(array('fe' => 'events_event'), 'fe.id = f.event_id', NULL)
                    ->where('fe.id_company = ?', $company->id)
                    ->where('f.is_confirmed = 1');
            
            $table = new Post_Model_Table_Post();
            $company_posts = $table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'post_posts'), array('id', '("post") as tbl', 'date'))                            
                    ->where('company_id = ?', $company->id)
                    ->where( 'status = 1' )
                    ->where( 'rubric_id <> 1' );
            
            $company_videos = $table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'post_posts'), array('id', '("video") as tbl', 'date'))                            
                    ->where('company_id = ?', $company->id)
                    ->where( 'status = 1' )
                    ->where( 'rubric_id = 1' );
            
            $db = $table->getAdapter();
            $main_select = $db->select()->from(
                            $db->select()->union(array($company_events, $company_reports, $company_posts, $company_videos), Zend_Db_Select::SQL_UNION_ALL)
                            )
                            ->order('date DESC')
                            ;
                    
            $items = $db->fetchAll($main_select);
            $this->view->items = $items;

        }
    }
    
    public function visitorsAction() {
//        $this->getResponse()
//                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
//        $this->view->headScript()
//                ;

        $companyId = (int) $this->getRequest()->getParam('companyId');
        $company_url = NULL;
        if ($companyId == 0) {
            $filter = new Zend_Filter_StripTags();
            $company_url = $filter->filter($this->_getParam("company_url"));            
        }
        
        if ($companyId != 0 || !is_null($company_url)) {
            $company = $this->_helper->companyProfile($companyId, $company_url);
            
//            $table = new Companies_Model_Table_Companies();            
//            $this->view->popular_companies = $table->getLugaresPopulares(11);  
//            $table = new Post_Model_Table_Post();
//            $this->view->popular_posts = $table->getPosts(null, 0, 10, false);
            
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
            
            // title и description
            $this->view->headTitle()->append('Visitantes');
            $this->view->headTitle()->append($company->name);
            $this->view->headTitle()->append($company->getHeaderType() . ' de Santiago');
            if (!is_null($company->description))
                $this->view->headMeta()->setName('description', $company->description);
            
            $items = $company->getVisitors();
            $this->view->items = $items;
        }
    }    
    
    public function eventsAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
        $this->view->headScript()
                ->appendFile( '/js/events/walks.js' );

        $companyId = (int) $this->getRequest()->getParam('companyId');
        $company_url = NULL;
        if ($companyId == 0) {
            $filter = new Zend_Filter_StripTags();
            $company_url = $filter->filter($this->_getParam("company_url"));            
        }
        
        if ($companyId != 0 || !is_null($company_url)) {
            $company = $this->_helper->companyProfile($companyId, $company_url);
            
//            $table = new Companies_Model_Table_Companies();            
//            $this->view->popular_companies = $table->getLugaresPopulares(11);
//            $table = new Post_Model_Table_Post();
//            $this->view->popular_posts = $table->getPosts(null, 0, 10, false);
            
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
            
            // title и description
            $this->view->headTitle()->append('Eventos');
            $this->view->headTitle()->append($company->name);
            $this->view->headTitle()->append($company->getHeaderType() . ' de Santiago');
            if (!is_null($company->description))
                $this->view->headMeta()->setName('description', $company->description);
            
            $this->view->items = $company->getEvents();            
            $this->view->past_items = $company->getPastEvents();
        }
    }
    
    public function lentsAction() {
        
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->_helper->initInterface();
//        $this->view->headScript()
//                //->appendFile( '/js/events/walks.js' )
//                ;

        $companyId = (int) $this->_getParam('companyId');

        $company_url = NULL;
        if ($companyId == 0) {
            $filter = new Zend_Filter_StripTags();
            $company_url = $filter->filter($this->_getParam("company_url"));            
        }
        
        if ($companyId != 0 || !is_null($company_url)) {
            $company = $this->_helper->companyProfile($companyId, $company_url);
            
//            $table = new Companies_Model_Table_Companies();            
//            $this->view->popular_companies = $table->getLugaresPopulares(11);
//            $table = new Post_Model_Table_Post();
//            $this->view->popular_posts = $table->getPosts(null, 0, 10, false);
            
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
            
            $this->view->headTitle()->append('Blog');
            $this->view->headTitle()->append($company->name);
            $this->view->headTitle()->append($company->getHeaderType() . ' de Santiago');
            //$this->view->headTitle()->append('Publicaciones');
            if (!is_null($company->description))
                $this->view->headMeta()->setName( 'description', $company->description );   
            
            
            $items = $company->getListPosts();
            $this->view->items = $items;
            
        }        
        
    }
    
    public function photosAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
//        $this->view->headScript()
//                //->appendFile( '/js/events/walks.js' )
//                ;

        $companyId = (int) $this->getRequest()->getParam('companyId');
        $company_url = NULL;
        if ($companyId == 0) {
            $filter = new Zend_Filter_StripTags();
            $company_url = $filter->filter($this->_getParam("company_url"));            
        }
        
        if ($companyId != 0 || !is_null($company_url)) {
            $company = $this->_helper->companyProfile($companyId, $company_url);
            
//            $table = new Companies_Model_Table_Companies();            
//            $this->view->popular_companies = $table->getLugaresPopulares(11); 
//            $table = new Post_Model_Table_Post();
//            $this->view->popular_posts = $table->getPosts(null, 0, 10, false);
            
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
            
            // title и description
            $this->view->headTitle()->append('Fotoreportajes');
            $this->view->headTitle()->append($company->name);
            $this->view->headTitle()->append($company->getHeaderType() . ' de Santiago');
            if (!is_null($company->description))
                $this->view->headMeta()->setName('description', $company->description);
            
            $items = $company->getEventGalleries();
            $this->view->items = $items;
        }
    } 
    
    public function musicAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
//        $this->view->headScript()
//                //->appendFile( '/js/events/walks.js' )
//                ;

        $companyId = (int) $this->getRequest()->getParam('companyId');
        if (!empty($companyId)) {
            $company = $this->_helper->companyProfile($companyId);
            
//            $table = new Companies_Model_Table_Companies();            
//            $this->view->popular_companies = $table->getLugaresPopulares(11);
//            $table = new Post_Model_Table_Post();
//            $this->view->popular_posts = $table->getPosts(null, 0, 10, false);
            
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
            
            // title и description
            $this->view->headTitle()->append($company->name);
            if (!is_null($company->description))
                $this->view->headMeta()->setName('description', $company->description);
            
            //$items = $company->getEventGalleries();
            //$this->view->items = $items;
        }
    } 
    
    public function videoAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
        $this->view->headScript()
                ->appendFile( '/js/mediaelement-and-player.min.js' )
                //->appendFile( '/js/events/walks.js' )
                ;

        $companyId = (int) $this->getRequest()->getParam('companyId');
        $company_url = NULL;
        if ($companyId == 0) {
            $filter = new Zend_Filter_StripTags();
            $company_url = $filter->filter($this->_getParam("company_url"));            
        }
        
        if ($companyId != 0 || !is_null($company_url)) {
            $company = $this->_helper->companyProfile($companyId, $company_url);
            
//            $table = new Companies_Model_Table_Companies();            
//            $this->view->popular_companies = $table->getLugaresPopulares(11);
//            $table = new Post_Model_Table_Post();
//            $this->view->popular_posts = $table->getPosts(null, 0, 10, false);
            
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
            
            // title и description
            $this->view->headTitle()->append('Video');
            $this->view->headTitle()->append($company->name);
            $this->view->headTitle()->append($company->getHeaderType() . ' de Santiago');
            if (!is_null($company->description))
                $this->view->headMeta()->setName('description', $company->description);
            
            $items = $company->getListVideos();
            $this->view->items = $items;
        }
    }    

    public function mapAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->_helper->initInterface();
        $companyId = (int) $this->getRequest()->getParam('companyId');
        if (!empty($companyId)) {
            $company = $this->_helper->companyProfile($companyId);
            $this->view->headTitle()->append('Компания ' . $company->name);
            $this->view->headTitle()->append('Местонахождение');
            if (!is_null($company->description))
                $this->view->headMeta()->setName('description', $company->description);

            $this->_helper->companyProfile($companyId);
            $this->view->active = 'map';
            $this->view->partial()->setObjectKey('company');
        }
    }

    /**
     * Список компаний
     * @return void
     */
    public function filterAction() {
        $this->_helper->layout->disableLayout();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $request = $this->getRequest();

        // отфильтровать от всяких пидарасов
        $filter = new Zend_Filter_StripTags();
        $search_str = $filter->filter((string) $request->getParam('search_str'));

        $table = new Companies_Model_Table_Companies();
        $items = $table->FilterByName($search_str);

        $this->view->companies = $items;
    }

    protected function sendEmail($company) {
        $link = $this->view->url(array('companyId' => $company->id, 'action' => 'confirm'), 'company');
        $link_view = $this->view->url(array('companyId' => $company->id, 'action' => 'profile'), 'company');
        
        $mal = new Zend_Mail('UTF-8');
        //Уважаемый '.$user->lastname.' '.$user->firstname.', ссылка для подтверждения: <a href="' . $link .  '">' . $link . '</a>'
        $mal->setBodyHtml('
            <table cellspacing="0" cellpadding="0" border="0" width="100%" style="background: url(http://www.qlick.cl/sprites/qmail/line.gif) repeat-x top left;">
                <tr>
                    <td width="5%">
                    </td>
                    <td width="60%">
                        <h1 style="font-family: Calibri, Arial; font-size: 24px; text-transform: uppercase; padding: 30px 0 15px 0; border-bottom: 1px #000000 solid; width: 80%;">Qlick.cl — Подтверждение регистрации новой компании</h1>
                        <p style="font-family: Arial; font-size: 12px; padding: 5px 0 12px 0; line-height: 150%;">
                            Добавилась компания ' . $company->name . ', чтобы активировать ее, пожалуйста, пройдите по ссылке:
                            <a style="color: #284C6A; text-decoration: none;" href="' . $link . '">' . $link . '</a>
                            чтобы посмотреть компанию, пройдите по ссылке:
                            <a style="color: #284C6A; text-decoration: none;" href="' . $link_view . '">' . $link_view . '</a>                                    
                        </p>
                        <table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top: 50px;">
                            <tr>
                                <td valign="top">
                                    <a href="http://www.qlick.cl"><img src="http://www.qlick.cl/sprites/qmail/logo.gif" width="79" height="21" alt="Qlick.cl" border="0" /></a>
                                </td>
                                <td>
                                    <p style="margin: 0 0 0 50px; color: #7D7D7D; font-family: Arial; font-size: 11px; width: 100%; line-height: 150%;">
                                        Данное письмо отправлено автоматической системой оповещений. Не отвечайте на это сообщение. Если у вас возникли вопросы по работе служб сайта Qlick.cl — пожалуйста, обратитесь к нашей технической поддержке по адресу <a style="color: #7D7D7D; text-decoration: none;" href="mailto:support@qlick.cl">support@qlick.cl</a>.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="35%">
                    </td>
                </tr>
            </table>
                ')
                ->setFrom('no-reply@qlick.cl', 'Qlick.cl')
                ->addTo('mailbox@qlick.cl')
                ->setSubject('Confirmación de empresa en Qlick.cl')
                ->send();
    }
    
    protected function sendAcceptEmail($user)
    {

            $who = $this->view->getUserByIdentity();
            $mal = new Zend_Mail( 'UTF-8' );
            //Уважаемый '.$user->lastname.' '.$user->firstname.', ссылка для подтверждения: <a href="' . $link .  '">' . $link . '</a>'
            $mal->setBodyHtml('
                    Hola, '.$user->username.'!
                    <br /><br />
		    Ваша компания подтверждена
                    <br /><br />
		    Atentamente, Qlick.cl<br />
		    <a href="http://www.qlick.cl/">http://qlick.cl<br />
		    ')
                    ->setFrom('no-reply@qlick.cl', 'Qlick.cl')
                    ->addTo($user->email)
                    ->setSubject('Confirmación de empresa en Qlick.cl')
                    ->send();

    }     

    /**
     * Подтверждение
     * @return void
     */
    public function confirmAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

        $companyId = (int) $this->getRequest()->getParam('companyId');

        if (empty($companyId)) {
            //throw new Exception("Идентификатор не задан");
            $this->_redirect($this->view->url(array(), 'companies'));
        }

        if ($this->view->isGodOfProject()) {
            $table = new Companies_Model_Table_Companies();
            $rows = $table->find($companyId);
            if (count($rows) > 0) {
                $company = $rows->current();
                $company->is_confirmed = 1;
                $company->save();
                
                // уведомление автору                
                $user = $company->getOwner(); 
                if (!is_null($user->email)) $this->sendAcceptEmail($user);
                
                $this->_redirect($this->view->url(array('companyId' => $company->id, 'action' => 'profile'), 'company'));
                return;
            }
        } else {
            $this->logger->log('Попытка активировать компанию ID' . $companyId, Zend_Log::ALERT, $this->getRequest());
            $this->_redirect($this->view->url(array(), 'companies'));
            return;
        }
    }

    /**
     * Добавление новой компании
     * @return void
     */
    public function newAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
        
        $this->view->headScript()
                ->appendFile( '/js/companies/newcompany.js' )
                ->appendFile( '/js/url.js' )
                ->appendFile( '/js/charCount.js' );
        
//        $table = new Companies_Model_Table_Companies();            
//        $this->view->popular_companies = $table->getLugaresPopulares(11);
//        $table = new Post_Model_Table_Post();
//        $this->view->popular_posts = $table->getPosts(null, 0, 10, false);

        $request = $this->getRequest();
        $identity = Zend_Auth::getInstance();

        $form = new Companies_Form_NewCompany();
        $form->setTemplate('/forms/new_company');
        $form->getElement('url')->addValidator('Db_NoRecordExists', false, array('table' => 'companies_company', 'field' => 'url'));
        
        if ($identity->hasIdentity()) {
            if ($request->isPost()) {

                $data = $request->getPost();
                if ($form->isValid($data)) {
                    
                    $data = $form->getValues();
                    
                    $data['description'] = nl2br($data['description']);
                    $data['path'] = nl2br($data['path']);

                    if ($data['agree'] == 1) {
                        //                    if (!isset($data['type'])) {
                        //                        $data['industry'] = $data['activity_type'];
                        //                    } else {
                        //                        $company_types = Companies_Model_Company::getTypes();
                        //                        if (!array_key_exists($data['type'], $company_types))
                        //                            throw new Exception('Ошибка валидации типа компании');
                        //                    }
                        //                    try

                        $table = new Companies_Model_Table_Companies();
                        
                        if ($this->view->isGodOfProject()) $data['is_confirmed'] = 1;
                        
                        $company = $table->createRow($data);
                        $id = $company->save();

//                        // добавить владельца
                        $user_id = $identity->getIdentity()->id;
//                        if ($data['representative'] == 1)
//                            $position = $data['position'];
//                        else
//                            $position = '';

                        $table = new Companies_Model_Table_Employers();
                        $row = $table->createRow(array(
                            'id_company' => $id,
                            'id_user' => $user_id,
                            'is_owner' => 1,
                            'position' => '',
                            'is_representative' => 1,
                            'is_confirmed' => true
                                ));
                        $row->save();
                        
//                        // создадим ленту компании
//                        if (!is_null($company->description))
//                            $description = $company->description;
//                        else
//                            $description = 'Blog de la empresa ' . $company->name;
//                            
//                        $table = new Feed_Model_Table_Feed();
//                        $row = $table->createRow(array(
//                            'name' => 'Blog de la empresa ' . $company->name,
//                            'author_id' => $user_id,
//                            'company_id' => $id,   
//                            'description' => $description,
//                            'rubric_id' => 13
//                        ));
//                        $row->save();                        

                        // отправим напоминание о том, чтобы проверить компанию
                        if (!$this->view->isGodOfProject()) $this->sendEmail($company);
                        $this->_helper->FlashMessenger(array('system_ok' => 'Lugar agregó'));
                        $this->_redirect($company->getUrl());
                    } else {
                        $this->_helper->FlashMessenger(array('system_message' => 'Debe aceptar las condiciones'));
                        //$this->_redirect($this->view->url(array(''), 'createcompany'), $data);
                        $form->setDefaults($data);
                    }
                } else {
                    $form->setDefaults($data);
                    //$form->activity_type->setMultiOptions($table->getAllSubs($data['activity_sphere']));
                }
            } else {
                
            }
        } else {
            $this->_helper->FlashMessenger(array('system_message' => 'Para crear una empresa tiene que ser autorizada'));
            $this->_redirect($this->view->url(array(''), 'signin'));
        }

        //throw new Exception();
        $this->view->addform = $form;
    }

    /**
     * Редактирование главной информации о компании
     * @return void
     */
    public function editAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

        $this->_helper->initInterface();

        $this->view->headScript()
                ->appendFile('/resources/uploadify/jquery.uploadify.js')
                ->appendFile('/js/photoUploader.js')
                ->appendFile('/js/avatarUploader.js')
                ->appendFile('/js/companies/editcompany.js')
                ->appendFile('/js/url.js')
                ->appendFile('/js/charCount.js')
                ->appendFile('/js/jcrop/jquery.Jcrop.min.js');

        $this->view->headLink()
                ->appendStylesheet('/resources/uploadify/uploadify.css')
                ->appendStylesheet('/css/jcrop/jquery.Jcrop.css');

        $request = $this->getRequest();
        //$id = (int) $request->getParam('companyId');
        $companyId = (int) $request->getParam('companyId');
        $company = $this->_helper->companyProfile($companyId);
        
//        $table = new Companies_Model_Table_Companies();            
//        $this->view->popular_companies = $table->getLugaresPopulares(11);
//        $table = new Post_Model_Table_Post();
//        $this->view->popular_posts = $table->getPosts(null, 0, 10, false);
        
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
        
//        $table = new Companies_Model_Table_Companies();
//        $rows = $table->find($id);
//        if (count($rows) > 0) {
//            $row = $rows->current();
//        } else {
//            //throw new Exception('Desconocido identificador companyId' . $id);
//            
//        }
        
        // Только владелец может редактировать компанию
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $owner = $company->getOwner();
            if (!$owner && !$this->view->isGodOfProject()) {
                $this->_helper->FlashMessenger(array('system_error' => 'Операция запрещена'));
                //throw new Exception('Операция запрещена');
                $this->_redirect($company->getUrl());
            }
        } else {
            $this->_helper->FlashMessenger(array('system_message' => 'Para editar una compañía debe ser autorizada'));
            $this->_redirect( $this->view->url(array(''), 'signin') );
        }
        
        $this->view->headTitle()->append($company->name);
        $this->view->headTitle()->append('Editar perfil');

        $this->view->company = $company;
        $form = $this->view->editform = new Companies_Form_EditCompany(array('type' => $company->type));
        $form->setTemplate('/forms/edit_company');
        if ($request->isPost()) {
            $form->addConditionalValidators($request->getPost());
            $data = $request->getPost();
            if ($form->isValid($data)) {

                $data = $form->getValues();
                
                $data['description'] = nl2br($data['description']);
                $data['path'] = nl2br($data['path']);
                $data['regime'] = nl2br($data['regime']);

                if ($data['photos'] && $data['photos'] != $company->photos) {
                    $files = json_decode($data['photos'], true); // фото
                    $photos = $company->getPhotos();
                    if (!is_null($photos))
                        $delete_photos = array_diff($photos, $files); // найдем разницу
                    else
                        $delete_photos = array();

                    $path = Zend_Registry::get('upload_path');

                    $bage = false;
                    foreach ($files as $key => $file) {
                        if (!is_null($file) && $file != '') {
                            if (is_file($path . '/tmp/' . $file)) {

                                if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/company/163_108_' . $file, 108, 163, true)) {
                                    throw new Exception('Error al editar imágenes');
                                }
                                
                                if (!$bage) {
                                    if (!$this->_helper->uploader->resizeToPath($path . '/tmp/' . $file, $path . '/company/400_' . $file, 400, true, true)) {
                                        throw new Exception('Error al editar imágenes');
                                    }
                                    $bage = true;
                                }
                                
                                $size = getimagesize($path . '/tmp/' . $file);
                                if ($size[0] > $size[1]) { // width > heigth
                                    if ($size[0] > 1100) {            
                                        if ( ! $this->_helper->uploader->resizeToPath($path . '/tmp/' . $file, $path . '/company/' . $file, 1100, true, true, 90)) {
                                            throw new Exception( 'Error al editar imágenes' );
                                        }
                                    } else {
                                        @copy($path . '/tmp/' . $file, $path . '/company/' . $file);
                                    }
                                } else {
                                    if ($size[1] > 700) {            
                                        if ( ! $this->_helper->uploader->resizeToPath( $path . '/tmp/' . $file, $path . '/company/' . $file, 700, false, true, 90)) {
                                            throw new Exception( 'Error al editar imágenes' );
                                        }
                                    } else {
                                        @copy($path . '/tmp/' . $file, $path . '/company/' . $file);
                                    }
                                }                                
                                // собственно переносим саму фотку
                                @unlink($path . '/tmp/' . $file);
                            } else {
                                unset($files[$key]);
                            }
                        } else {
                            unset($files[$key]);
                        }
                    }

                    if (!is_null($photos)) {
                        $a_photos = array_values($files + array_diff($photos, $delete_photos));
                    } else {
                        $a_photos = array_values($files);  // новые фотки                        
                    }
                    
                    if (count($a_photos) > 0)
                        $data['photos'] = json_encode($a_photos);
                    else
                        $data['photos'] = new Zend_Db_Expr('NULL');
                        
                    // раз все загрузили, удалим старье
                    foreach ($delete_photos as $photo) {
                        if (is_file($path . '/company/' . $photo)) unlink($path . '/company/' . $photo);
                        if (is_file($path . '/company/163_108_' . $photo)) unlink($path . '/company/163_108_' . $photo);
                        if (is_file($path . '/company/400_' . $photo)) unlink($path . '/company/400_' . $photo);
                    }
                } else {
                    unset($data['photos']);
                }

                // Аватар
                if ($data['photo'] && $data['photo'] != $company->photo) {
                    //$mainImage = json_decode($data['photo']);

                    //if ($avatar !== $company->photo) {
                        //$data['photo'] = $avatar;
                        $path = Zend_Registry::get('upload_path');
                        
                        if (is_file($path . '/tmp/' . $data['photo'])) {
                            
                            // определим размер
                            $size = getimagesize($path . '/tmp/' . $data['photo']);
                            // если не выбрали область выделения
                            if ($data['w'] == 0 && $data['h'] == 0) {
                                $data['w'] = 600;
                                $aspect = round($size[1] / $size[0], 3);
                                $data['h'] = $size[1] + ((600 - $size[0]) * $aspect);
                                $data['crop_x'] = 0;
                                $data['crop_y'] = 0;    
                            }

                            $koef = $size[0] / 600;
                            $aspect = round($size[1] / $size[0], 3);
                            $koef_h = $size[1] / ($size[1] + ((600 - $size[0]) * $aspect));
                            $data['w'] = floor($data['w'] * $koef);
                            $data['h'] = floor($data['h'] * $koef);
                            $data['crop_x'] = floor($data['crop_x'] * $koef);
                            $data['crop_y'] = floor($data['crop_y'] * $koef_h);
                            
                            if ($data['w'] > $data['h']) {
                                
                                if ($size[0] > 280) {
                                    if (!$this->_helper->uploader->resizeAvatarToPath($path . '/tmp/' . $data['photo'], $path . '/company/avatars/' . $data['photo'], 280, true, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                                        throw new Exception('Error al editar imágenes');
                                    }
                                } else {
                                    copy($path . '/tmp/' . $data['photo'], $path . '/company/avatars/' . $data['photo']);
                                }
                                
                                if ($size[0] > 168) {
                                    if (!$this->_helper->uploader->resizeAvatarToPath($path . '/tmp/' . $data['photo'], $path . '/company/avatars/mid_' . $data['photo'], 168, true, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                                        throw new Exception('Error al editar imágenes');
                                    }
                                } else {
                                    copy($path . '/tmp/' . $data['photo'], $path . '/company/avatars/mid_' . $data['photo']);
                                }
                                
                                if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $data['photo'], $path . '/company/avatars/b_kv_' . $data['photo'], 119, 119, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'], false)) {
                                    throw new Exception('Error al editar imágenes');
                                }

                                if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $data['photo'], $path . '/company/avatars/m_kv_' . $data['photo'], 35, 35, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'], false)) {
                                    throw new Exception('Error al editar imágenes');
                                }                                    
                                
                            } else {
                                
                                if ($size[1] > 250) {
                                    if (!$this->_helper->uploader->resizeAvatarToPath($path . '/tmp/' . $data['photo'], $path . '/company/avatars/' . $data['photo'], 250, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                                        throw new Exception('Error al editar imágenes');
                                    }
                                } else {
                                    copy($path . '/tmp/' . $data['photo'], $path . '/company/avatars/' . $data['photo']);
                                }
                                    
                                if ($size[1] > 122) {
                                    if (!$this->_helper->uploader->resizeAvatarToPath($path . '/tmp/' . $data['photo'], $path . '/company/avatars/mid_' . $data['photo'], 122, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                                        throw new Exception('Error al editar imágenes');
                                    }
                                } else {
                                    copy($path . '/tmp/' . $data['photo'], $path . '/company/avatars/mid_' . $data['photo']);                                    
                                }
                                
                                if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $data['photo'], $path . '/company/avatars/b_kv_' . $data['photo'], 119, 119, true, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                                    throw new Exception('Error al editar imágenes');
                                }

                                if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $data['photo'], $path . '/company/avatars/m_kv_' . $data['photo'], 35, 35, true, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'], false)) {
                                    throw new Exception('Error al editar imágenes');
                                }
                                
                            }
                            
                            //copy($path . '/tmp/' . $data['photo'], $path . '/company/avatars/b_kv_' . $data['photo']);
                            
                            
                            unlink($path . '/tmp/' . $data['photo']);
                            
                            //rename($path . '/tmp/' . $data['photo'], $path . '/company/avatars/' . $data['photo']);                            
                            //$this->_helper->uploader->resizeAvatar($path . '/company/avatars/' . $data['photo'], 135, $data['w'], $data['h'], $data['crop_x'], $data['crop_y']);
                            
                            if (!is_null($company->photo)) {
                                @unlink($path . '/company/avatars/' . $company->photo);
                                @unlink($path . '/company/avatars/mid_' . $company->photo);
                                @unlink($path . '/company/avatars/b_kv_' . $company->photo);
                                @unlink($path . '/company/avatars/m_kv_' . $company->photo);
                            }
                            
                        }
                    //}
                }

                $data['phone_add'] = $this->_helper->clearJson($data['phone_add']);
                $data['email'] = $this->_helper->clearJson($data['email']);
                
                //if ($data['delivery_available'] == 0) $data['delivery_phone'] = NULL;
                
                $company->setFromArray($data);
                $id = $company->save();
                // очистим память
                unset($data);
                unset($delete_photos);
                unset($photos);
                unset($files);

                $this->_helper->FlashMessenger(array('system_message' => 'Información sobre la compañía cambió'));
                $this->_redirect($company->getUrl());
            } else {
                $data['photos'] = json_encode(json_decode($data['photos'], true));
                $form->setDefaults($data);
                return;
            }
        }

//        if (!is_null($company->delivery_phone) && $company->delivery_phone != "") {
//            $form->delivery_available->setChecked(true);
//        }
        $form->setDefaults($company->toArray());
    }

    /**
     * Ajax валидация
     * @return void
     */
    public function validateAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        $field = $request->getParam('field');
        $value = $request->getParam('value');
        if ($field == 'email') {
            $validator = new Zend_Validate_EmailAddress();
        } elseif ($field == 'digits') {
            $validator = new Zend_Validate_Digits();
        } elseif ($field == 'notempty') {
            $validator = new Zend_Validate_NotEmpty();
        } elseif ($field == 'phone') {
            $validator = new Qlick_Validate_Phone();
        } else {
            throw new Exception('Неизвестный тип поля ' . $field);
        }
        if ($validator->isValid($value)) {
            $this->getResponse()->setBody(
                    $this->view->json(array('success' => true, 'value' => $value))
            );
        } else {
            $messages = $validator->getMessages();
            $this->getResponse()->setBody(
                    $this->view->json(array('success' => false, 'message' => array_pop($messages)))
            );
        }
    }

    /**
     * Удаление
     * @return void
     */
    public function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

//        $id = (int) $this->getRequest()->getParam('companyId');
//        if (empty($id)) {
//            throw new Exception("Идентификатор не задан");
//        }
        $request = $this->getRequest();
        //$id = (int) $request->getParam('companyId');
        $companyId = (int) $request->getParam('companyId');
        $company = $this->_helper->companyProfile($companyId);
        
        $owner = $company->getOwner(); 
        if (!is_null($owner) || $this->view->isGodOfProject()) {            
            $identity = $this->view->getUserByIdentity();            
            if ((!is_null($identity) && $owner->id == $identity->id && $company->is_confirmed == 0) || $this->view->isGodOfProject()) {
                // удалим фотки
                $path = Zend_Registry::get('upload_path');
                $photo = $company->photo;
                if (is_file($path . '/company/avatars/' . $photo)) unlink($path . '/company/avatars/' . $photo);
                if (is_file($path . '/company/avatars/mid_' . $photo)) unlink($path . '/company/avatars/mid_' . $photo);
                if (is_file($path . '/company/avatars/b_kv_' . $photo)) unlink($path . '/company/avatars/b_kv_' . $photo);
                if (is_file($path . '/company/avatars/m_kv_' . $photo)) unlink($path . '/company/avatars/m_kv_' . $photo);
                $photos = $company->getPhotos();
                foreach ($photos as $photo) {
                    if (is_file($path . '/company/' . $photo)) unlink($path . '/company/' . $photo);
                    if (is_file($path . '/company/163_108_' . $photo)) unlink($path . '/company/163_108_' . $photo);
                    if (is_file($path . '/company/400_' . $photo)) unlink($path . '/company/400_' . $photo);
                }                
                $company->delete();
                $this->_helper->FlashMessenger(array('system_message' => 'La empresa eliminó'));
                $this->_redirect($this->view->url(array(''), 'companies'));                
            }
        }
        
        $this->_redirect($this->view->url(array(''), 'companies'));

    }

    /**
     * Элемент формы мультичекбокс со списком типов деятельности для заданной сферы
     * @return void
     */
    public function activitytypeAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');

        $sphere_id = (int) $this->getRequest()->getParam('sphere');

        $table = new Companies_Model_Table_Spheres();

        $element = new Zend_Form_Element_Multiselect('activity_type', 'Tipos de la actividad');
        $element->setLabel('Escoja el tipo de compañía');
        if (!empty($sphere_id)) {
            $element->setMultiOptions($table->getAllSubs($sphere_id));
            $element->setDecorators(array(array('ViewScript', array(
                'viewScript' => 'chosen_nulledmultiselect.phtml'
            ))));
        }
        $this->getResponse()->setBody(
                $element->render()
        );
    }

    /**
     * Нравиться
     * @return void
     */
    public function likeAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $request = $this->getRequest();
        // Company
        $company_id = (int) $request->getParam('companyId');
        if (empty($company_id)) {
            throw new Exception("El ID de Desconocido");
        }
        // User
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (is_null($identity)) {
            throw new Exception('Iniciar la sesión!');
        }
        $user_id = $identity->id;
        $table = new Companies_Model_Table_Likes();
        $like = $table->fetchRow($table->select()
                        ->where('id_company = ?', $company_id)
                        ->where('id_user = ?', $user_id)
        );
        if ($like) {
            $like->delete();
            $result = 0;
        } else {
            $like = $table->createRow();
            $like->id_company = $company_id;
            $like->id_user = $identity->id;
            $result = $like->save();
        }
        $this->getResponse()->setBody(
                $result
        );
    }

    public function uploadAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $name = $this->_helper->uploader(Zend_Registry::get('upload_path') . "/tmp");
        $path = Zend_Registry::get('upload_path') . "/tmp/$name";
        if (!is_file($path)) {
            throw new Exception('Файл не загружен');
        }
        

        $this->getResponse()->setBody($name);
    }

    public function uploadavatarAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $name = $this->_helper->uploader(Zend_Registry::get('upload_path') . "/tmp");
        $path = Zend_Registry::get('upload_path') . "/tmp/$name";
        if (!is_file($path)) {
            throw new Exception('Файл не загружен');
        }
        
        $size = getimagesize($path);
        if ($size[0] < 90) return false; ;
        
        if ($size[0] > 600) {
            if (!$this->_helper->uploader->resize($path, 600)) {
                throw new Exception('Ошибка при уменьшении изображения');
            }
            $size = getimagesize($path);
        }
            
        $data = array();
        $data['name'] = $name;
        if ($size[0] > 280 && $size[1] > 250) {
            $data['width'] = floor(280 / ($size[0] / 600));
            $aspect = round($size[1] / $size[0], 3);
            $data['height'] = floor(250 / ($size[1] / $size[1] + ((600 - $size[0]) * $aspect)) );
        } else {
            $data['width'] = 280;
            $data['height'] = 250;
        }

        $this->getResponse()->setBody( json_encode($data) );
    }

    public function contactsAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $request = $this->getRequest();

        $company_id = (int) $request->getParam('companyId');
        if (empty($company_id)) {
            throw new Exception("Идентификатор компании не задан");
        }

        $table = new Companies_Model_Table_Companies();
        $items = $table->find($company_id);
        if (count($items) > 0) {
            $company = $items->current();
            $contacts = array();
            $contacts['address'] = $company->address;
            $contacts['phone'] = $company->phone;
            $contacts['site'] = $company->site;
            $this->getResponse()->setBody(json_encode($contacts));
        } else {
            $this->getResponse()->setBody(NULL);
        }
    }

}