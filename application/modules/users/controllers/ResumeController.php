<?php
/**
 * 
 * Контроллер резюме
 * 
 */

class Users_ResumeController extends Zend_Controller_Action {	
	
	/**
	 * Карточка резюме
	 * @return void
	 */
	public function viewAction() {
            $this->getResponse()
                ->setHeader('Content-Type', 'text/html; charset=utf-8' );        
        
            $userId = ( int ) $this->getRequest()->getParam ( 'userId' );
            $user = $this->_helper->userProfile($userId);
            
            $this->view->headTitle()->append($user->username);
            $this->view->headTitle()->append('Резюме');

            $this->_helper->initInterface();
            $this->view->headScript()
                    ->appendFile( '/js/users/resume.js' );
            
            $logged = $this->view->GetUserByIdentity();
            
            $resume = $user->getResume();
            if (!is_null($resume)) {
                if (!is_null($logged) && $logged->id != $userId && $resume->is_visible == 1)
                    $this->view->resume = $resume;
                elseif (!is_null($logged) && $logged->id == $userId)
                    $this->view->resume = $resume;
                else
                    $this->_redirect( $this->view->url(array('userId' => $user->id), 'profile') );
            } elseif (is_null($resume) && !is_null($logged) && $logged->id != $userId)
                $this->_redirect( $this->view->url(array('userId' => $user->id), 'profile') );

	}
	
	/**
	 * Добавление
	 * @return void
	 */
	public function newAction() {
		$this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );
                
		$this->_helper->initInterface();
		$this->view->headScript()
                        ->appendFile( '/js/jquery.datepicker.min.js' )
                        ->appendFile( '/js/date.js' )
                        ->appendFile( '/resources/chosen/chosen.jquery.min.js' )
			->appendFile( '/js/companies/editresume.js' )
                        ->appendFile( '/js/charCount.js' );

                $this->view->headLink()
                        ->appendStylesheet( '/resources/chosen/chosen.css' )
                        ->appendStylesheet( '/css/datePicker.css' );

                $userId = ( int ) $this->getRequest()->getParam ( 'userId' );
                $user = $this->_helper->userProfile($userId);
                
                $logged = $this->view->GetUserByIdentity();
                if (is_null($logged) || $logged->id != $userId)
                    $this->_redirect( $this->view->url( array(''), 'signin' ) );
                
                $this->view->headTitle()->append($user->username);
                $this->view->headTitle()->append('Создание резюме');              
                
		$form = $this->view->addform = new Companies_Form_EditResume();
                $form->setTemplate('/forms/new_resume');
                $form->getElement('birthdate')->setValue('01.01.1983');

