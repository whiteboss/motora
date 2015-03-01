<?php
/**
 *
 * Интерфейс доступа к таблице данных резюме
 *
 */

class Companies_Model_Table_Resumes extends Zend_Db_Table_Abstract {
	protected $_name = 'companies_resume';
	protected $_rowClass = 'Companies_Model_Resume';

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
        
        public function getIndustryCatalog()
        {
            $data = array();
            $select = $this->select()->from($this, array('id', 'COUNT(industry) as industry_count', 'id_company', 'industry'))->group('industry')->order('industry ASC');
            //throw new Exception($select);
            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                $data[ $row->id ] = $row;
            }
            return $data;        
        }
        
        public function getIndustryCatalogWithUsers()
        {
            $table = new Users_Model_Table_Resumes();
            $adapter = $this->getAdapter();            
            $select = $adapter->select()->from( $adapter->select()->union( array(
                $this->select()->from($this, array('COUNT(industry) as industry_count', 'industry')),
                $table->select()->from($table, array('COUNT(industry) as industry_count', 'industry'))                
                ), Zend_Db_Select::SQL_UNION_ALL ), array('industry', 'SUM(industry_count) AS industry_count') )->where('industry_count > 0')->group('industry');
            
            //throw new Exception($select);
            $rows = $adapter->fetchAll( $select );            
            return $rows;        
        }        
        
        public function getResumesByIndustry($industryId)
        {
            $data = array();
            $select = $this->select()->from($this, array('id', 'id_company', 'industry', 'position', 'skills', 'salary_from', 'date', 'CONCAT("company") as type'));
            if ($industryId > 0)
                $select->where( 'industry = ?', $industryId);

            $select->where( 'salary_from > 0' )->order( array('date DESC', 'salary_from DESC') );

            //throw new Exception($select);
            $rows = $this->fetchAll( $select );
            foreach ( $rows as $row ) {
                $row['type'] = 'company';
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