<?php
/**
 * YandexOpenIDService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).'/SOpenIDService.php';

/**
 * Yandex provider class.
 * @package application.extensions.eauth.services
 */
class YandexOpenIDService extends SOpenIDService {
  
  protected $name = 'yandexid';
  protected $title = 'Яндекс OpenID';
  protected $type = 'OpenID';
  protected $jsArguments = array('popup' => array('width' => 900, 'height' => 550));
  
  protected $url = 'http://openid.yandex.ru/trusted_request/';
  protected $requiredAttributes = array(
    'name' => array('fullname', 'namePerson'),
    'username' => array('nickname', 'namePerson/friendly'),
    'email' => array('email', 'contact/email'),
    'gender' => array('gender', 'person/gender'),
    'birthDate' => array('dob', 'birthDate'),
  );
  
  protected function fetchAttributes() {
    if (isset($this->attributes['username']) && !empty($this->attributes['username']))
      $this->attributes['url'] = 'http://openid.yandex.ru/'.$this->attributes['username'];
      
    //if (isset($this->attributes['birthDate']) && !empty($this->attributes['birthDate']))
      //$this->attributes['birthDate'] = strtotime($this->attributes['birthDate']);
  }
  
  protected function userLogout() {        
    return true;
  }
}