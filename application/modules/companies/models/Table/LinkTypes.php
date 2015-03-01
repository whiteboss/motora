<?php

class Companies_Model_Table_LinkTypes extends Zend_Db_Table_Abstract
{

    protected $_name = 'companies_link_types';
    protected $_rowClass = 'Companies_Model_LinkType';
    protected $_referenceMap = array(
        'Company' => array(
            'columns'       => 'id_company',
            'refTableClass' => 'Companies_Model_Table_Companies',
            'refColumns'    => 'id'
        ),
        'Sphere' => array(
            'columns'       => 'id_sphere',
            'refTableClass' => 'Companies_Model_Table_Spheres',
            'refColumns'    => 'id'
        )        
    );   

}

