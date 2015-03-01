<?php
/**
 * 
 * Помошник вида: шапка профиля компании
 */

class Companies_View_Helper_CompaniesHead extends Zend_View_Helper_Abstract {

	public function companiesHead() {
            
            $request = Zend_Controller_Front::getInstance()->getRequest();

            $output = '<ul class="EVE-TYPES f110 BREAK">';
            
            if ( ($request->getActionName() == 'index') && (!$request->getParam('type')) && (!$request->getParam('sphere')) )
                $output .= '<li class="act-002">Compañias del Santiago</li>';
            else
                $output .= '<li><a href="' . $this->view->url(array(), 'companies') . '">Compañias del Santiago</a></li>';                
            
            if ($request->getActionName() == 'catalog')
                $output .= '<li class="act-002">Catálogo</li>';
            else
                $output .= '<li><a href="' . $this->view->url(array(''), 'companiescatalog') . '">Catálogo</a></li>';
            
//            $output .= '<li><img class="COLLECTION" src="/sprites/null.png" width="2" height="10" alt="" /></li>';
            
            if ($request->getActionName() == 'allvacancies')
                $output .= '<li class="act-002">Trabajo</li>';
            else
                $output .= '<li><a href="' . $this->view->url(array(''), 'job') . '">Trabajo</a></li>';
                        
            $output .= '<li><a href="' . $this->view->url(array(''), 'map') . '">En el plano de la ciudad</a></li>                        
                        <li><a href="' . $this->view->url(array(''), 'oramas') . '">Panorama</a></li>';

            $output .= '<li class="right"><a href="' . $this->view->url(array(), 'createcompany') . '">Crear compañía</a></li>';

            $output .= '</ul>';
            return $output;
	}
}