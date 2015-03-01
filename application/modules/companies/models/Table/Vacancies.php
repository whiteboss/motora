<?php
/**
 *
 * Интерфейс доступа к таблице данных вакансий
 *
 */

class Companies_Model_Table_Vacancies extends Zend_Db_Table_Abstract {
    
    protected $_name = 'companies_vacancy';
    protected $_rowClass = 'Companies_Model_Vacancy';

    protected $_referenceMap = array (
        'Company' => array (
                'columns' => 'id_company', 
                'refTableClass' => 'Companies_Model_Table_Companies', 
                'refColumns' => 'id'
        ),
        'City' => array (
            'columns' => 'id_city',
            'refTableClass' => 'Application_Model_Table_Cities',
            'refColumns' => 'id'
        )            
    );

    public function getVacancies($limit = 0, $hot = 0)
    {
        $data = array();
        $select = $this->select()->from($this, array('id', 'id_company', 'visible', 'position', 'description', 'salary_from', 'salary_to'))->where('visible = 1');
        
        if ($hot == 1) $select->where( 'is_hot = 1' );
                
        $select->where('salary_from > 0')->order(array('salary_from DESC', 'salary_to DESC'));

        if ($limit > 0)        
            $select->limit($limit);
        
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $data[ $row->id ] = $row;
        }
        return $data;        
    }
    
    public function getVacanciesByName($jobname)
    {
        $data = array();
        $select = $this->select()->from($this, array('id', 'id_company', 'visible', 'position', 'description', 'salary_from', 'salary_to'))->where('visible = 1');
        $select->where( 'position LIKE ?', '%'.$jobname.'%' );
        $select->where( 'salary_from > 0' )->order( array('salary_from DESC', 'salary_to DESC') );
        
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $data[ $row->id ] = $row;
        }
        return $data;        
    }    
    
    public function getVacanciesByIndustry($industryId)
    {
        $data = array();
        $select = $this->select()->from($this, array('id', 'id_company', 'visible', 'industry', 'position', 'description', 'salary_from', 'salary_to', 'date'))->where('visible = 1');
        if ($industryId > 0)
            $select->where( 'industry = ?', $industryId);
        
        $select->order( array('salary_from DESC', 'salary_to DESC') );
        
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $data[ $row->id ] = $row;
        }
        return $data;        
    }    
    
    public function getCatalog()
    {
        $data = array();
        $select = $this->select()->from($this, array('id', 'COUNT(position) as vacancy_count', 'id_company', 'visible', 'position', 'industry', 'salary_from', 'salary_to'))->where('visible = 1')->group('position')->order('industry ASC');
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $data[ $row->id ] = $row;
        }
        return $data;        
    }
    
    public function getIndustryCatalog()
    {
        $data = array();
        $select = $this->select()->from($this, array('id', 'COUNT(industry) as industry_count', 'id_company', 'industry'))->where('visible = 1')->group('industry')->order('industry ASC');
        //throw new Exception($select);
        $rows = $this->fetchAll( $select );
        foreach ( $rows as $row ) {
            $data[ $row->id ] = $row;
        }
        return $data;        
    }    

    /**
     * Создание нового
     * @param  array $data OPTIONAL данные инициализации
     * @param  string $defaultSource OPTIONAL флаг указывающий установить значения по умолчанию
     * @return Companies_Model_Vacancy
     */
    public function createRow( $data = array(), $defaultSource = null ) {
        $new = parent::createRow( $data, $defaultSource );
        $new->date = date_create()->format( 'Y-m-d H:i:s' );
        return $new;
    }
    
}