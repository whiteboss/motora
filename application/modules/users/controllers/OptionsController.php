<?php

class Users_OptionsController extends Zend_Controller_Action
{

    protected $_user;

    public function preDispatch()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $user = $this->_helper->userProfile($auth->getIdentity()->id);
            $this->_user = $auth->getIdentity();
        } else {
            $this->_helper->FlashMessenger(array('system_message' => 'Вы не авторизованы'));
            $this->_redirect($this->view->url(array(''), 'signin'));
        }
    }

    public function init()
    {
        $this->_helper->initInterface();
    }

    public function indexAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->view->headScript()
                ->appendFile( '/js/jquery.datepicker.min.js' )
                ->appendFile( '/js/date.js' )
                ->appendFile( '/js/avatarUploader.js' )
                ->appendFile( '/js/users/profile.js' )
                ->appendFile( '/js/jcrop/jquery.Jcrop.min.js' )
                ->appendFile( '/resources/uploadify/jquery.uploadify.js' );
                //->appendFile( '/js/users/uploader.js' );
        
        $this->view->headLink()
                ->appendStylesheet( '/css/datePicker.css' )
                ->appendStylesheet( '/css/jcrop/jquery.Jcrop.css' )
                ->appendStylesheet( '/resources/uploadify/uploadify.css' );
        
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (!is_null($identity)) {
            $userId = $identity->id;
        } else {
            $this->_helper->FlashMessenger(array('system_message' => 'Пожалуйста авторизируйтесь'));
            $this->_redirect('/signin');
        }

        $this->_helper->initInterface();
        if ($userId) {
            $user = $this->_helper->userProfile($this->_user->id);
            //$this->view->messages = $user->getOptions();
        }

        $form = new Users_Form_EditProfile();        
        $form->setTemplate('/forms/profileEdit');
        $users = new Application_Model_Table_Users();

        $rows = $users->find($userId);
        if (!$rows) {
            throw new Exception("Desconocido identificador $userId");
        }
        $row = $rows->current();        
        
        // доступность email
        if (!is_null($row->identity) && is_null($row->email)) {
            $form->getElement('email')->setRequired(false);
//            $form->getElement('email')->clearDecorators();
//            $form->getElement('email')->setDecorators(array(array('ViewScript', array(
//                    'viewScript'    => 'prof_input.phtml',
//                    'input_class'   => ' fgray' 
//                )))
//            );
        }
        
        // MUSICS
        $musicManager = new Music_Model_Table_Musics();
        $items = array();
        $most_viewed = $musicManager->getMostViewedMusics(7);
        if (count($most_viewed) >= 7) {
            srand((float) microtime() * 10000000);
            $random_key = array_rand($most_viewed);
            $items[] = $most_viewed[$random_key];
            $this->view->most_viewed_music = $items[0];
        } else {
            foreach ($most_viewed as $key=>$music)
                $items[] = $music; 

            $this->view->most_viewed_music = $items[0];    
        }

