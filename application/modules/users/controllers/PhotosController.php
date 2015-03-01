<?php

class Users_PhotosController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction() {
            $this->getResponse()
                    ->setHeader('Content-Type', 'text/html; charset=utf-8' );

            $this->_helper->initInterface();
            $uid = ( int ) $this->getRequest()->getParam ( 'userId' );
            if ( !empty($uid) ) {
                    $user = $this->_helper->userProfile($uid);
                    $this->view->friends = $user->getPhotos();
            }
    }


}

