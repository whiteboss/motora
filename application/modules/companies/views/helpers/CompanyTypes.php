<?php
/**
 * 
 * Помошник вида: шапка профиля компании
 */

class Companies_View_Helper_CompanyTypes extends Zend_View_Helper_Abstract {

	public function companyTypes() {
            
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $typeId = str_replace('-', ' ', $request->getParam( 'company_type' ));

            $output = '<ul class="Qlugares-menu w100 mb40">';
            
            if (empty($typeId)) {
                $output .= '<li><a class="Qlactive" href="' . $this->view->url(array(), 'companies') . '">Todos</a></li>';    
            } else {
                $output .= '<li><a href="' . $this->view->url(array(), 'companies') . '">Todos</a></li>';
            }
            
            foreach (Companies_Model_Company::$header_types as $key => $type) {
                if ($typeId == mb_strtolower($type, 'UTF-8')) {
                    $output .= '<li><a class="Qlactive" href="' . $this->view->url(array('company_type' => mb_strtolower(str_replace(' ', '-', $type), 'UTF-8')), 'companybytype') . '">' . $type . '</a></li>';
                } else {
                    $output .= '<li><a href="' . $this->view->url(array('company_type' => mb_strtolower(str_replace(' ', '-', $type), 'UTF-8')), 'companybytype') . '">' . $type . '</a></li>';
                }
            }

            $output .= '</ul>';
            
            return $output;
            
	}
}