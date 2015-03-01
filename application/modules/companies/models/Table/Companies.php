<?php
/**
 *
 * Интерфейс доступа к таблице данных компаний
 *
 */

class Companies_Model_Table_Companies extends Zend_Db_Table_Abstract {
    
    protected $_name = 'companies_company';
    protected $_rowClass = 'Companies_Model_Company';

    public function getCompanies($from = 0, $limit = 0, $sort = 1)
    {
        $data = array();
        $select = $this->select()
                ->from($this, array('id', 'url', 'name', 'type', 'photo'))
                ->setIntegrityCheck(false)
                //->joinLeft( 'companies_likes', 'companies_likes.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_likes` WHERE `companies_likes`.id_company = `companies_company`.id) AS company_like') )                
                ->where('is_confirmed = 1')
                ->group('id')
                ;
        
        if ($sort != 1) {
            $select->order(array('name ASC', 'signup_date DESC'));
        } else {
            $select->order(array('signup_date DESC'));
        }
        
        if ($from > 0) {            
            $select->limit(Companies_Model_Company::$company_per_lazypage, $from);
        } elseif ($limit > 0) {
            $select->limit($limit);
        }
        
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $data[ $row->id ] = $row;
        }
        return $data;
    }
    
    public function getCompanyByUrl($url)
    {

        $qs = $this->select()->setIntegrityCheck(true)->from(array('c' => $this->_name));

        $qs->where('c.url = ?', $url)->limit(1)->order('signup_date DESC');

        $result = $this->fetchRow($qs);
        
        if ($result)        
            return $result;
        else
            return NULL;
    } 
    
    public function getListByType( array $type, $from = 0, $limit = 0 )
    {
            $companies = array();
            if ( empty($type) ) return;
            
            $type_keys = array();
            foreach ($type as $v) {
                $type_keys[] = array_search($v, Companies_Model_Company::$_types);    
            }
            
            $select = $this->select()
                    ->from($this, array('id', 'url', 'photo', 'name', 'type', 'address', 'description', 'photos'))
                    ->setIntegrityCheck(false)
                    //->joinLeft( 'companies_likes', 'companies_likes.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_likes` WHERE `companies_likes`.id_company = `companies_company`.id) AS company_like') )
                    ->where( 'type in (?)', $type_keys )
                    ->where( 'is_confirmed = 1' )
                    ->group( 'id' )
                    ->order( array('signup_date DESC', 'name ASC') ); 
            
            if ($from > 0) {            
                $select->limit(Companies_Model_Company::$company_per_lazypage, $from);
            } elseif ($limit > 0) {
                $select->limit($limit);
            }
            
            //throw new Exception($select);

            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                    $companies[ $row->id ] = $row;
            }
            return $companies;
    }
    
    public function getFoodCompanies( $limit = 0 )
    {
            $companies = array();            
            $select = $this->select()
                    ->from($this, array('id', 'photo', 'name', 'type', 'address', 'description', 'photos', 'la_cuenta'))
                    ->setIntegrityCheck(false)
                    ->joinLeft( 'companies_likes', 'companies_likes.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_likes` WHERE `companies_likes`.id_company = `companies_company`.id) AS company_like') )
                    ->joinLeft( 'orama_oramas', 'orama_oramas.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `orama_oramas` WHERE `orama_oramas`.id_company = `companies_company`.id AND `orama_oramas`.id_category = 2) AS orama_count') )
                    ->where( 'companies_company.type in (?)', array_keys(Companies_Model_Company::$food_types) )
                    ->where( 'photos IS NOT NULL' )
                    ->where( 'is_confirmed = 1' )
                    ->group( 'id' )
                    ->order( array('orama_count DESC', 'company_like DESC', 'name ASC') ); 
            //throw new Exception($select);
            if ($limit > 0)
                $select->limit($limit);

            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                    $companies[ $row->id ] = $row;
            }
            return $companies;
    }
    
    public function getActiveRecrutCompanies( $limit = 0 )
    {
            $companies = array();
            $select = $this->select()
                    ->from($this, array('id', 'photo', 'name', 'type', 'description'))
                    ->setIntegrityCheck(false)
                    ->joinLeft( 'companies_likes', 'companies_likes.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_likes` WHERE `companies_likes`.id_company = `companies_company`.id) AS company_like') )
                    ->joinLeft( 'companies_vacancy', 'companies_vacancy.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_vacancy` WHERE `companies_vacancy`.id_company = `companies_company`.id) AS vacancies') )
                    ->joinLeft( 'companies_resume', 'companies_resume.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_resume` WHERE `companies_resume`.id_company = `companies_company`.id) AS resumes') )
                    ->where( 'is_confirmed = 1' )
                    ->where( 'companies_company.type = "recr"' )
                    ->group( 'id' )
                    ->order( array('vacancies DESC', 'company_like DESC', 'name ASC') ); 
            //throw new Exception($select);
            if ($limit > 0)
                $select->limit($limit);

            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                    $companies[ $row->id ] = $row;
            }
            return $companies;
    }    
    