//        $musics = array_diff($musicManager->getMusics(0, 0, 10), array($items[0]));
//        if (count($musics) > 7) {
//            srand((float) microtime() * 10000000);
//            $random_musics = array_rand($musics, 7);
//            $items = array();
//            foreach ($random_musics as $key=>$music)
//                $items[$key] = $musics[$music];
//
//            $this->view->musics = $items;
//            unset($items);
//        } else {
//            $this->view->musics = $musics;
//        } 
        
        // EVENTS
        // right block
        $eventManager = new Events_Model_Table_Events();
        $items = array();
        $events = $eventManager->getComingEvents();
        if (count($events) > 5) {
            srand((float) microtime() * 10000000);
            $random_events = array_rand($events, 5);
            $items = array();
            foreach ($random_events as $key=>$event)
                $items[$key] = $events[$event];

            $this->view->r_events = $items;
            unset($items);
        } else {
            $this->view->r_events = $events;
        }
        // ------
        
        $form->setDefaults($row->toArray());
        
        $request = $this->getRequest();
        //$data    = $this->_getAllParams();
        if ($request->isPost()) {
            //проверка валидации формы
            $data = $request->getPost();            
            if ($form->isValid($data)) {
                
                if (!empty($data['date_of_birth']) && !Zend_Date::isDate($data['date_of_birth'], 'mm.dd.YYYY')) {
                    $this->_helper->FlashMessenger(array('system_error' => 'Неверно указана дата рождения'));
                    $this->_redirect($this->view->url(array(), 'options'));  
                    return;
                }
                
                $data = $form->getValues();
                
                if ($data['googleplus']) $data['googleplus'] = str_replace('https', 'http', $data['googleplus']);                                
                
                $changePassword = null;
                // проверка пароля если пользователь хочет его сменить
                if (!empty($data['password_new'])) {
                    $changePassword = false;
                    if ($row->password != md5(md5($row->salt).md5($data['password_old']))) {
                        $form->getElement('password_old')->setErrors(array('Вы ввели не верный пароль'));                    
                        //$form->getElement('password_old')->setErrorMessages(array('Вы ввели не верный пароль'));                    
                        //$form->getElement('password_old')->markAsError();
                    } else {
                        $changePassword = true;
                    }
                }

                // день рождения                

                $date_birth = explode('.', $data['date_of_birth']);
                $data['day_birth'] = $date_birth[0];
                $data['month_birth'] = $date_birth[1];
                $data['year_birth'] = $date_birth[2];
                
                if ($changePassword === null || $changePassword === true) {
                    unset($data['password_old']);
                    unset($data['password_new_repeat']);
                    
                    if ($changePassword === true && !empty($data['password_new'])) {
                        $salt = $users->generateSalt();
                        $data['salt'] = $salt;
                        $data['password'] = md5(md5($salt).md5($data['password_new']));
                    }
                    
                    if ($data['avatar'] && $data['avatar'] !== $row->avatar) {
                        $path = Zend_Registry::get('upload_path');   
                        $file = $data['avatar'];
                        // определим размер
                        $size = getimagesize($path . '/tmp/' . $file);
                        // если не выбрали область выделения
                        if ($data['w'] == 0 && $data['h'] == 0) {
                            $data['w'] = 600;
                            $aspect = round($size[1] / $size[0], 3);
                            $data['h'] = $size[1] + ((600 - $size[0]) * $aspect);
                            $data['crop_x'] = 0;
                            $data['crop_y'] = 0;    
                        }                        
                        
                        // если ширина меньше 600, то сделаем преобразование
                        if ($size[0] < 600) {                                
                            $koef = $size[0] / 600;
                            $aspect = round($size[1] / $size[0], 3);
                            $koef_h = $size[1] / ($size[1] + ((600 - $size[0]) * $aspect));
                            $data['w'] = floor($data['w'] * $koef);
                            $data['h'] = floor($data['h'] * $koef);
                            $data['crop_x'] = floor($data['crop_x'] * $koef);
                            $data['crop_y'] = floor($data['crop_y'] * $koef_h);
                        }

                        if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/user/' . $file, 200, 200, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                            throw new Exception('Ошибка при уменьшении изображения1');
                        }
                        
                        if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/user/119_' . $file, 119, 119, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                            throw new Exception('Ошибка при уменьшении изображения1');
                        }
                        
                        if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/user/105_' . $file, 105, 105, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                            throw new Exception('Ошибка при уменьшении изображения1');
                        }
                        
                        if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/user/64_' . $file, 64, 64, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'])) {
                            throw new Exception('Ошибка при уменьшении изображения1');
                        }
                        
                        if (!$this->_helper->uploader->resizeAvatarWithCropToPath($path . '/tmp/' . $file, $path . '/user/21_' . $file, 21, 21, false, $data['w'], $data['h'], $data['crop_x'], $data['crop_y'], false)) {
                            throw new Exception('Ошибка при уменьшении изображения1');
                        }
                        
                        @unlink($path . '/tmp/' . $file);


//                            // удалим старую фотку
//                            if (!is_null($row->avatar))
//                                unlink($path . '/user/' . $row->avatar);
//
//                            if (is_file($path.'/tmp/'.$data['avatar'])) {
//                                rename($path.'/tmp/'.$data['avatar'], $path.'/user/'.$data['avatar']);
//                                $this->_helper->uploader->resizeAvatar($path.'/user/'.$data['avatar'], 64, $data['w'], $data['h'], $data['crop_x'], $data['crop_y']);
//                            }

                        // раз все загрузили, удалим старье
                        if (is_file($path . '/user/' . $row->avatar)) @unlink($path . '/user/' . $row->avatar);
                        if (is_file($path . '/user/119_' . $row->avatar)) @unlink($path . '/user/119_' . $row->avatar);
                        if (is_file($path . '/user/105_' . $row->avatar)) @unlink($path . '/user/105_' . $row->avatar);
                        if (is_file($path . '/user/64_' . $row->avatar)) @unlink($path . '/user/64_' . $row->avatar);
                        if (is_file($path . '/user/21_' . $row->avatar)) @unlink($path . '/user/21_' . $row->avatar);
                    } 
                    
                    // email менять нельзя
                    if (!is_null($row->email)) unset($data['email']);

                    $row->setFromArray($data);
                    $row->save();

                    $this->_helper->FlashMessenger(array('system_message' => 'Los cambios se guardan'));
                    $this->_redirect($this->view->url(array('userId' => $row->id), 'profile'));
                }
            } else {                
                $form->setDefaults($data);
            }
        } else {
            if (!is_null($row->day_birth) && !is_null($row->month_birth) && !is_null($row->year_birth)) {
                $user_birthday = new Zend_Date($row->day_birth.".".$row->month_birth.".".$row->year_birth, Zend_Date::DATES, 'ru_RU');
                $form->getElement('date_of_birth')->setValue(substr($user_birthday, 0, 10));
            } else {
                //$form->getElement('date_of_birth')->setValue('не указана');    
            }
        }

        //if($row->avatar==null || trim($row->avatar)=="") $form->getElement("avatar")->setValue('globalLogo.jpg');
        $this->view->addProfileForm = $form;
    }

}

