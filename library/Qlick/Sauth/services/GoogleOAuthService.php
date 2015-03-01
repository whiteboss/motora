<?php
/**
 * GoogleOpenIDService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).'/SOAuth2Service.php';

/**
 * Google provider class.
 * @package application.extensions.eauth.services
 */
class GoogleOAuthService extends SOAuth2Service {
	
	protected $name = 'google';
	protected $title = 'Google';
  protected $type = 'OAuth';
//	protected $type = 'OpenID';
	protected $jsArguments = array('popup' => array('width' => 450, 'height' => 380));
  
  protected $client_id = '';
  protected $client_secret = '';
  protected $scope = 'https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fplus.me';
  protected $providerOptions = array(
    'authorize' => 'https://accounts.google.com/o/oauth2/auth',
    'access_token' => 'https://accounts.google.com/o/oauth2/token',
    'using_access_token' => 'https://www.google.com/m8/feeds/contacts/default/full',
    'user_logout' => '',
  );
	
  protected function fetchAttributes() {
    $access_token = $this->getAuthToken();

    $data['access_token'] = $access_token['access_token'];
    
    $query = $this->makeQuery($data);    
    $url = $this->providerOptions['using_access_token'].$query;
    
    $info = $this->makeSignedRequest($url);
    var_dump($info);
    exit;

    $this->attributes['id'] = $info->id;
    $this->attributes['name'] = $info->name;
    $this->attributes['url'] = $info->link;
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
  
  protected function getTokenUrl($code) {
    $data = parent::getTokenUrl($code);
    
    $data['grant_type'] = 'authorization_code';
    $data['redirect_uri'] = $this->getRedirectUrl();
    return $data;
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