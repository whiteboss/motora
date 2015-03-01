<?php

class Application_Model_Map extends Application_Model_Abstract
{
    
    protected $_author;
    protected $_company;

    public function getRating() {
        $rating = $this->isgood - $this->ispoor;
        return (int) $rating;        
    }
    
    public function getAuthor() {
        if ( !empty($this->_author) ) return $this->_author;
        $this->_author = $this->findParentRow( 'Application_Model_Table_Users' );
        return $this->_author;        
    }
    
    public function getCompany() {
        if ( !empty($this->_company) ) return $this->_company;
        $this->_company = $this->findParentRow( 'Companies_Model_Table_Companies' );
        return $this->_company;        
    }    
    
}

