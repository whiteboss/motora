<?php

class Post_Bootstrap extends Zend_Application_Module_Bootstrap {

    public function initResourceLoader() {
        $loader = $this->getResourceLoader();
        $loader->addResourceType('helper', 'helpers', 'Helper');
    }

    public function activeInitHelpers() {
        Zend_Controller_Action_HelperBroker::addHelper( new Qlick_Controller_Helper_InitInterface() );
        Zend_Controller_Action_HelperBroker::addHelper( new Qlick_Controller_Helper_InitInterfaceUI() );
    }

    protected function _initRouters() {
        $this->bootstrap('frontcontroller');
        $router = $this->frontController->getRouter();
        //$router->addRoute('posts', new Zend_Controller_Router_Route('lents/:page', array('module' => 'feed', 'controller' => 'index', 'action' => 'catalog', 'page' => '1')));
        $router->addRoute('createpost', new Zend_Controller_Router_Route('createpost', array('module' => 'post', 'controller' => 'index', 'action' => 'new')));
        $router->addRoute('postupload', new Zend_Controller_Router_Route('posts/upload', array('module' => 'post', 'controller' => 'index', 'action' => 'upload')));
        $router->addRoute('editorpostupload', new Zend_Controller_Router_Route('posts/upload/editor', array('module' => 'post', 'controller' => 'index', 'action' => 'upload', 'editor' => true)));
        $router->addRoute('postindexbageupload', new Zend_Controller_Router_Route('posts/indexbageupload', array('module' => 'post', 'controller' => 'index', 'action' => 'indexbageupload')));
        $router->addRoute('postbageupload', new Zend_Controller_Router_Route('posts/bageupload', array('module' => 'post', 'controller' => 'index', 'action' => 'bageupload')));
        $router->addRoute('postmainbageupload', new Zend_Controller_Router_Route('posts/mainbageupload', array('module' => 'post', 'controller' => 'index', 'action' => 'mainbageupload')));
        $router->addRoute('postfileupload', new Zend_Controller_Router_Route('posts/fileupload', array('module' => 'post', 'controller' => 'index', 'action' => 'fileupload')));
        $router->addRoute('postbytag', new Zend_Controller_Router_Route('publicaciónes/tema/:tag', array('module' => 'post', 'controller' => 'index', 'action' => 'postsbytag')));
        //$router->addRoute('feedview', new Zend_Controller_Router_Route('feed/view/:feedId', array('module' => 'feed', 'controller' => 'index', 'action' => 'feedview')));
        $router->addRoute('post',
                new Zend_Controller_Router_Route_Regex(
                        'post(\d+)/?(view|edit|like|favorite|delete|togglenoticia)?',
                        array('module' => 'post', 'controller' => 'index', 'action' => 'view'),
                        array(1 => 'postId', 2 => 'action'),
                        'post%d/%s'
                )
        );
        // посты с ЧПУ
        $router->addRoute(
            'post_seo',
            new Zend_Controller_Router_Route('publicación/:post_url', array('module' => 'post', 'controller' => 'index', 'action' => 'view'))
        );
        
        $router->addRoute(
            'video_seo',
            new Zend_Controller_Router_Route('video/:post_url', array('module' => 'post', 'controller' => 'index', 'action' => 'view'))
        );
        
    }

}

