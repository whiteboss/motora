<?php
/**
 * 
 * Помошник вида: фильтр
 */

class Auto_View_Helper_Filter extends Zend_View_Helper_Abstract {
	
    public function filter() {
        
        $this->view->headScript()
                ->appendFile( '/resources/chosen/chosen.jquery.min.js' )
                ->appendFile( '/resources/jquery/js/jquery.form.js' )
                //->appendFile( '/js/auto/filter.js' )
                ->appendFile( '/js/jquery.sticky.js' )
                ;
        
        $this->view->headLink()
                ->appendStylesheet('/resources/chosen/chosen.css');

        $output = '
        <div id="sticker" class="BREAK"><a id="search_toggle" class="ESF-T f90"><img class="coll" src="/sprites/null.png" width="20" height="18" alt="" />Изменить параметры поиска</a></div>
        ' . $this->view->car_search_form . '
        ';
        
        return $output;
        
    }
}