		$request = $this->getRequest();
		if ( $request->isPost() ) {
			$data = $request->getPost();
			$form->addConditionalValidators( $data );
			if ( $form->isValid( $data ) ) {
                            
                                $data = $form->getValues();
                                
                                $data['skills'] = nl2br($data['skills']);
                                $data['certificates'] = nl2br($data['certificates']);
                                $data['hobbies'] = nl2br($data['hobbies']);
                            
                                if (!Zend_Date::isDate($data['birthdate'], 'dd.mm.yyyy')) throw new Exception('Неверный формат даты рождения');
                                $data['birthdate'] = date("Y-m-d", strtotime($data['birthdate']));
				$data['industry'] = $data['industry']; //implode( ',', $data['industry'] );
				$data['work'] = $this->_helper->clearJson( $data['work'] );
				$data['institute'] = $this->_helper->clearJson( $data['institute'] );
				$data['languages'] = $this->_helper->clearJson( $data['languages'] );
				$data['driving'] = implode( ',', $data['driving'] );                                
				$data['id_user'] = $user->id;
				$table = new Users_Model_Table_Resumes();
				$row = $table->createRow( $data );
				$id = $row->save();
				$this->_helper->FlashMessenger(array('system_ok' => 'Резюме добавлено'));
				$this->_redirect( $this->view->url( array('userId' => $user->id, 'action' => 'view'), 'userresume' ) );
				return;
			}
		}
	}
	
	/**
	 * Редактирование
	 * @return void
	 */
	public function editAction() {
		$this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );
		$this->_helper->initInterface();
		$this->view->headScript()
                        ->appendFile( '/js/jquery.datepicker.min.js' )
                        ->appendFile( '/js/date.js' )
                        ->appendFile( '/resources/chosen/chosen.jquery.min.js' )
			->appendFile( '/js/companies/editresume.js' )
                        ->appendFile( '/js/charCount.js' );  
                
                $this->view->headLink()
                        ->appendStylesheet( '/resources/chosen/chosen.css' )
                        ->appendStylesheet( '/css/datePicker.css' );
                
                $request = $this->getRequest();
               
                $userId = ( int ) $request->getParam ( 'userId' );
                $user = $this->_helper->userProfile($userId);
                
                $resume = $user->getResume();
                if (is_null($resume))
                    throw new Exception('Резюме еще не создано');
                
                if ((!is_null($userId) || $this->view->isGodOfProject()) && !is_null($resume)) {
                    $identity = $this->view->getUserByIdentity();                
                    if ((!is_null($identity) && $userId == $identity->id) || $this->view->isGodOfProject()) {
                                
                        $this->view->headTitle()->append($user->username);
                        $this->view->headTitle()->append('Редактирование резюме'); 

                        $form = $this->view->editform = new Companies_Form_EditResume();
                        $form->setTemplate('/forms/new_resume');
                        if ( $request->isPost() ) {
                                $data = $request->getPost();
                                $form->addConditionalValidators( $data );
                                if ( $form->isValid( $data ) ) {
                                    
                                        $data = $form->getValues();
                                        
                                        $data['skills'] = nl2br($data['skills']);
                                        $data['certificates'] = nl2br($data['certificates']);
                                        $data['hobbies'] = nl2br($data['hobbies']);
                                    
                                        $data['industry'] = $data['industry']; //implode( ',', $data['industry'] );
                                        $data['work'] = $this->_helper->clearJson( $data['work'] );
                                        $data['institute'] = $this->_helper->clearJson( $data['institute'] );
                                        $data['languages'] = $this->_helper->clearJson( $data['languages'] );
                                        $data['driving'] = implode( ',', $data['driving'] );                                        
                                        $resume->setFromArray( $data );
                                        $this->_helper->FlashMessenger(array('system_message' => 'Информация о резюме изменена'));
                                        $id = $resume->save();
                                        $this->_redirect( $this->view->url( array('action'=>'view') ) );
                                        return;
                                }
                        }
                        $this->view->item = $resume;
                        $data = $resume->toArray();
                        $data['industry'] = explode( ',', $data['industry'] );
                        $data['driving'] = explode( ',', $data['driving'] );
                        $form->setDefaults( $data );
                        
                    }
                }
                        
	}
	
	/**
	 * Удаление
	 * @return void
	 */
	public function deleteAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );
                
                $req = $this->getRequest();
                
                $userId = ( int ) $req->getParam ( 'userId' );
                $user = $this->_helper->userProfile($userId);
                
		$resume = $user->getResume();	                
                
                if ((!is_null($userId) || $this->view->isGodOfProject()) && !is_null($resume)) {
                    $identity = $this->view->getUserByIdentity();                
                    if ((!is_null($identity) && $userId == $identity->id) || $this->view->isGodOfProject()) {
                
                        $table = new Users_Model_Table_Resumes();
                        $rows = $table->find( $resume->id );
                        if ( count($rows)>0 ) {
                                $rows->current()->delete();    
                                $this->_helper->FlashMessenger(array('system_message' => 'Резюме удалено'));
                                $this->_redirect( $this->view->url( array('userId' => $user->id, 'action' => 'view'), 'userresume' ) );
                        } else {
                                throw new Exception('Desconocido identificador');
                        }
                        
                    }
                } else {
                    throw new Exception('Удаление запрещено');
                } 
	}
        
        public function openAction() {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();    
            
            $this->getResponse()
			->setHeader('Content-Type', 'text/html; charset=utf-8' );
                
                $req = $this->getRequest();
                
                $userId = ( int ) $req->getParam ( 'userId' );
                $user = $this->_helper->userProfile($userId);
                
		$resume = $user->getResume();	                
                
                if ((!is_null($userId) || $this->view->isGodOfProject()) && !is_null($resume)) {
                    $identity = $this->view->getUserByIdentity();                
                    if ((!is_null($identity) && $userId == $identity->id) || $this->view->isGodOfProject()) {
                
                        $table = new Users_Model_Table_Resumes();
                        $rows = $table->find( $resume->id );
                        if ( count($rows)>0 ) {
                                $row = $rows->current();    
                                $row->is_visible = $row->is_visible ? 0 : 1;
                                $row->save();
                                
                                if ($row->is_visible == 0)
                                    $this->getResponse()->setBody( 'Опубликовать' );
                                else
                                    $this->getResponse()->setBody( 'Снять с публикации' );
                                
                        } else {
                                throw new Exception('Desconocido identificador');
                        }
                        
                    }
                } else {
                    throw new Exception('Удаление запрещено');
                }
            
        }

                /**
	 * Количество
	 * @return void
	 */
	public function countAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$cid = ( int ) $this->getRequest()->getParam ( 'cid' );
		if ( empty($cid) ) throw new Exception( "Идентификатор компании не задан" );
		$table = new Companies_Model_Table_Resumes();
		$rows = $table->fetchAll( $table->select()
			->where('id_company = ?', $cid)
			//->where('visible = 1')
		);
		$this->getResponse()->setBody(
			count($rows) 
		);
	}
}