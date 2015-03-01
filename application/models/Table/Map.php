<?php

class Application_Model_Table_Map extends Zend_Db_Table_Abstract
{

    protected $_name = 'map';
    protected $_rowClass = 'Application_Model_Map';

    protected $_referenceMap = array (
            'Company' => array (
                    'columns' => 'company_id',
                    'refTableClass' => 'Companies_Model_Table_Companies',
                    'refColumns' => 'id'
            ),
            'User' => array (
                    'columns' => 'user_id',
                    'refTableClass' => 'Application_Model_Table_Users',
                    'refColumns' => 'id'
            ),        
    );
    
    public function getCompaniesWithoutMap()
    {
        
        $items = array();
        $select = $this->select()
                ->from($this, array('lat', 'lng'))
                ->setIntegrityCheck(false)
                ->joinRight( array('c' => 'companies_company'), 'map.company_id = c.id', array('id', 'name') )                
                ->where('c.is_confirmed = 1')
                ->where('c.type != "proj"')
                ->where('lat IS NULL AND lng IS NULL')
                ->order(array('name ASC', 'signup_date DESC'))
                ;
        
        //throw new Exception($select);
        
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $items[ $row->id ] = $row;
        }
        return $items;        
        
    }

    public function getObjectByName($search_str) {
        $items = array();

        $select = $this->select()
                ->from($this, array('id', 'lat', 'lng'))
                ->setIntegrityCheck(false)
                ->joinInner( array('c' => 'companies_company'), 'map.company_id = c.id', array('c.name') )                
                //->where('companies_company.is_confirmed = 1')
                ->where('c.name LIKE ?', '%' . $search_str . '%')//->orwhere('companies_company.name LIKE ?', '%' . $search_str . '%')
                ->order( 'date desc' )
                ->limit(1);
        
//        Throw new Exception($select);
        $rows = $this->fetchAll($select);
        
//        if (count($rows) == 0) {
//            $select = $this->select()
//                ->from($this, array('id', 'lat', 'lng'))
//                ->setIntegrityCheck(false)
//                ->joinLeft( 'companies_company', 'map.company_id = companies_company.id', null )                
//                ->where('companies_company.is_confirmed = 1')
//                ->where('companies_company.name LIKE ?', '%' . $search_str . '%')
//                ->orwhere('companies_company.name_eng LIKE ?', '%' . $search_str . '%')
//                ->order( 'date desc' )
//                ->limit(1);
//            //Throw new Exception($select);
//            $rows = $this->fetchAll($select);            
//        }    
        
        foreach ( $rows as $row ) $items[ $row->id ] = $row;
        return $items;
    }

    public function getListByCategory(array $category)
    {
        
        $items = array();
        $select = $this->select();
        $select = $this->select()
                ->from($this, array('id', 'lat', 'lng', 'date'))
                ->setIntegrityCheck(false)
                ->joinLeft( 'companies_company', 'map.company_id = companies_company.id', array('type as catId') )                
                ->where('companies_company.is_confirmed = 1')
                ->where('type in (?)', $category)
                ;

//        throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
                $items[] = $row;
        }
        return $items;
        
    }
    
    public function getObjectByLatLng($lat, $lng) {
        $items = array();
        $select = $this->select()->where( 'lat = ?', $lat )->where(' lng = ?', $lng )->limit(1);
        $row = $this->fetchRow( $select );
        if ($row) return $row; else return NULL;        
    }
    
    public function createRow( $data = array(), $defaultSource = null )
    {
        $new = parent::createRow( $data, $defaultSource );
        $new->date = date_create()->format( 'Y-m-d H:i:s' );
        return $new;
    }
    
}