//    public function getActiveTourCompanies( $limit = 0 )
//    {
//            $companies = array();
//            $select = $this->select()
//                    ->from($this, array('id', 'photo', 'name', 'type', 'description'))
//                    ->setIntegrityCheck(false)
//                    ->joinLeft( 'companies_likes', 'companies_likes.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_likes` WHERE `companies_likes`.id_company = `companies_company`.id) AS company_like') )
//                    ->joinLeft( 'companies_tours', 'companies_tours.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_tours` WHERE `companies_tours`.id_company = `companies_company`.id) AS tours') )                    
//                    ->where( 'is_confirmed = 1' )
//                    ->where( 'companies_company.type = "tour"' )
//                    ->group( 'id' )
//                    ->order( array('tours DESC', 'company_like DESC', 'name ASC') ); 
//            //throw new Exception($select);
//            if ($limit > 0)
//                $select->limit($limit);
//
//            $rows = $this->fetchAll( $select );
//            foreach ( $rows as $row ) {
//                    $companies[ $row->id ] = $row;
//            }
//            return $companies;
//    }
    
    public function getActiveAutoCompanies( $limit = 0 )
    {
            $companies = array();
            $select = $this->select()
                    ->from($this, array('id', 'photo', 'name'))
                    ->setIntegrityCheck(false)
                    //->joinLeft( 'companies_likes', 'companies_likes.id_company = companies_company.id', new Zend_Db_Expr('(SELECT COUNT(*) FROM `companies_likes` WHERE `companies_likes`.id_company = `companies_company`.id) AS company_like') )
                    ->joinLeft( array('ce' => 'companies_employer'), 'ce.id_company = companies_company.id', null )
                    ->joinLeft( 'auto_car_item', 'auto_car_item.id_user = ce.id_user', new Zend_Db_Expr('(SELECT COUNT(*) FROM `auto_car_item` WHERE `auto_car_item`.id_user = `ce`.id_user) AS car_count') )
                    ->joinInner( 'companies_link_types', 'companies_link_types.id_company = companies_company.id', array('companies_link_types.id_sphere') )
                    ->where( 'companies_link_types.id_sphere = 18' )
                    //->where( 'car_count > 0')
                    ->where( 'companies_company.is_confirmed = 1' )
                    ->group( 'companies_company.id' )
                    ->order( array('car_count DESC', 'name ASC') ); 
            //throw new Exception($select);
            if ($limit > 0)
                $select->limit($limit);

            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                //if ($row->)
                    $companies[ $row->id ] = $row;
            }
            return $companies;
    } 
    
    // $shop_type - array
    public function getPopularShops($from = 0, $limit = 0, array $shop_type = null, $with_items = true)
    {
            $companies = array();
            $select = $this->select( )
                    ->from($this, array('id', 'photo', 'name', 'type', 'shop_type', 'description'))
                    ->setIntegrityCheck(false);
            
            if ($with_items)                
                $select->joinLeft( 'catalog_category', 'catalog_category.company_id = companies_company.id', new Zend_Db_Expr('(SELECT MAX(viewed) FROM `catalog_category` WHERE `catalog_category`.company_id = `companies_company`.id) AS catalog_viewed') )
                        ->joinInner( 'catalog_units', 'catalog_units.company_id = companies_company.id', null );            
            
                    //->having( 'unit_count > 0' )
            $select->where( 'is_confirmed = 1' )->where( 'type = "shop"' );
            
            if (count($shop_type) > 0)
                $select->where( 'shop_type IN (?)', $shop_type );                    
                    
            $select->group( 'id' );
            
            if ($with_items)
                $select->order( array('catalog_viewed DESC', 'name ASC') ); 
            else
                $select->order( 'name ASC' ); 
            
            //throw new Exception($select);
            if ($from > 0) {            
                $select->limit(Companies_Model_Company::$shop_per_lazypage, $from);
            } elseif ($limit > 0) {
                $select->limit($limit);
            }

            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                    $companies[ $row->id ] = $row;
            }
            return $companies;
    }
    
    public function getNewShops($from = 0, $limit = 0, $with_items = true)
    {
            $companies = array();
            $select = $this->select()
                    ->from($this, array('id', 'photo', 'name', 'type', 'shop_type', 'description', 'signup_date'))
                    ->setIntegrityCheck(false);
            
            if ($with_items)                
                    $select->joinInner( 'catalog_units', 'catalog_units.company_id = companies_company.id', null );
            
            $select->where( 'is_confirmed = 1' )
                    ->where( 'companies_company.type = "shop"' )
                    ->group( 'id' )
                    ->order( 'signup_date DESC' ); 
            //throw new Exception($select);
            
            if ($from > 0) {            
                $select->limit(Companies_Model_Company::$shop_per_lazypage, $from);
            } elseif ($limit > 0) {
                $select->limit($limit);
            }

            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                    $companies[ $row->id ] = $row;
            }
            return $companies;
    }
    
    public function getShopsCatalog($limit = 0)
    {
            $companies = array();
            $select = $this->select()
                    ->from($this, array('shop_type', 'COUNT(shop_type) as shop_count'))
                    ->setIntegrityCheck(false)
                    //->joinLeft( 'catalog_units', 'catalog_units.company_id = companies_company.id' )            
                    ->where( 'is_confirmed = 1' )
                    ->where( 'type = "shop"' )                     
                    ->group( 'shop_type' )
                    ->order( 'shop_count DESC' ); 
            
            //throw new Exception($select);
            if ($limit > 0)
                $select->limit($limit);            

            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                    $companies[] = $row;
            }
            return $companies;
    }    
    
    public function getListBySphere( array $sphere, $from = 0, $limit = 0 )
    {
        $companies = array();
        if ( empty($sphere) ) return;

        //$table = new Companies_Model_Table_LinkTypes();
        $select = $this->select()
                ->from($this->_name, array('id', 'photo', 'name', 'description', 'type'))
                ->setIntegrityCheck(false)
                //->joinInner( 'companies_company', 'companies_company.id = companies_link_types.id_company' )                    
                ->joinInner( 'companies_link_types', 'companies_link_types.id_company = companies_company.id', array('companies_link_types.id_sphere') )
                ->where( 'companies_link_types.id_sphere in (?)', $sphere )                  
                ->where( 'companies_company.is_confirmed = 1' )
                //->group( 'id' )
                ->order( 'companies_company.signup_date DESC' );
        
        if ($from > 0) {            
            $select->limit(Companies_Model_Company::$company_per_lazypage, $from);
        } elseif ($limit > 0) {
            $select->limit($limit);
        }
        
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
                $companies[ $row->id ] = $row;
        }
        return $companies;
    }    

    public function FilterByName($search_str)
    {
        $items = array();
        $select = $this->select()->from($this, array('id', 'url', 'name', 'type', 'photo'));
        if ($search_str != '') {
            $select->where("name LIKE ?", "%".$search_str."%")->where('is_confirmed = 1'); //->orwhere("name_eng LIKE ?", "%".$search_str."%");
        } else {
            return $this->getCompanies();
        }
        $select->order( 'signup_date desc' );
        $rows = $this->fetchAll($select);
        foreach ( $rows as $row ) $items[ $row->id ] = $row;
        return $items;
    }
    
    // на идексе компаний
    public function getLugaresPopulares($limit = 0, $day = 7)
    {
        $items = array();
        
        $subselect = $this->select()
                ->from(array('e' => 'events_event'), array('id_company', 'COUNT(e.id) as company_events', 'SUM(e.counter) as company_event_views'))
                ->setIntegrityCheck(false)
                ->where('TO_DAYS(e.start_date) - TO_DAYS(NOW()) >= 0')
                ->where('e.is_confirmed = 1')
                ->group('name')
                ;

        $select = $this->select()
                ->from(array('c' => $this->_name), array('id', 'photo', 'name', 'e.company_events', 'e.company_event_views + SUM(cv.counter) as populares'))
                ->setIntegrityCheck(false)
                ->joinLeft( array('cv' => 'companies_view'), 'cv.id_company = c.id', null )
                ->joinInner( array('e' => new Zend_Db_Expr('('.$subselect.')')) , 'e.id_company = c.id', null )                
                ->where( 'c.is_confirmed = 1' )
                ->where( 'TO_DAYS(NOW()) - TO_DAYS(cv.date) <= ?', $day )
                ->order( array('populares DESC') )
                ->group(array('c.id'))
                ;
        
        if ($limit > 0) $select->limit($limit);
        
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
                $items[ $row->id ] = $row;
        }
        return $items;        
    }    
    
    // на индексе в колонке слева Carretes populares
    public function getCarretesPopulares($limit = 0, $c_events = 0)
    {
        $items = array();
        
        $table = new Events_Model_Table_Events();
        
//select b.name_company from  
//(select a.name_company, COUNT(a.name_company) as cnt_eve from 
//(SELECT 
// cc.name as name_company
//, sb.name as name_events
//  FROM companies_company cc
//  inner join 
//    (SELECT id_company, name
//    FROM events_event where start_date > '2013-06-10'
//    group by id_company, name) sb on sb.id_company = cc.id) a
//group by a.name_company) b
//where cnt_eve >= 2
//limit 0,3
        
        $subselect2 = $table->select()
                ->from(array('e' => 'events_event'), array('id_company', 'name', 'COUNT(id_company) as all_company_events'))
                ->setIntegrityCheck(false)
                ->where('(TO_DAYS(e.start_date) - TO_DAYS(NOW())) >= 0')
                ->where('e.type = 1')
                ->where('e.is_confirmed = 1')
                ->group( array('e.id_company', 'e.name') ); 
        
        $subselect1 = $this->select()
                ->from(array('cc' => 'companies_company'), array('id', 'url', 'name', 'type', 'sb.name as name_events', 'photo', 'sb.all_company_events'))
                ->setIntegrityCheck(false)
                ->joinInner( array('sb' => new Zend_Db_Expr('('.$subselect2.')')) , 'sb.id_company = cc.id', null )
                ;        

        $subselect = $this->select()
                ->from(array('a' => new Zend_Db_Expr('('.$subselect1.')')), array('id', 'url', 'name', 'type', 'photo', 'COUNT(a.name_events) as company_events', 'SUM(all_company_events) as all_company_events'))
                ->setIntegrityCheck(false)
                ->group('name')
                ; 
        
        $select = $this->select()
                ->from(array('b' => new Zend_Db_Expr('('.$subselect.')')), array('id', 'url', 'photo', 'type', 'name', 'company_events', 'all_company_events'))
                ->setIntegrityCheck(false);
        
        if ($c_events > 0)
            $select->where( 'company_events >= ?' , (int) $c_events );         
        
        if ($limit > 0) $select->limit($limit);
        
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
                $items[ $row->id ] = $row;
        }
        return $items;        
    }
    
    // кол-во контор по типу
    public function getCountByType ($type)
    {
        if ((!empty($type)) && (array_key_exists($type, Companies_Model_Company::$_types))) {

            $select = $this->select()->setIntegrityCheck(false)->from(array("f" => $this->_name), array('companyCount' => 'COUNT(id)'))->where('type = ?', $type)->where('is_confirmed = 1');
            $result = $this->fetchRow($select);
            return $result->companyCount;

        } else {
            return NULL;
        }                    
    }   
    
    // кол-во контор по типу
    public function getCountBySphere ($sphereId)
    {
        
            $companies = array();
            $select = $this->select()
                    ->from($this, array('id'))
                    ->setIntegrityCheck(false)
                    ->joinInner( 'companies_link_types', 'companies_link_types.id_company = companies_company.id', array('companies_link_types.id_sphere') )
                    ->where( 'companies_link_types.id_sphere = ?', $sphereId )
                    ->where( 'companies_company.is_confirmed = 1' )
                    //->group( 'companies_company.id' )
                    ; 
            //throw new Exception($select);

            $rows = $this->fetchAll( $select );
            return (int) count($rows);
        
    }    

    /**
     * Создание нового
     * @param  array $data OPTIONAL данные инициализации
     * @param  string $defaultSource OPTIONAL флаг указывающий установить значения по умолчанию
     * @return Companies_Model_Company
     */
    public function createRow( $data = array(), $defaultSource = null )
    {
        $new = parent::createRow( $data, $defaultSource );
        $new->signup_date = date_create()->format( 'Y-m-d H:i:s' );
        return $new;
    }
}