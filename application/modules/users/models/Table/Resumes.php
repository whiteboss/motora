<?php
/**
 *
 * Интерфейс доступа к таблице данных резюме
 *
 */

class Users_Model_Table_Resumes extends Zend_Db_Table_Abstract {
    
    protected $_name = 'users_resume';
    protected $_rowClass = 'Users_Model_Resume';

    protected $_referenceMap = array (
            'User' => array (
                'columns' => 'id_user',
                'refTableClass' => 'Application_Model_Table_Users',
                'refColumns' => 'id'
            ),
            'City' => array (
                'columns' => 'id_city',
                'refTableClass' => 'Application_Model_Table_Cities',
                'refColumns' => 'id'
            )
    );

    public function getIndustryCatalog()
    {
        $data = array();
        $select = $this->select()->from($this, array('id', 'COUNT(industry) as industry_count', 'industry'))->where('is_visible = 1')->group('industry')->order('industry ASC');
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $data[ $row->id ] = $row;
        }
        return $data;        
    } 

    public function getResumesByIndustry($industryId)
    {
        $data = array();
        $select = $this->select()->from($this, array('id', 'id_user', 'is_visible', 'industry', 'position', 'skills', 'salary_from', 'date', 'CONCAT("user") as type'))->where('is_visible = 1');
        if ($industryId > 0)
            $select->where( 'industry = ?', $industryId);

        $select->where( 'salary_from > 0' )->order( array('date DESC', 'salary_from DESC') );

        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            //$row['type'] = 'user';
            $data[ $row->id ] = $row;
        }
        return $data;        
    }        

    /**
     * Создание нового
     * @param  array $data OPTIONAL данные инициализации
     * @param  string $defaultSource OPTIONAL флаг указывающий установить значения по умолчанию
     */
    public function createRow( $data = array(), $defaultSource = null ) {
            $new = parent::createRow( $data, $defaultSource );
            $new->date = date_create()->format( 'Y-m-d H:i:s' );
            return $new;
    }
}