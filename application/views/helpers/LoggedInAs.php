<?php
class Application_Views_Helper_LoggedInAs extends Zend_View_Helper_Abstract
{
    /* Для верхней шапки
     * 
     */
    public function loggedInAs ()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {

            $user = $this->view->GetUserByIdentity();
            $messages = 'Mis Mensajes';
            $short_message = '';
            $c = count($user->getUnreadMessages());
            if ($c > 0) {
                $messages .= '<span>+' . $c . '</span>';
            }
            
            $friends = 'Mis Amigos';
            $d = count($user->getFriendRequests());            
            if ($d > 0) {
                $friends .= '<span>+' . $d . '</span>';
            }
            
            return '
        <div id="profile" class="Quser pull-right point dropdown">
            <a id="drop-user" class="lnone" role="button" data-toggle="dropdown" href="#">' . $user->getAvatar(21, 21, false, '', '21_') . '<nobr>' . $user->username . '</nobr></a>
        
            <div id="user_profile" class="pull-right" role="menu" aria-labelledby="drop-user">
                <div class="Quser-bar lh14">
                    <div class="Quser-barA">
                        <div>' . $user->getAvatar(64, 64, false, '', '64_') . '</div>
                        <div class="Quser-barB">
                            <div class="w100 f14 mb5"><strong><a href="' . $this->view->url(array('userId' => $user->id), 'profile') . '">' . $user->username . '</a></strong></div>
                            <div class="w100 f11 mb10">' . $user->getFullName() . '</div>
                            <div class="w100 f11 grey">Ranking ' . $user->rate . '</div>
                        </div>
                    </div>
                    <ul class="Quser-barC">
                        <li><a href="' . $this->view->url(array('userId' => $user->id), 'userevents') . '">Mis Eventos</a></li>
                        <li><a href="' . $this->view->url(array('userId' => $user->id), 'profile') . '">Mi Perfil</a></li>
                        <li><a href="' . $this->view->url(array('userId' => $user->id), 'userposts') . '">Mi Blog</a></li>
                        <li><a href="' . $this->view->url(array('userId' => $user->id), 'usermessages') . '">' . $messages . '</a></li>
                        <li><a href="' . $this->view->url(array('userId' => $user->id), 'usercompanies') . '">Mis Lugares</a></li>
                        <li><a href="' . $this->view->url(array('userId' => $user->id), 'userfriends') . '">' . $friends . '</a></li>
                    </ul>
                    <a href="' . $this->view->url(array('userId' => $user->id), 'options') . '" class="f11 grey"><nobr><img class="Iajustes" src="/zeta/0.png" width="16" height="16" alt="" />Ajustes</nobr></a>
                    <a href="' . (!is_null($user->uid) ? $this->view->url(array('social' => $user->identity), 'logout') : $this->view->url(array(''), 'logout')) . '" class="f11 grey"><nobr><img class="Icerrar" src="/zeta/0.png" width="16" height="16" alt="" />Cerrar sesión</nobr></a>
                </div>
            </div>
        </div>    
        ';
        } 
        return '
        <div class="QuserU pull-right">
	    <a id="facebook" href="' . $this->view->url(array('social' => 'facebook'), 'auth') . '" class="conFB lnone"><img src="/zeta/0.png" width="14" height="26" alt="" />Conectar</a>
	</div>
        ';
    }

}