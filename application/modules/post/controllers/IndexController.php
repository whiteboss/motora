<?php

class Post_IndexController extends Zend_Controller_Action {

    public function init()
    {
//        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
    
    public function getcommentsfbAction()
    {
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        
        $table = new Post_Model_Table_Post();
        $posts = $table->getPopularPosts(0, 1); // собираем просмотренные за день        
        
        $fb = new Qlick_Api_Facebook_Facebook(array(
            'appId' => Zend_Registry::get('FB_app_ID'),
            'secret' => Zend_Registry::get('FB_app_secret'),
            'cookie' => true
        ));
        
        foreach ($posts as $post) {
//            $id = 'http://qlick.cl/post43/view';
//            $result = $fb->api("http://qlick.cl/post43/view");
            $page_id = 'http://' . Zend_Registry::get('server_name') . $post->getUrl();
            $result = $fb->api($page_id);
//            die(print $result['shares']);
            if ((count($result) > 0) && (isset($result['comments']) || isset($result['shares']))) {
                $row = $table->find($post->id)->current();
                if (isset($result['comments'])) $row->fb_comments = $result['comments'];
                if (isset($result['shares'])) $row->fb_shares = $result['shares'];
                $row->save();
            }             
        }
        
    }        

    public function togglenoticiaAction()
    {
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();   
        
        $id = (int) $this->_getParam('postId');
        
        $table = new Post_Model_Table_Post();
        $rows = $table->find( $id );
        if ( count($rows) > 0 ) {
            
            $post = $rows->current();
            if ($post->company_id > 0) {            
            
                $table = new Application_Model_Table_Noticias();

                if (!($noticia_id = $post->inNoticias())) {            
                    $row = $table->createRow(array('type' => 'post', 'id_type' => $id));
                    $id = $row->save();
                    $this->getResponse()->setBody( "<img class='Inoticias1' src='/zeta/0.png' width='29' height='29' alt='' /> " );
                } else {
                    $rows = $table->find( $noticia_id );
                    $row = $rows->current();                
                    $row->delete();
                    $this->getResponse()->setBody( "<img class='Inoticias2' src='/zeta/0.png' width='29' height='29' alt='' />" );
                }
                
            } else {
                $this->getResponse()->setBody( "<img class='Inoticias2' src='/zeta/0.png' width='29' height='29' alt='' /> no hay empresa" );
            }
            
        } else {
            $this->getResponse()->setBody( "<img class='Inoticias2' src='/zeta/0.png' width='29' height='29' alt='' />" );
        }
        
    }    
    
    protected function sendEmail($post_url, $post_name)
    {
        
        $url = 'http://' . Zend_Registry::get('server_name') . $post_url;
        
        $mal = new Zend_Mail('UTF-8');
        //Уважаемый '.$user->lastname.' '.$user->firstname.', ссылка для подтверждения: <a href="' . $link .  '">' . $link . '</a>'
        $mal->setBodyHtml('
            <table cellspacing="0" cellpadding="0" border="0" width="100%" style="background: url(http://www.qlick.cl/sprites/qmail/line.gif) repeat-x top left;">
                <tr>
                    <td width="5%">
                    </td>
                    <td width="60%">
                        <h1 style="font-family: Calibri, Arial; font-size: 24px; text-transform: uppercase; padding: 30px 0 15px 0; border-bottom: 1px #000000 solid; width: 80%;">Qlick.cl — Подтверждение новой публикации</h1>
                        <p style="font-family: Arial; font-size: 12px; padding: 5px 0 12px 0; line-height: 150%;">
                            Добавился пост <strong>' . $post_name . '</strong>, чтобы его посмотреть, пройдите по ссылке:
                            <a style="color: #284C6A; text-decoration: none;" href="'. $url . '">' . $url . '</a>                                    
                        </p>                        
                    </td>
                    <td width="35%">
                    </td>
                </tr>
            </table>
                ')
                ->setFrom('no-reply@qlick.cl', 'Qlick.cl')
                ->addTo('mailbox@qlick.cl')
                ->setSubject('Confirmación de publicación en Qlick.cl')
                ->send();
    }
    
    protected function sendAcceptEmail($user, $post_name, $post_url, $post_image)
    {

        // если не ФБ юзер тогда письмо
        if (is_null($user->uid)) {
        
            $mal = new Zend_Mail( 'UTF-8' );
            //Уважаемый '.$user->lastname.' '.$user->firstname.', ссылка для подтверждения: <a href="' . $link .  '">' . $link . '</a>'
            $mal->setBodyHtml('
                    Hola, '.$user->username.'!
                    <br /><br />
		    Ваша пост подтвержден
                    <br /><br />
		    Atentamente, Qlick.cl<br />
		    <a href="http://www.qlick.cl/">http://qlick.cl<br />
		    ')
                    ->setFrom('no-reply@qlick.cl', 'Qlick.cl')
                    ->addTo($user->email)
                    ->setSubject('Confirmación de empresa en Qlick.cl')
                    ->send();
            
        } else {           
            
            
        }    

    }     

    public function newAction()
    {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface('1.8.0');
        //$this->_helper->initInterfaceUI();
        
        $this->view->headScript()
                ->appendFile('/resources/uploadify/jquery.uploadify.js')                
                ->appendFile('/resources/imperavi/redactor.min.js')  // https://github.com/yiiext/imperavi-redactor-widget/tree/master/assets
                ->appendFile('/resources/imperavi/lang/es.js')
                ->appendFile('/js/singleUploader.js')
                //->appendFile('/js/jcrop/jquery.Jcrop.min.js')
                ->appendFile('/js/photoUploader.js') 
                ->appendFile('/js/charCount.js ')
                ;
                
        
       if ($this->view->isGodOfProject()) {               
           $this->view->headScript()               
                ->appendFile('/js/post/newpost.js');
       } else {
           $this->view->headScript()               
                ->appendFile('/js/post/user_newpost.js');
       }
                
//                ->appendFile( '/resources/everslider/js/jquery.everslider.min.js' )
//                ->appendFile( '/resources/everslider/js/jquery.easing.1.3.js' )
                
        
//        if ($this->view->isGodOfProject())
//            $this->view->headScript()
//                ->appendFile('/resources/chosen/chosen.jquery.min.js');
        
        $this->view->headLink()
                ->appendStylesheet('/resources/imperavi/redactor.css')
                //->appendStylesheet('/css/jcrop/jquery.Jcrop.css')
                ->appendStylesheet('/resources/uploadify/uploadify.css')
                //->appendStylesheet('/resources/everslider/css/everslider.css')
                ;
        
//        if ($this->view->isGodOfProject())
//            $this->view->headLink()
//                ->appendStylesheet('/resources/chosen/chosen.css');

        $identity = $this->view->getUserByIdentity();
        if (!is_null($identity)) {
            $user_id = $identity->id;
        } else {
            $this->_helper->FlashMessenger(array('system_message' => 'Por favor, inicie sesión'));
            $this->_redirect('/signin');
        }
        
        $table = new Events_Model_Table_Events();
        // рэндомные концерты
        $concerts = $table->getConcertsToday(); 
        if (count($concerts) > 6) {
            srand((float) microtime() * 10000000);
            $random_concerts = array_rand($concerts, 6);
            $items = array();
            foreach ($random_concerts as $key=>$concert)
                $items[$key] = $concerts[$concert];
            
            $this->view->concerts = $items;
            unset($items);
        } else {
            $this->view->concerts = $concerts;
        }       
        
        if ($this->view->isGodOfProject()) { 
            $form = new Post_Form_CreatePost();
        } else {
            $form = new Post_Form_UserCreatePost();
        }
        $form->getElement('url')->addValidator('Db_NoRecordExists', false, array('table' => 'post_posts', 'field' => 'url'));
        
        $userManager = new Application_Model_Table_Users();
        $companyManager = new Companies_Model_Table_Companies();
        
        // боссам можно выбирать любую контору
        if ($this->view->isGodOfProject()) {
            $form->setTemplate('/forms/new_post');
            $userCompanies = $companyManager->getCompanies(0,0,0);
            // iframe только для боссов
            //$form->getElement('post')->addFilter(new Zend_Filter_StripTags(array('allowTags' => array('li', 'b', 'i', 'strong', 'h1' => array('style', 'class'), 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'div' => array('style', 'class'), 'span' => array('style', 'class'), 'p' => array('style'), 'img' => array('src', 'title', 'width', 'height'), 'video' => array('src', 'class', 'id', 'width', 'height'), 'iframe' => array('src', 'width', 'height', 'frameborder'), 'a' => array('href', 'title'), 'br', 'em', 'table', 'tbody', 'tr', 'td')) ) );
        } else {
            $form->setTemplate('/forms/new_post_user');
            $form->getElement('post')->addFilter(new Zend_Filter_StripTags(array('allowTags' => array('li', 'b', 'i', 'strong', 'h1' => array('style', 'class'), 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'div' => array('style', 'class'), 'span' => array('style', 'class'), 'p' => array('style'), 'img' => array('src', 'title', 'width', 'height'), 'a' => array('href', 'title'), 'br', 'em', 'table', 'tbody', 'tr', 'td')) ) );
            
//            $this->_helper->FlashMessenger(array('system_message' => 'Publicado!'));
//            $this->_redirect( $this->view->url(array(''), 'lents') );
            
            $user = $userManager->find($user_id)->current();
            $userCompanies = $user->getCompanies();
        }         
        
        $feedManager = new Feed_Model_Table_Feed();
//        $feeds = $feedManager->select()->query()->fetch();
//        if (!is_array($feeds) || empty($feeds) || !$feeds) {
//            $saveMessage = "Для создания поста необходимо создать хотя бы одну ленту";
//            $this->_helper->FlashMessenger(array('system_message' => $saveMessage));
//            //$this->getHelper('redirector')->gotoSimpleAndExit('catalog', 'index', 'feed');
//            $this->_redirect( $this->view->url(array(''), 'lents') );
//        }

        $feedId = (int) $this->_getParam("feedId");
//        $rubricId = $this->_getParam("rubric_id");        

//        if (empty($feedId) || !$feedId) {
//            $this->_helper->FlashMessenger(array('system_message' => 'Необходимо выбрать ленту'));
//            //$this->getHelper('redirector')->gotoSimpleAndExit('catalog', 'index', 'feed');
//            $this->_redirect( $this->view->url(array(''), 'lents') );
//        }

        if (!empty($feedId)) {
            $feed = $feedManager->find($feedId)->current();
            $rubricId = $feed->rubric_id;
        } else {
            $rubricId = 0;
        }
        //$rubricId = $item->getRubric()->id;
        
        if (count($userCompanies) == 0)
            $form->removeElement('company_id');
        else
            $form->getElement('company_id')->setMultiOptions(array('0' => '- Elección de empresa -') + $userCompanies);
        
        $rubricManager = new Feed_Model_Table_Rubric();
        
        $postManager = new Post_Model_Table_Post();
        //this->view->popular_posts = $postManager->getPosts(null, 0, 10, false);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            //проверка валидации формы
            if ($form->isValid($data)) {
                
                $remoteTypograf = new Qlick_RemoteTypograf('UTF-8');
                $remoteTypograf->htmlEntities();
                $remoteTypograf->br (false);
                $remoteTypograf->nobr (3); 
                $remoteTypograf->quotA ('laquo raquo');
                $remoteTypograf->quotB ('bdquo ldquo');

                $path = Zend_Registry::get('upload_path');

                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->setDestination(realpath(dirname('.')).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR);
                $files  = $upload->getFileInfo();

                foreach($files as $file => $fileInfo) {
                    if ($upload->isUploaded($file)) {
                        if ($upload->isValid($file)) {

                            try {
                                $upload->receive($file);                            
                            } catch (Zend_File_Transfer_Exception $e) {
                                echo $e->message();
                            }

                         }
                    }
                }

                $data = $form->getValues();

                $name = $upload->getFileName('video'); 
                if (!is_array($name)) {                            
                    $ext = strtolower(substr(strrchr($name, '.'), 1));
                    $newName = time() . md5($name) . '.' . $ext;

                    $filterFileRename = new Zend_Filter_File_Rename(array('target' => $path . '/post/video/' . $newName, 'overwrite' => true));
                    $filterFileRename->filter($name);
                    $data['video'] = $newName;
                }    
                
                $name = $upload->getFileName('video_webm'); 
                if (!is_array($name)) {                            
                    $ext = strtolower(substr(strrchr($name, '.'), 1));
                    $newName = time() . md5($name) . '.' . $ext;

                    $filterFileRename = new Zend_Filter_File_Rename(array('target' => $path . '/post/video/' . $newName, 'overwrite' => true));
                    $filterFileRename->filter($name);
                    $data['video_webm'] = $newName;
                }

                // замена на тире
                $data['user_id'] = $user_id;
                $data['name'] = str_replace(' - ', ' — ', $data['name']);
                $data['description'] = str_replace(' - ', ' — ', $data['description']);
                $data['post'] = $remoteTypograf->processText($data['post']);
                
                // обработка тегов
                $tags = explode(',', $data['tags']);
                foreach ($tags as $key=>$tag) {
                    $tags[$key] = trim($tag);
                }                        
                $data['tags'] = implode(',', $tags); //preg_replace('/\s/', '', $data['tags']);
                
                if ($data['gallery_id'] == 0) unset($data['gallery_id']);
                
                //$null = new Zend_Db_Expr("NULL");
                $path = Zend_Registry::get('upload_path');
                
                if ($data['photo_list']) {
                    
                    $file = $data['photo_list'];

                    if (is_file($path . '/tmp/' . $file)) {

                        // определим размер
                        $size = getimagesize($path . '/tmp/' . $file);
//                        // если не выбрали область выделения
//                        if ($data['w'] == 0 && $data['h'] == 0) {
//                            $data['w'] = 600;
//                            $aspect = round($size[1] / $size[0], 3);
//                            $data['h'] = $size[1] + ((600 - $size[0]) * $aspect);
//                            $data['crop_x'] = 0;
//                            $data['crop_y'] = 0;    
//                        }
//
//                        $koef = $size[0] / 600;
//                        $aspect = round($size[1] / $size[0], 3);
//                        $koef_h = $size[1] / floor($size[1] + ((600 - $size[0]) * $aspect));
//                        $data['w'] = floor($data['w'] * $koef);
//                        $data['h'] = floor($data['h'] * $koef);
//                        $data['crop_x'] = floor($data['crop_x'] * $koef);
//                        $data['crop_y'] = floor($data['crop_y'] * $koef_h);

                        //if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/167_' . $file, 167, 167, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                        if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/167_' . $file, 167, 167, true)) {
                            throw new Exception('Ошибка при уменьшении квадрата на 167');
                        } 
                        
                        //if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/119_' . $file, 119, 119, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                        if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/119_' . $file, 119, 119, true)) {
                            throw new Exception('Ошибка при уменьшении квадрата на 119');
                        }
                        
                        //if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/140_82_' . $file, 140, 82, true, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                        if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/140_82_' . $file, 82, 140, true)) {
                            throw new Exception('Ошибка при уменьшении изображения');
                        }    
                        
                        // populares
                        if ($data['w'] > $data['h']) $cutter = false; else $cutter = true;
                        
                        //if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/168_122_' . $file, 168, 122, $cutter, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                        if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/168_122_' . $file, 122, 168, true)) {
                            throw new Exception('Ошибка при уменьшении изображения');
                        }

                        @rename($path . '/tmp/' . $file, $path . '/post/thumbs/326_217_' . $file);    
                        
                    }

                } 
                
                // бэйдж на индекс
                if ($data['photo_index']) {
                    
                    $file = $data['photo_index'];

                    if (is_file($path . '/tmp/' . $file)) {
                        @rename($path . '/tmp/' . $file, $path . '/post/thumbs/index_' . $file);                        
                    }

                }
                
                // основной бэйдж
                if ($data['photo']) {
                    
                    $file = $data['photo'];

                    if (is_file($path . '/tmp/' . $file)) {
                        
                        $size = getimagesize($path . '/tmp/' . $file);    
                        // video poster
                        if ($data['rubric_id'] == 1) {
                            if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/video_' . $file, 420, 714, true)) {
                                throw new Exception('Ошибка при уменьшении большой картинки');
                            }
                            @unlink($path . '/tmp/' . $file);
                        } else {
                            if ($size[0] > 884) {
                                //if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/' . $file, 465, 700, true)) {
                                if (!$this->_helper->uploader->resizeToPath($path . '/tmp/' . $file, $path . '/post/' . $file, 884, true, true)) {
                                    throw new Exception('Ошибка при уменьшении большой картинки');
                                }
                                @unlink($path . '/tmp/' . $file);
                            } else {
                                @rename($path . '/tmp/' . $file, $path . '/post/' . $file);
                            }    
                        }
                        
                    }

                }                
                
                
                //throw new Exception($data['photo']);
                // галерея
                if ($data['photos']) {
                    $files = json_decode( $data['photos'], true );
                    foreach( $files as $key=>$file ) {
                        if (!is_null($file)) {
                            if (is_file($path.'/tmp/'.$file)) {
                                rename($path.'/tmp/'.$file, $path.'/post/'.$file);
                            }
                        } else {
                            unset($files[$key]);
                        }
                    }
                    if (count($files) > 0)
                        $data['photos'] = json_encode( array_values($files) );
                    else
                        unset($data['photos']);
                } else {
                    unset($data['photos']);
                }
                
