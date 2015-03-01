<?php

/**
 * 
 * Контроллер сотрудников
 * 
 */
class Companies_EmployerController extends Zend_Controller_Action {

    /**
     * Инициализация
     * @return void
     */
    public function preDispatch() {
        
    }

    public function indexAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->_helper->initInterface();
        $cid = (int) $this->getRequest()->getParam('companyId');
        if (!empty($cid)) {
            $company = $this->_helper->companyProfile($cid);
            $this->view->active = 'employers';
            
            $this->view->headTitle()->append('Компания ' . $company->name);
            $this->view->headTitle()->append('Сотрудники');
            if (!is_null($company->description))
                $this->view->headMeta()->setName( 'description', $company->description );            
            
            $this->view->list = $company->getEmployers();
        }
    }

    /**
     * Добавление
     * @return void
     */
    public function newAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        
        $this->_helper->initInterface();
        $this->view->headScript()
                //->appendFile('/js/jquery.autocomplete.pack.js')
                ->appendFile('/js/companies/newemployer.js')
                ->appendFile('/js/charCount.js');
        
        $this->view->headLink()
                ->appendStylesheet('/css/jquery.autocomplete.css');
        
        $cid = (int) $this->getRequest()->getParam('companyId');
        $company = $this->_helper->companyProfile($cid);
        
        $owner = $company->getOwner();
        
        $identity = $this->view->getUserByIdentity();
        
        if (!is_null($owner) || $this->view->isGodOfProject()) :
            if ((!is_null($identity) && $owner->id == $identity->id) || $this->view->isGodOfProject())  :
        
                $this->view->headTitle()->append('Компания ' . $company->name);
                $this->view->headTitle()->append('Приглашение в команду');
                if (!is_null($company->description))
                    $this->view->headMeta()->setName( 'description', $company->description );        

                $form = $this->view->addform = new Companies_Form_EditEmployer();
                $form->setTemplate('/forms/new_employer');
                //$form->user->setJQueryParams(array('source' => '/companies/employer/friends/cid/' . $cid));

                $request = $this->getRequest();
                if ($request->isPost()) {
                    $data = $request->getPost();
                    if ($form->isValid($data)) {
                        $data['id_company'] = $company->id;
                        // ищем пользователя по имени и фамилии в $data['user']
                        $table = new Application_Model_Table_Users();
                        $users = $table->find($data['id_user']);
                        if (count($users) > 0) {
                            $user = $users->current();
            //                $user = $table->fetchRow($table->select()->where("CONCAT(lastname, ' ', firstname) = ?", $data['user']));
            //                if (!$user)
            //                    throw new Exception('Ошибка при попытке добавления сотрудника');
            //                $data['id_user'] = $user->id;
                            $table = new Companies_Model_Table_Employers();
                            $row = $table->createRow($data);
                            $id = $row->save();
                            $this->sendEmail($company, $user, $id);
                            $this->_helper->FlashMessenger(array('system_ok' => 'Сотруднику отправлено приглашение на e-mail'));
                            $this->_redirect($this->view->url(array('companyId' => $row->id_company), 'companyemployers'));
                            return;
                        } else {
                            $this->_helper->FlashMessenger(array('system_message' => 'Сотрудник не найден, попробуйте еще раз'));
                            $this->_redirect($this->view->url(array('companyId' => $row->id_company), 'companyemployers'));                    
                        }    

                    }
                }
                
            endIf;
        endIf;
        
        $this->_redirect($this->view->url(array('companyId' => $company->id), 'company'));    
        
    }

    /**
     * Отправляет сообщение пользователю для подтверждения
     * @param Zend_Db_Table_Row $user
     */
    protected function sendEmail($company, $user, $id_employer)
    {
        $link = 'http://' . $_SERVER['HTTP_HOST'] . $this->view->url(array('companyId' => $company->id, 'employerId' => $id_employer, 'action' => 'confirm'), 'companyemployer');
        //$link = $this->view->url(array('companyId' => $company->id, 'employerId' => $id_employer, 'action' => 'confirm'), 'companyemployer');
        //$link_view = 'http://' . $_SERVER['HTTP_HOST'] . $this->view->url(array('companyId' => $company->id, 'action' => 'profile'), 'company');
        $link_view = $this->view->url(array('companyId' => $company->id, 'action' => 'profile'), 'company');
        $mal = new Zend_Mail( 'UTF-8' );
        //Уважаемый '.$user->lastname.' '.$user->firstname.', ссылка для подтверждения: <a href="' . $link .  '">' . $link . '</a>'
        $mal->setBodyHtml('
            <table cellspacing="0" cellpadding="0" border="0" width="100%" style="background: url(http://www.qlick.ru/sprites/qmail/line.gif) repeat-x top left;">
                <tr>
                    <td width="5%">
                    </td>
                    <td width="60%">
                        <h1 style="font-family: Calibri, Arial; font-size: 24px; text-transform: uppercase; padding: 30px 0 15px 0; border-bottom: 1px #000000 solid; width: 80%;">Qlick.ru — Приглашение в компанию</h1>
                        <p style="font-family: Arial; font-size: 12px; padding: 5px 0 12px 0; line-height: 150%;">
                            Вы были приглашены в компанию '.$company->name.'. Чтобы принять приглашение, пожалуйста, пройдите по ссылке:
                            <a style="color: #284C6A; text-decoration: none;" href="'.$link.'">'.$link.'</a>
                            <br /><br />Чтобы посмотреть компанию, пройдите по ссылке:
                            <a style="color: #284C6A; text-decoration: none;" href="'.$link_view.'">'.$link_view.'</a>                                    
                        </p>
                        <table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top: 50px;">
                            <tr>
                                <td valign="top">
                                    <a href="http://www.qlick.ru"><img src="http://www.qlick.ru/sprites/qmail/logo.gif" width="79" height="21" alt="Qlick.ru" border="0" /></a>
                                </td>
                                <td>
                                    <p style="margin: 0 0 0 50px; color: #7D7D7D; font-family: Arial; font-size: 11px; width: 100%; line-height: 150%;">
                                        Данное письмо отправлено автоматической системой оповещений. Не отвечайте на это сообщение. Если у вас возникли вопросы по работе служб сайта Qlick.ru — пожалуйста, обратитесь к нашей технической поддержке по адресу <a style="color: #7D7D7D; text-decoration: none;" href="mailto:support@qlick.ru">support@qlick.ru</a>.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="35%">
                    </td>
                </tr>
            </table>
                ')
                ->setFrom('no-reply@qlick.ru', 'Интернет-журнал Qlick.ru')
                ->addTo($user->email)
                ->setSubject('Qlick.ru — Приглашение в компанию')
                ->send();        
        
    }

    /**
     * Редактирование
     * @return void
     */
    public function editAction() {
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->_helper->initInterface();
        $request = $this->getRequest();
        $companyId = (int) $request->getParam('companyId');
        $employerId = (int) $request->getParam('employerId');
        $table = new Companies_Model_Table_Employers();
        $rows = $table->find($employerId);
        if (count($rows) > 0) {
            $row = $rows->current();
            if ($row->id_company != $companyId) {
                throw new Exception('Сотрудник не работает в данной компании');
            }
        } else {
            throw new Exception('Desconocido identificador');
        }
        $company = $this->_helper->companyProfile($row->id_company);
        $owner = $company->getOwner();
        
        $identity = $this->view->getUserByIdentity();
        
        if (!is_null($owner) || $this->view->isGodOfProject()) :
            if ((!is_null($identity) && $owner->id == $identity->id) || $this->view->isGodOfProject())  :        
        
                $this->view->headTitle()->append('Empresa ' . $company->name);
                $this->view->headTitle()->append('Destino?');
                if (!is_null($company->description))
                    $this->view->headMeta()->setName( 'description', $company->description );        

                $form = $this->view->editform = new Companies_Form_EditEmployer();
                $form->setTemplate('/forms/edit_employer');
                if ($request->isPost()) {
                    $data = $request->getPost();
                    if ($form->isValid($data)) {
                        $row->setFromArray($data);
                        $id = $row->save();
                        $this->_helper->FlashMessenger(array('system_message' => 'Información de empleo se ha cambiado'));
                        $this->_redirect($this->view->url(array('companyId' => $row->id_company), 'companyemployers'));
                        return;
                    }
                }
                $this->view->item = $row;
                $data = $row->toArray();
                $data['user'] = $row->getFullName();
                $form->setDefaults($data);
                
            endIf;
        endIf;
    }

    /**
     * Удаление
     * @return void
     */
    public function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $request = $this->getRequest();
        $companyId = (int) $request->getParam('companyId');
        $employerId = (int) $request->getParam('employerId');
        if (empty($employerId)) throw new Exception("Идентификатор не задан");
        
        $company = $this->_helper->companyProfile($companyId);
        $owner = $company->getOwner();
        
        $identity = $this->view->getUserByIdentity();
        
        if (!is_null($owner) || $this->view->isGodOfProject()) :
            if ((!is_null($identity) && $owner->id == $identity->id) || $this->view->isGodOfProject())  :
        
                $table = new Companies_Model_Table_Employers();
                $rows = $table->find($employerId);
                if (count($rows) > 0) {
                    $row = $rows->current();
                    $cid = $row->id_company;
                    if ($cid != $companyId) {
                        $this->_helper->FlashMessenger(array('system_error' => 'Сотрудник не работает в данной компании'));
                    } 
                    $row->delete();
                    $this->_helper->FlashMessenger(array('system_message' => 'Сотрудник уволен'));
                    $this->_redirect($this->view->url(array('companyId' => $companyId), 'companyemployers'));
                    return;
                } else {
                    throw new Exception('Desconocido identificador');
                }
                
            endIf;
        endIf;
    }

    /**
     * Подтверждение
     * @return void
     */
    public function confirmAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8');
        $id = (int) $this->getRequest()->getParam('employerId');
        if (empty($id))
            throw new Exception("Идентификатор не задан");
        // User
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (is_null($identity)) {
            $this->_helper->FlashMessenger(array('system_message' => 'Por favor, inicia sesión con tu nombre de usuario y contraseña'));
            $this->_redirect($this->view->url(array(''), 'signin')); 
        }
            
        $user_id = $identity->id;
        $table = new Companies_Model_Table_Employers();
        $rows = $table->find($id);
        if (count($rows) > 0) {
            $employer = $rows->current();
            $table = new Application_Model_Table_Users();
            $users = $table->find($employer->id_user);                        
            if ($employer->id_user == $user_id) {
                $employer->is_confirmed = 1;
                $employer->save();
                $this->_helper->FlashMessenger(array('system_ok' => 'Теперь вы являетесь сотрудником этой компании'));
                $this->_redirect($this->view->url(array('companyId' => $employer->id_company), 'companyemployers'));
                return;
            } else {
                //throw new Exception($employer->id_user);
                $user = $users->current();
                Zend_Auth::getInstance()->clearIdentity();
                $this->_helper->FlashMessenger(array('system_message' => 'Необходимо войти под именем '. $user->username));
                $this->_redirect($this->view->url(array(''), 'signin')); 
                return;
            }
        } else {
            throw new Exception('Desconocido identificador');
        }
    }

    /**
     * Возвращает список друзей компании в json формате
     * @return void
     */
    public function friendsAction() {
        // TODO сейчас просто возвращает всех пользователей
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $cid = (int) $this->getRequest()->getParam('cid');
        if (empty($cid))
            throw new Exception('Desconocido identificador компании');
        $term = $this->getRequest()->getParam('term');
        $table = new Application_Model_Table_Users();
        $rows = $table->fetchAll($table->select()->where("CONCAT(lastname, ' ', firstname) LIKE '%$term%'"));
        if (count($rows) == 0) {
            return;
        }
        $data = array();
        foreach ($rows as $row) {
            $data[] = $row->lastname . ' ' . $row->firstname;
        }
        $this->getResponse()->setBody(
                $this->view->json($data)
        );
    }

}