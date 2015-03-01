<?php

/**
 *
 * Интерфейс доступа к таблице данных сотрудников
 *
 */
class Companies_Model_Table_Employers extends Zend_Db_Table_Abstract {

    protected $_name = 'companies_employer';
    protected $_rowClass = 'Companies_Model_Employer';
    protected $_referenceMap = array(
        'Company' => array(
            'columns' => 'id_company',
            'refTableClass' => 'Companies_Model_Table_Companies',
            'refColumns' => 'id'
        ),
        'User' => array(
            'columns' => 'id_user',
            'refTableClass' => 'Application_Model_Table_Users',
            'refColumns' => 'id'
        )
    );

    public function getCompanyEmployersCount($params) {
        $qs = $this->select()->setIntegrityCheck(false)->from(array("ce" => $this->_name), array("employerCount" => "COUNT(ce.id)"));

        if (isset($params['companyId']))
            $qs->where("ce.id_company = ?", $params['companyId']);

        $result = $this->fetchRow($qs);
        return $result;
    }

}