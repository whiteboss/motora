<?php

class Companies_Model_Table_Spheres extends Zend_Db_Table_Abstract
{

    protected $_name = 'companies_spheres';
    protected $_rowClass = 'Companies_Model_Sphere';
    protected $_referenceMap = array(
        'Parent' => array(
            'columns'       => 'id_parent',
            'refTableClass' => 'Companies_Model_Table_Spheres',
            'refColumns'    => 'id'
        ),
    );
    
    

    /**
     * Удаляет указанную категорию вместе с подкатегориями
     *  
     * @param type $id
     * @param type $cid 
     */
    public function deleteTree($id, $cid)
    {
        // TODO: также проверять и перемещать товары с этих категорий
        $select = $this->select(self::SELECT_WITH_FROM_PART)
                ->columns(array('id', 'parent_id'))
                ->where('parent_id = ?', $id);
        $children = $this->fetchAll($select);
        
        if ($children) {
            foreach ($children as $row) {
                $this->deleteTree($row->id, $cid);
            }
        }

        return $this->delete("company_id = $cid AND (parent_id = $id OR id = $id)");
    }

    /**
     * Возвращает дерево сфер
     * @return array
     */
    public function getRoot($rootId = 0)
    {
        $items = array();
        $select = $this->select()
                ->where('id_parent is NULL')
                ->order('name ASC');
        
        if ($rootId > 0)
            $select->where('id = ?', $rootId);

        $rows = $this->fetchAll($select);
        foreach ($rows as $row) {
            $items[$row->id] = $row;
        }
        return $items;
    }
    
    public function getAllSubs($id)
    {
        $items = array();
        $select = $this->select()
                ->where('id_parent = ?', (int) $id)
                ->order('name ASC');

        $rows = $this->fetchAll($select);
        foreach ($rows as $row) {
            $items[$row->id] = $row;
        }
        return $items;        
    }    
    
    public function getSubs($id)
    {
        $items = array();
        $select = $this->select( Zend_Db_Table::SELECT_WITH_FROM_PART )
                ->setIntegrityCheck(false)
                ->joinLeft( 'companies_link_types', 'companies_link_types.id_sphere = ' . $this->_name . '.id', null )
                ->joinLeft( 'companies_company', 'companies_company.id = companies_link_types.id_company', '
                    COUNT(companies_company.id) as companies_count'
                )
                ->where( 'companies_company.is_confirmed = 1' )
                ->where( 'id_parent = ?', (int) $id )
                ->group( $this->_name.'.id' )
                ->order( 'name ASC' );
        //throw new Exception($select);
        $rows = $this->fetchAll($select);
        foreach ($rows as $row) {
            if ($row->companies_count > 0) $items[$row->id] = $row;
        }
        return $items;        
    }
    
    
    public function getCatalog($rootId = 0)
    {
        $items = array();
        $roots = $this->getRoot($rootId);
        foreach ($roots as $root_id=>$root) {            
            $sub_cats = $this->getSubs($root_id);
            if (count($sub_cats) > 0) {
                $items[$root_id] = array( 'name' => $root, 'subs' => array() );            
                foreach ($sub_cats as $sub_id=>$sub_cat) {
                    $items[$root_id]['subs'][$sub_id] = $sub_cat;    
                }
            }
        }        
        return $items;
    } 

}

