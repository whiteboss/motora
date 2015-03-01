<?php
/**
 * 
 * Помошник вида: шапка профиля компании
 */

class Companies_View_Helper_ProfileHead extends Zend_View_Helper_Abstract {

	public function profileHead() {

                $request = Zend_Controller_Front::getInstance()->getRequest();
                $identity = Zend_Auth::getInstance()->getIdentity();

		$tabs['profile'] = 'Perfil de lugar';
                $tabs['visitors'] = 'Visitantes';
                $tabs['lents'] = 'Blog de empresa';
                $tabs['photos'] = 'Fotoreportajes';
                //$tabs['music']  = 'Música';
		$tabs['events'] = 'Eventos';
                $tabs['video']  = 'Vídeo';

                $list = array();
                foreach ( $tabs as $name => $title ) {
                        if ( $request->getActionName() == $name)
				$list[] = '<li class="bor4"><a class="aktiv bor4 black lnone" href="' . $this->view->company->getUrl($name) . '"><b>' . $title . '</b><img class="IblogM1 pull-right" src="/zeta/0.png" width="33" height="33" alt=""></a></li>';
                        else
                                $list[] = '<li><a class="bor4" href="' . $this->view->company->getUrl($name) . '">' . $title . '</a></li>';
                }

                $output = '
                <ul class="QblogM QblogM2 f14 pull-left mb15 QperfM lugar-menu">
			' . implode("\n", $list) . '			
		</ul>';
                
		return $output;
                
	}
}