                if (!$this->view->isGodOfProject()) $data['status'] = 0;
                
                $row = $postManager->createRow($data);

                $postId = $row->save();

                //App_Cache_Cache::getInstance()->remove("getPost");

                if ($this->view->isGodOfProject()) {
                    $this->_helper->FlashMessenger(array('system_ok' => 'Publicación publicado'));
                    $this->_redirect($row->getUrl());
                } else {
                    $this->_helper->FlashMessenger(array('system_ok' => 'Publicación enviado para revisión'));
                    $this->sendEmail($row->getUrl(), $row->name);
                    $this->_redirect( $this->view->url(array(''), 'lents') );
                }
                
            } else {
                //throw new Exception();
                $form->setDefaults($data);
            }
        }

        $this->view->addPostForm = $form;
        $this->view->feedId = $feedId;
    }

    public function editAction()
    {
        
        if (!$this->view->isGodOfProject()) $this->_redirect( $this->view->url(array(''), 'lents') );
        
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface('1.8.0');
        //$this->_helper->initInterfaceUI();
        
        $this->view->headScript()
                ->appendFile('/resources/uploadify/jquery.uploadify.js')                
                ->appendFile('/resources/imperavi/redactor.js')  // https://github.com/yiiext/imperavi-redactor-widget/tree/master/assets
                ->appendFile('/resources/imperavi/lang/es.js')
                ->appendFile('/js/singleUploader.js')
                //->appendFile('/js/jcrop/jquery.Jcrop.min.js')
                ->appendFile('/js/photoUploader.js') 
                ->appendFile('/js/charCount.js ')
                ;
                
        
       if ($this->view->isGodOfProject()) {               
           $this->view->headScript()               
                ->appendFile('/js/post/newpost.js');
       } else {
           $this->view->headScript()               
                ->appendFile('/js/post/user_newpost.js');
       }
                
//                ->appendFile( '/resources/everslider/js/jquery.everslider.min.js' )
//                ->appendFile( '/resources/everslider/js/jquery.easing.1.3.js' )
                
        
//        if ($this->view->isGodOfProject())
//            $this->view->headScript()
//                ->appendFile('/resources/chosen/chosen.jquery.min.js');
        
        $this->view->headLink()
                ->appendStylesheet('/resources/imperavi/redactor.css')
                //->appendStylesheet('/css/jcrop/jquery.Jcrop.css')
                ->appendStylesheet('/resources/uploadify/uploadify.css')
                //->appendStylesheet('/resources/everslider/css/everslider.css')
                ;
        
//        if ($this->view->isGodOfProject())
//            $this->view->headLink()
//                ->appendStylesheet('/resources/chosen/chosen.css');
        
        $identity = $this->view->getUserByIdentity();
        if (!is_null($identity)) {
            $user_id = $identity->id;
        } else {
            $this->_helper->FlashMessenger(array('system_message' => 'Por favor, inicie sesión'));
            $this->_redirect('/signin');
        }
        
        $table = new Events_Model_Table_Events();
        // рэндомные концерты
        $concerts = $table->getConcertsToday(); 
        if (count($concerts) > 6) {
            srand((float) microtime() * 10000000);
            $random_concerts = array_rand($concerts, 6);
            $items = array();
            foreach ($random_concerts as $key=>$concert)
                $items[$key] = $concerts[$concert];
            
            $this->view->concerts = $items;
            unset($items);
        } else {
            $this->view->concerts = $concerts;
        }
        
        $feedManager = new Feed_Model_Table_Feed();
        $rubricManager = new Feed_Model_Table_Rubric();

        //$item = $feedManager->find($feedId)->current();
        //$rubricId = $item->getRubric()->id;

        $userManager = new Application_Model_Table_Users();
        $companyManager = new Companies_Model_Table_Companies();
        
        $form = new Post_Form_CreatePost();
        $form->setTemplate('/forms/edit_post');
        
        // боссам можно выбирать любую контору
        if ($this->view->isGodOfProject()) {
            $userCompanies = $companyManager->getCompanies(0,0,0);
            // iframe только для боссов
            //$form->getElement('post')->addFilter(new Zend_Filter_StripTags(array('allowTags' => array('li', 'b', 'i', 'strong', 'h1' => array('style', 'class'), 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'div' => array('style', 'class'), 'span' => array('style'), 'p' => array('style', 'class'), 'img' => array('src', 'title', 'width', 'height'), 'video' => array('src', 'class', 'id', 'width', 'height'), 'iframe' => array('src', 'width', 'height', 'frameborder'), 'a' => array('href', 'title'), 'br', 'em', 'table', 'tbody', 'tr', 'td')) ) );
        } else {
            $user = $userManager->find($user_id)->current();
            $userCompanies = $user->getCompanies();
            $form->getElement('post')->addFilter(new Zend_Filter_StripTags(array('allowTags' => array('li', 'b', 'i', 'strong', 'h1' => array('style', 'class'), 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'div' => array('style', 'class'), 'span' => array('style', 'class'), 'p' => array('style'), 'img' => array('src', 'title', 'width', 'height'), 'a' => array('href', 'title'), 'br', 'em', 'table', 'tbody', 'tr', 'td')) ) );
        }
        
        if (count($userCompanies) == 0)
            $form->removeElement('company_id');
        else
            $form->getElement('company_id')->setMultiOptions(array('0' => '- Elección de empresa -') + $userCompanies);

        $postId = (int) $this->_getParam("postId");
        $table = new Post_Model_Table_Post();
        $rows = $table->find($postId);
        if (count($rows) > 0) {
            $post = $rows->current();
        } else {
            throw new Exception('Desconocido identificador поста');
        }  
        
        //$this->view->popular_posts = $table->getPosts(null, 0, 10, false);
        
        $author = $post->getUser();
        $identity = $this->view->getUserByIdentity();
        if (!is_null($author) || $this->view->isGodOfProject()) :                            
            if ((!is_null($identity) && $author->id == $identity->id) || $this->view->isGodOfProject())  :

                $request = $this->getRequest();
                if ($request->isPost()) {
                    //проверка валидации формы
                    $data = $request->getPost();
                    if ($form->isValid( $data )) {
                        
                        $remoteTypograf = new Qlick_RemoteTypograf('UTF-8');
                        $remoteTypograf->htmlEntities();
                        $remoteTypograf->br (false);
                        $remoteTypograf->nobr (3); 
                        $remoteTypograf->quotA ('laquo raquo');
                        $remoteTypograf->quotB ('bdquo ldquo');
                        
                        $path = Zend_Registry::get('upload_path');
                        
                        $upload = new Zend_File_Transfer_Adapter_Http();
                        $upload->setDestination(realpath(dirname('.')).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR);
                        $files  = $upload->getFileInfo();
                        //die(print_r($files));
                        
                        foreach($files as $file => $fileInfo) {
                            if ($upload->isUploaded($file)) {
                                if ($upload->isValid($file)) {
                                    
                                    try {
                                        $upload->receive($file);                            
                                    } catch (Zend_File_Transfer_Exception $e) {
                                        echo $e->message();
                                    }
                                    
                                 }
                            }
                        }
                        
                        $data = $form->getValues();
                        
                        $name = $upload->getFileName('video'); 
                        if (!is_array($name)) {                            
                            $ext = strtolower(substr(strrchr($name, '.'), 1));
                            $newName = time() . md5($name) . '.' . $ext;

                            $filterFileRename = new Zend_Filter_File_Rename(array('target' => $path . '/post/video/' . $newName, 'overwrite' => true));
                            $filterFileRename -> filter($name);
                            $data['video'] = $newName;
                            @unlink($path.'/post/video/' . $post->video);
                        } else {
                            unset($data['video']);
                        }
                        
                        $name = $upload->getFileName('video_webm'); 
                        if (!is_array($name)) {                            
                            $ext = strtolower(substr(strrchr($name, '.'), 1));
                            $newName = time() . md5($name) . '.' . $ext;

                            $filterFileRename = new Zend_Filter_File_Rename(array('target' => $path . '/post/video/' . $newName, 'overwrite' => true));
                            $filterFileRename -> filter($name);
                            $data['video_webm'] = $newName;
                            @unlink($path.'/post/video/' . $post->video_webm);
                        } else {
                            unset($data['video_webm']);
                        }
                        
                        $data['name'] = str_replace(' - ', ' — ', $data['name']);
                        $data['description'] = str_replace(' - ', ' — ', $data['description']);
                        $data['post'] = $remoteTypograf->processText($data['post']);
                        
                        // обработка тегов
                        $tags = explode(',', $data['tags']);
                        foreach ($tags as $key=>$tag) {
                            $tags[$key] = trim($tag);
                        }                        
                        $data['tags'] = implode(',', $tags); //preg_replace('/\s/', '', $data['tags']);
                        
                        if ($data['gallery_id'] == 0) unset($data['gallery_id']);
                        
                        if (!empty($data['photo_list']) && $data['photo_list'] != $post->photo_list) {

                            $file = $data['photo_list'];

                            if (is_file($path . '/tmp/' . $file)) {

                                // определим размер
                                $size = getimagesize($path . '/tmp/' . $file);

                                //if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/167_' . $file, 167, 167, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                                if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/167_' . $file, 167, 167, true)) {
                                    throw new Exception('Ошибка при уменьшении квадрата на 167');
                                } 

                                //if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/119_' . $file, 119, 119, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                                if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/119_' . $file, 119, 119, true)) {
                                    throw new Exception('Ошибка при уменьшении квадрата на 119');
                                }

                                //if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/140_82_' . $file, 140, 82, true, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                                if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/140_82_' . $file, 82, 140, true)) {
                                    throw new Exception('Ошибка при уменьшении изображения');
                                }    

                                // populares
                                if ($data['w'] > $data['h']) $cutter = false; else $cutter = true;

                                //if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/168_122_' . $file, 168, 122, $cutter, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                                if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/thumbs/168_122_' . $file, 122, 168, true)) {
                                    throw new Exception('Ошибка при уменьшении изображения');
                                }
                                
                                @rename($path . '/tmp/' . $file, $path . '/post/thumbs/326_217_' . $file);    
                                
                                // раз все загрузили, удалим старье
                                if (is_file($path . '/post/thumbs/140_82_' . $post->photo_list)) unlink($path . '/post/thumbs/140_82_' . $post->photo_list);
                                if (is_file($path . '/post/thumbs/167_' . $post->photo_list)) unlink($path . '/post/thumbs/167_' . $post->photo_list);
                                if (is_file($path . '/post/thumbs/119_' . $post->photo_list)) unlink($path . '/post/thumbs/119_' . $post->photo_list);
                                if (is_file($path . '/post/thumbs/168_122_' . $post->photo_list)) unlink($path . '/post/thumbs/168_122_' . $post->photo_list);
                                if (is_file($path . '/post/thumbs/326_217_' . $post->photo_list)) unlink($path . '/post/thumbs/326_217_' . $post->photo_list);

                            }

                        } 
                        
                        // бэйдж на индекс
                        if (!empty($data['photo_index']) && $data['photo_index'] != $post->photo_index) {

                            $file = $data['photo_index'];

                            if (is_file($path . '/tmp/' . $file)) {
                                @rename($path . '/tmp/' . $file, $path . '/post/thumbs/index_' . $file);   
                                if (is_file($path . '/post/thumbs/index_' . $post->photo_index)) unlink($path . '/post/thumbs/index_' . $post->photo_index);
                            }

                        }

                        // основной бэйдж
                        if (!empty($data['photo']) && $data['photo'] != $post->photo) {

                            $file = $data['photo'];

                            if (is_file($path . '/tmp/' . $file)) {

                                $size = getimagesize($path . '/tmp/' . $file);    
                                // video poster
                                if ($data['rubric_id'] == 1) {
                                    if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/video_' . $file, 420, 714, true)) {
                                        throw new Exception('Ошибка при уменьшении большой картинки');
                                    }
                                    @unlink($path . '/tmp/' . $file);
                                } else {
                                    if ($size[0] > 884) {
                                        //if (!$this->_helper->uploader->resizeWithCropToPath($path . '/tmp/' . $file, $path . '/post/' . $file, 465, 700, true)) {
                                        if (!$this->_helper->uploader->resizeToPath($path . '/tmp/' . $file, $path . '/post/' . $file, 884, true, true)) {
                                            throw new Exception('Ошибка при уменьшении большой картинки');
                                        }
                                        @unlink($path . '/tmp/' . $file);
                                    } else {
                                        @rename($path . '/tmp/' . $file, $path . '/post/' . $file);
                                    }    
                                }
                                
                                // раз все загрузили, удалим старье
                                if (is_file($path . '/post/' . $post->photo)) unlink($path . '/post/' . $post->photo);
                                if (is_file($path . '/post/video_' . $post->photo)) unlink($path . '/post/video_' . $post->photo);

                            }

                        }                        
                        
                        //throw new Exception($data['photo']);
                        // галерея
                        if ($data['photos'] && $data['photos'] != $post->photos) {
                            $files = json_decode( $data['photos'] );
                            $photos = $post->getPhotos();
                            if (!is_null($photos)) 
                                $delete_photos = array_diff($photos, $files); // найдем разницу
                            else
                                $delete_photos = array();
                            
                            foreach( $files as $key=>$file ) {
                                if (!is_null($file)) {
                                    if (is_file($path.'/tmp/'.$file)) {
                                        rename($path.'/tmp/'.$file, $path.'/post/'.$file);
                                    } else {
                                        unset($files[$key]);
                                    }
                                } else {
                                    unset($files[$key]);
                                }
                            }

                            if (!is_null($photos)) 
                                $data['photos'] = json_encode( array_values($files + array_diff($photos, $delete_photos)) ); // новые фотки + актуальные старые
                            else
                                $data['photos'] = json_encode( array_values($files) ); // новые фотки
                                //
                            // раз все загрузили, удалим старье
                            //$photos = $post->getPhotos();                        
                            foreach ($delete_photos as $photo) {                            
                                if ( is_file($path.'/post/'.$photo)) unlink($path.'/post/'.$photo);            
                            } 

                        } else {
                            unset($data['photos']);
                        }
                        
                        // если опубликован пост юзера, то известим
                        if ($post->status == 0 && $data['status'] == 1 && $author->id != $identity->id) $this->sendAcceptEmail($author);
                        
                        $post->setFromArray($data);
                        $postId = $post->save();
                        // очистим память
                        unset($data);
                        unset($delete_photos);
                        unset($photo);
                        unset($photos);
                        unset($files);                

                        $this->_helper->FlashMessenger(array('system_ok' => 'Publicado!'));
                        
                        $this->_redirect($post->getUrl());
                        
                        return;
                    } else {
                        $form->setDefaults($data);
                    }
                }

                //заполняем форму при редактировании
                $this->view->post = $post;
                $form->setDefaults( $post->toArray() );

                $this->view->addPostForm = $form;
                $this->view->postId = $postId;
                
            else :
                $this->_helper->FlashMessenger(array('system_message' => 'Вы не автор данного поста'));                
                $this->_redirect($post->getUrl());
            endIf;
            
        endIf;
        
    }

    public function viewAction()
    {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
        
        $this->view->headScript()
                ->appendFile( '/js/comments.js')
                ->appendFile( '/js/events/walks.js' )
                ;
        
        $this->view->headLink()
                ->appendStylesheet('/css/posts.css');
        
        $postId = (int) $this->_getParam("postId");
        if ($this->view->getLogged()) {
            $commentForm = $this->_helper->comments->commentform("post", $postId);
            $this->view->commentForm = $commentForm;
        }
        $commentsList = $this->_helper->comments->commentslist("post", $postId);
        $this->view->commentsList = $commentsList;
        $userId = Zend_Auth::getInstance()->getIdentity();
        
        $post_url = NULL;
        if ($postId == 0) {
            $filter = new Zend_Filter_StripTags();
            $post_url = $filter->filter($this->_getParam("post_url"));            
        }
        
        if ($postId != 0 || !is_null($post_url)) {

            $postManager = new Post_Model_Table_Post();
            
            if ($postId > 0)
                $posts = $postManager->find($postId);
            else
                $posts = $postManager->getPostByUrl($post_url);
            
            if (count($posts) > 0) {
                
                if ($postId > 0)
                    $post = $posts->current()->count();
                else
                    $post = $posts->count();
                    
                $this->view->post = $post;
                $this->view->similar_posts = $postManager->getPostsByRubric($post->rubric_id, 3, 0, array($post->id));
                
                if (!is_null($post->photos)) {                    
                    $this->view->headScript()
                        ->appendFile( '/js/post/view.js')
                        ->appendFile( '/resources/ulslide/jquery.ulslide.js');
                }
                
                $rubricId = $post->rubric_id;
//                if ($rubricId == 1) {
//                    $this->view->headScript()->appendFile( '/js/mediaelement-and-player.min.js' );
//                    $this->view->popular_posts = $postManager->getPopularVideos(10, 30);
//                } else {
//                    //$this->view->popular_posts = $postManager->getPopularPosts(10, 30);
//                    $eventManager = new Events_Model_Table_Events();
//                    $this->view->popular_events = $eventManager->getPopularEvents(10);
//                }
                $this->view->rubricId = $rubricId;
                
                if ($post->status == App_Data_Constants::$POST_STATUS_IS_REMOVE)
                    $this->_redirect($this->view->url(array(), 'lents'));
                
                $this->view->doctype('XHTML1_RDFA');
                $this->view->headTitle()->append($post->name);
                $this->view->headTitle()->append($post->getRubric());
                if ($rubricId != 1) $this->view->headTitle()->append('Revista');
                
                if (!is_null($post->description))
                    $this->view->headMeta()->setName('description', $post->description);
                if (!is_null($post->tags))
                    $this->view->headMeta()->setName('keywords', $post->tags);

                $this->view->headMeta()->setProperty('og:title', $post->name);
                $this->view->headMeta()->setProperty('og:description', $post->description);
                $this->view->headMeta()->setProperty('og:url', 'http://' . Zend_Registry::get('server_name') . $post->getUrl());                                
                $this->view->headMeta()->setProperty('og:image', 'http://' . Zend_Registry::get('server_name') . '/files/post/' . $post->getMainPhoto());
                
                $table = new Events_Model_Table_Events();
                // рэндомные концерты
                $concerts = $table->getConcertsToday(); 
                if (count($concerts) > 6) {
                    srand((float) microtime() * 10000000);
                    $random_concerts = array_rand($concerts, 6);
                    $items = array();
                    foreach ($random_concerts as $key=>$concert)
                        $items[$key] = $concerts[$concert];

                    $this->view->concerts = $items;
                    unset($items);
                } else {
                    $this->view->concerts = $concerts;
                }
                
            } else {
                $this->_helper->FlashMessenger(array('system_message' => 'No hay publicación'));
                $this->_redirect( $this->view->url(array(''), 'lents') );    
            }
        } else {
            $this->_helper->FlashMessenger(array('system_message' => 'No hay publicación'));
            $this->_redirect( $this->view->url(array(''), 'lents') );    
        }
    }

    public function deleteAction()
    {
        $postManager = new Post_Model_Table_Post();
        $postId = (int) $this->_getParam('postId');
        
        $posts = $postManager->find($postId);
        if (count($posts) > 0) {
            $post = $posts->current();
            $user = Zend_Auth::getInstance()->getIdentity();
            if (($user->id == $post->user_id) || $this->view->isGodOfProject()) {
                $photo = $post->photo;
                $path = Zend_Registry::get('upload_path');
                if ( is_file($path.'/post/'.$photo)) unlink($path.'/post/'.$photo);
                if ( is_file($path.'/post/thumbs/index_'.$photo)) unlink($path.'/post/thumbs/index_'.$photo);
                if ( is_file($path.'/post/video_'.$photo)) unlink($path.'/post/video_'.$photo);
                if ( is_file($path.'/post/thumbs/167_'.$photo)) unlink($path.'/post/thumbs/167_'.$photo);
                if ( is_file($path.'/post/thumbs/119_'.$photo)) unlink($path.'/post/thumbs/119_'.$photo);
                if ( is_file($path.'/post/thumbs/140_82_'.$photo)) unlink($path.'/post/thumbs/140_82_'.$photo);                
                if ( is_file($path.'/post/thumbs/242_175_'.$photo)) unlink($path.'/post/thumbs/242_175_'.$photo);
                if ( is_file($path.'/post/thumbs/326_217_'.$photo)) unlink($path.'/post/thumbs/326_217_'.$photo);
                if ( is_file($path.'/post/thumbs/168_122_'.$photo)) unlink($path.'/post/thumbs/168_122_'.$photo);

                $post->delete();
                //$post->setFromArray(array('status' => App_Data_Constants::$POST_STATUS_IS_REMOVE));
                //$post->save();
//                
                $this->_helper->FlashMessenger(array('system_message' => 'Publicación eliminado'));
//                $this->_redirect( $this->view->url(array('feedId' => $feedId, 'action' => 'view'), 'lent') );
//                return;
            }
        }
        $this->_redirect( $this->view->url(array(''), 'lents') );
    }
    
    public function toindexAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        $postId = (int) $request->getParam ( 'postId' );
        $table = new Post_Model_Table_Post();
        $rows = $table->find( $postId );
        if ( count($rows)>0 ) {
            $table->nullindex();
            $row = $rows->current();
        } else {
            throw new Exception('Desconocido identificador новости');
        }
        
        // 1 = index
        // 2 = fun
        // 3 = top_left
        // 4 = top_right
        
        $row->is_content = $row->is_content == 1 ? 0 : 1;
        $row->save();
        $this->getResponse()->setBody( $row->is_content );        
    }
    
    public function tofunAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        $postId = (int) $request->getParam ( 'postId' );
        $table = new Post_Model_Table_Post();
        $rows = $table->find( $postId );
        if ( count($rows)>0 ) {
            $table->nullfun();
            $row = $rows->current();
        } else {
            throw new Exception('Desconocido identificador новости');
        }
        
        // 1 = index
        // 2 = fun
        // 3 = top_left
        // 4 = top_right
        
        $row->is_content = $row->is_content == 2 ? 0 : 2;
        $row->save();
        $this->getResponse()->setBody( $row->is_content );        
    }
    
    public function totopleftAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        $postId = (int) $request->getParam ( 'postId' );
        $table = new Events_Model_Table_Events();
        $table->nulltopleft();
        $table = new Post_Model_Table_Post();
        //$table->nulltopleft();
        $rows = $table->find( $postId );
        if ( count($rows)>0 ) {            
            $row = $rows->current();
        } else {
            throw new Exception('Desconocido identificador новости');
        }
        
        // 1 = index
        // 2 = fun
        // 3 = top_left
        // 4 = top_right
        
        $row->is_content = $row->is_content == 3 ? $row->is_content = 0 : $row->is_content = 3;
        $row->save();
        $this->getResponse()->setBody( $row->is_content );        
    }
    
    public function totoprightAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        $postId = (int) $request->getParam ( 'postId' );
        $table = new Events_Model_Table_Events();
        $table->nulltopright();
        $table = new Post_Model_Table_Post();
        //$table->nulltopright();
        $rows = $table->find( $postId );
        if ( count($rows)>0 ) {            
            $row = $rows->current();
        } else {
            throw new Exception('Desconocido identificador новости');
        }
        
        // 1 = index
        // 2 = fun
        // 3 = top_left
        // 4 = top_right
        
        $row->is_content = $row->is_content == 4 ? $row->is_content = 0 : $row->is_content = 4;
        $row->save();
        $this->getResponse()->setBody( $row->is_content );        
    }    

    public function likeAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()
            ->setHeader('Content-Type', 'text/html; charset=utf-8');

        $request = $this->getRequest();

        $postId = (int) $request->getParam('postId');
        if (empty($postId))
            throw new Exception("Идентификатор поста не задан");

        $table = new Post_Model_Table_Post();
        $rows = $table->find($postId);
        if (count($rows) > 0) {
            $row = $rows->current();
        } else {
            throw new Exception('Desconocido identificador поста');
        }

        $ratingVal = (int) $request->getParam('rating');

        // User
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (is_null($identity))
            throw new Exception('Вы не авторизованы');

        $user_id = $identity->id;

        $like = $row->isLiked($user_id);
        $table = new Application_Model_Table_Rating();
        $like = $table->fetchRow($table->select()
                                ->where('object_id = ?', $postId)
                                ->where('object_type = "post"')
                                ->where('user_id = ?', $user_id)
        );        
        
        if(!$like)
        {
            //$row->rating = $ratingVal == 1 ? $row->rating + 1 : $row->rating - 1;
            //$row->save();
            $rowRating = $table->createRow();
            $rowRating->user_id = $user_id;
            $rowRating->object_id = $postId;
            $rowRating->object_type = 'post';
            if ($ratingVal == 1) {
                $rowRating->good = 1;
            } else {
                $rowRating->poor = 1;
            }
            $rowRating->save();
            
            $row->calcRating();
            
            // обновим карму автору
            $author = $row->getUser();
            $author->calcrating();            
        }
        $this->getResponse()->setBody( $row->rating );        
    }

    public function favoriteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()
            ->setHeader('Content-Type', 'text/html; charset=utf-8');

        $request = $this->getRequest();
        
        $postId = (int) $request->getParam("postId");
        if (empty($postId))
            throw new Exception("Идентификатор поста не задан");

        $table = new Post_Model_Table_Post();
        $rows = $table->find($postId);
        if (count($rows) > 0) {
            $row = $rows->current();
        } else {
            throw new Exception('Desconocido identificador поста');
        }
        
        // User
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (is_null($identity))
            throw new Exception('Вы не авторизованы');

        $user_id = $identity->id;
        $table = new Post_Model_Table_Favorites();
        if(!is_null($postFavorites = $row->isFavorite( $user_id )))
        {
            $favorites = $postFavorites->delete();
            $this->getResponse()->setBody(0);
        }
        else{
            $row = $table->createRow(array("user_id"=>$user_id, "post_id"=>$postId));
            $row->save();
            $this->getResponse()->setBody(1);
        }

    }

    public function uploadAction()
    {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();        
        
        if ( !is_null( $this->getRequest()->getParam('editor') ) ) {
            $name = $this->_helper->uploader(Zend_Registry::get('upload_path') . "/post/editor");
            $path = Zend_Registry::get('upload_path') . "/post/editor/$name";
            if (!is_file($path)) throw new Exception( 'Файл не загружен' );
            
            $size = getimagesize($path);
            if ($size[0] > 884) {
                if (!$this->_helper->uploader->resize( $path, 884, true, false )) {
                    throw new Exception( 'Ошибка при уменьшении изображения' );
                }
                $size = getimagesize($path);
            }            
            
            $path = "/files/post/editor/" . $name;
                        
            $img_param = array();
            $img_param['filelink'] = $path;
            $img_param['width'] = $size[0];
            $img_param['height'] = $size[1];
            
            $this->getResponse()->setBody( stripslashes(json_encode($img_param)) ); 
            
        } else {
            $name = $this->_helper->uploader(Zend_Registry::get('upload_path') . "/tmp");
            $path = Zend_Registry::get('upload_path') . "/tmp/$name";

            if ( ! is_file( $path ) ) throw new Exception( 'Файл не загружен' );
            $size = getimagesize($path);
            if ($size[0] >= $size[1]) {
                if ($size[0] < 800) return false;
                if ($size[0] > 800)
                    if ( ! $this->_helper->uploader->resize( $path, 800, true, false ) ) {
                        throw new Exception( 'Ошибка при уменьшении изображения' );
                    }
            } else {
                if ($size[1] < 600) return false;
                if ($size[1] > 600)
                    if ( ! $this->_helper->uploader->resize( $path, 600, false, false ) ) {
                        throw new Exception( 'Ошибка при уменьшении изображения' );
                    }            
            } 
            
            $this->getResponse()->setBody( $name );
        }

    }
    
    public function indexbageuploadAction()
    {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();        
        
        $name = $this->_helper->uploader(Zend_Registry::get('upload_path') . "/tmp");
        $path = Zend_Registry::get('upload_path') . "/tmp/$name";

        if ( ! is_file( $path ) ) throw new Exception( 'Файл не загружен' );
//        $size = getimagesize($path);
//        if ($size[0] < 269) return false;
//        
//        if ($size[0] > 269) {
//            if (!$this->_helper->uploader->resize($path, 269, true, true)) {
//                throw new Exception('Ошибка при уменьшении изображения');
//            }
//        }

        //$this->getResponse()->setBody( $name );
        $data = array();
        $data['name'] = $name;                  
//        $data['width'] = floor(326 / ($size[0] / 600));
//        $aspect = round($size[1] / $size[0], 3);
//        $data['height'] = floor(217 / ($size[1] / $size[1] + ((600 - $size[0]) * $aspect)) );

        $this->getResponse()->setBody( json_encode($data) );

    }
    
    public function bageuploadAction()
    {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();        
        
        $name = $this->_helper->uploader(Zend_Registry::get('upload_path') . "/tmp");
        $path = Zend_Registry::get('upload_path') . "/tmp/$name";

        if ( ! is_file( $path ) ) throw new Exception( 'Файл не загружен' );
        $size = getimagesize($path);
        if ($size[0] < 269) return false;
        
        if ($size[0] > 269) {
            if (!$this->_helper->uploader->resize($path, 269, true, true)) {
                throw new Exception('Ошибка при уменьшении изображения');
            }
        }

        //$this->getResponse()->setBody( $name );
        $data = array();
        $data['name'] = $name;                  
//        $data['width'] = floor(326 / ($size[0] / 600));
//        $aspect = round($size[1] / $size[0], 3);
//        $data['height'] = floor(217 / ($size[1] / $size[1] + ((600 - $size[0]) * $aspect)) );

        $this->getResponse()->setBody( json_encode($data) );

    }    
    
    public function mainbageuploadAction()
    {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();        
        
        $name = $this->_helper->uploader(Zend_Registry::get('upload_path') . "/tmp");
        $path = Zend_Registry::get('upload_path') . "/tmp/$name";

        if ( ! is_file( $path ) ) throw new Exception( 'Файл не загружен' );
        $size = getimagesize($path);
        if ($size[0] < 884) return false;

        //$this->getResponse()->setBody( $name );
        $data = array();
        $data['name'] = $name;                  
//        $data['width'] = floor(326 / ($size[0] / 600));
//        $aspect = round($size[1] / $size[0], 3);
//        $data['height'] = floor(217 / ($size[1] / $size[1] + ((600 - $size[0]) * $aspect)) );

        $this->getResponse()->setBody( json_encode($data) );

    }    
    
    public function fileuploadAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $name = $this->_helper->uploader->fileupload(Zend_Registry::get('upload_path') . "/post/editor/video");
        $path = Zend_Registry::get('upload_path') . "/post/editor/video/$name";
        if ( ! is_file( $path ) ) return NULL; //throw new Exception( 'Файл не загружен' ); 
        $content = '<video src="/files/post/editor/video/'.$name.'" width="100%" height="420" preload="none" poster="/zeta/prevideo.jpg"></video>';
       // $content = '<video class="jwplayer" src="/files/post/editor/video/'.$name.'" height="400" id="container" width="612"></video>'; //  files/post/editor/video/$name";
        $this->getResponse()->setBody($content);
        
    }

    public function postsbytagAction()
    {
//        $this->getResponse()
//                ->setHeader('Content-Type', 'text/html; charset=utf-8' );
        
        $this->_helper->initInterface();
        $this->view->headScript()
            ->appendFile( '/js/events/walks.js' )
            //->appendFile( '/js/feed/subscribe.js' )
            //->appendFile( '/js/feed/main.js' )
            ;
        
        $this->view->headLink()
                ->appendStylesheet('/css/posts.css');
        
        $filter = new Zend_Filter_StripTags();
        $tag = $filter->filter((string) $this->_getParam("tag"));
        
        $table = new Post_Model_Table_Post();
        //$this->view->popular_posts = $table->getPopularPosts(10, 30);
        
        $this->view->tag = $tag;
        
        if (trim($tag) != "")
        {   
            $this->view->headTitle()->append($tag);
            $posts = $table->getCatalogPosts(0, 0, 0, $tag);
            $this->view->posts = $posts;
        }
        
        $table = new Events_Model_Table_Events();
        // рэндомные концерты
        $concerts = $table->getConcertsToday(); 
        if (count($concerts) > 6) {
            srand((float) microtime() * 10000000);
            $random_concerts = array_rand($concerts, 6);
            $items = array();
            foreach ($random_concerts as $key=>$concert)
                $items[$key] = $concerts[$concert];

            $this->view->concerts = $items;
            unset($items);
        } else {
            $this->view->concerts = $concerts;
        }
        
    }

}

