<?php
/**
 * YandexOpenIDService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).'/SOAuth2Service.php';

/**
 * Yandex provider class.
 * @package application.extensions.eauth.services
 */
class YandexOAuthService extends SOAuth2Service {
  
  protected $name = 'yandex';
  protected $title = 'Яндекс';
  protected $type = 'OpenID';
  protected $jsArguments = array('popup' => array('width' => 900, 'height' => 550));
                    
  protected $client_id = '';
  protected $client_secret = '';
  protected $scope = '';
  protected $providerOptions = array(
    'authorize' => 'https://oauth.yandex.ru/authorize',
    'access_token' => 'https://oauth.yandex.ru/token',
    'using_access_token' => 'http://api.moikrug.ru/v1/my/',
    'user_logout' => '',
  );
  
  protected $fields = '';
  
  protected function fetchAttributes() {
                                       
    $access_token = $this->getAuthToken();
    $data['oauth_token'] = $access_token['access_token'];
    
//    $data['access_token'] = $access_token['access_token'];
//    $data['format'] = 'json';
    
    $query = $this->makeQuery($data);
    $url = $this->providerOptions['using_access_token'].$query;
    
    $info = (array)$this->makeSignedRequest($url);
        
    $info = $info[0];                  

    $this->attributes['id'] = $info['id'];
    $this->attributes['name'] = $info['name'];
    $this->attributes['url'] = $info['link'];
    $this->attributes['photo'] = $info['avatar']['SnippetSquare'];    
    $this->attributes['gender'] = ($info['gender'] == 'male') ? 'F' : 'M';
  }
  
  protected function userLogout() {    
    $access_token = $this->getAuthToken();
    
    if(empty($access_token['access_token'])) return false;
    
    $server = 'http://'.$_SERVER['HTTP_HOST'];
    $path = $_SERVER['REQUEST_URI'];
    
    $data['next'] = $server.$path;
    $data['access_token'] = $access_token['access_token'];
    
    $query = $this->makeQuery($data);
    
    $url = $this->providerOptions['user_logout'].$query;
    
    $this->redirect($url);
    
    return true;
  }
  
  protected function getTokenUrl($code = null) {
    $data = parent::getTokenUrl($code);
    
    $data['grant_type'] = 'authorization_code';
    return $data;
  }
}