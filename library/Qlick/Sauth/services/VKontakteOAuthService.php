<?php
/**
 * VKontakteOAuthService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

//require_once dirname(dirname(__FILE__)).'/SOAuth2Service.php';

/**
 * VKontakte provider class.
 * @package application.extensions.eauth.services
 */
class Qlick_Sauth_services_VKontakteOAuthService extends Qlick_Sauth_SOAuth2Service {  
  
  protected $name = 'vkontakte';
  protected $title = 'ВКонтакте';
  protected $type = 'OAuth';
  protected $jsArguments = array('popup' => array('width' => 824, 'height' => 500));

  protected $client_id = '';
  protected $client_secret = '';
  protected $scope = '';  
  protected $providerOptions = array(
    //'authorize' => 'https://api.vk.com/oauth/authorize',
      'authorize' => 'http://oauth.vk.com/authorize',
    'access_token' => 'https://api.vk.com/oauth/access_token',
    'using_access_token' => 'https://api.vk.com/method/getProfiles',
    'user_logout' => 'https://oauth.vk.com/logout',
    //'user_logout' => 'http://api.vk.com/oauth/logout'
  );

  protected $fields = 'uid,first_name,last_name,nickname,bdate,city,sex,photo_medium';
    
  protected function fetchAttributes() {
    
    $access_token = $this->getAuthToken();
    $data['uids'] = $access_token['user_id'];
    $data['access_token'] = $access_token['access_token'];
    $data['fields'] = $this->fields;
    
    $query = $this->makeQuery($data);
    $url = $this->providerOptions['using_access_token'].$query;
    
    $info = (array)$this->makeSignedRequest($url);

    $info = $info['response'][0];  
    
    $this->attributes = $info;

//    $this->attributes['id'] = $info['uid'];
//    $this->attributes['name'] = $info['first_name'].' '.$info['last_name'];
//    $this->attributes['url'] = 'http://vkontakte.ru/id'.$info['uid'];
//    $this->attributes['photo'] = $info['photo'];    
//    $this->attributes['gender'] = $info['sex'] == 1 ? 'F' : 'M';
  }
  
  protected function userLogout() {    
    $url = $this->providerOptions['user_logout'];    
    $info = $this->makeRequest($url, array(), 'GET');
    return true;
  }
  
  /**
   * Returns the error info from json.
   * @param stdClass $json the json response.
   * @return array the error array with 2 keys: code and message. Should be null if no errors.
   */
  protected function fetchJsonError($json) {
    if (isset($json->error)) {
      return array(
        'code' => $json->error->error_code,
        'message' => $json->error->error_msg,
      );
    }
    else
      return null;
  }
}