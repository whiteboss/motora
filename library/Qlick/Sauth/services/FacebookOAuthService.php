<?php
/**
 * FacebookOAuthService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

//require_once dirname(dirname(__FILE__)).'/SOAuth2Service.php';

/**
 * Facebook provider class.
 * @package application.extensions.eauth.services
 */
class Qlick_Sauth_services_FacebookOAuthService extends Qlick_Sauth_SOAuth2Service {  
  
  protected $name = 'facebook';
  protected $title = 'Facebook';
  protected $type = 'OAuth';
  protected $jsArguments = array('popup' => array('width' => 585, 'height' => 290));

  protected $client_id = '';
  protected $client_secret = '';
  protected $scope = '';
  protected $providerOptions = array(
    'authorize' => 'https://www.facebook.com/dialog/oauth',
    'access_token' => 'https://graph.facebook.com/oauth/access_token',
    'using_access_token' => 'https://graph.facebook.com/me',
    'user_logout' => 'https://www.facebook.com/logout.php',
  );
    
  protected function fetchAttributes() {
    parse_str($this->getAuthToken(), $access_token);
    
    $data['access_token'] = $access_token['access_token'];
    $query = $this->makeQuery($data);
    
    $url = $this->providerOptions['using_access_token'].$query;
    
    $info = $this->makeSignedRequest($url);
    
    $this->attributes = json_decode($info);
    
//    var_dump(print_r($this->attributes));
//    die();
    
//    $this->attributes['id'] = $info['id'];
//    $this->attributes['name'] = $info['first_name'].' '.$info['last_name'];
//    $this->attributes['url'] = $info['link'];
//    $this->attributes['photo'] = 'http://graph.facebook.com/'.$info['id'].'/picture';
//    $this->attributes['gender'] = ($info['gender'] == 'male') ? 'F' : 'M';
  }
    
  protected function getTokenUrl($code) {
    $data = parent::getTokenUrl($code);
    
    $data['grant_type'] = 'authorization_code';
    $data['redirect_uri'] = $this->getRedirectUrl();
    return $data;
  }
  
  protected function userLogout() {    
    parse_str($this->getAuthToken(), $access_token);
    
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
  
//  protected function getAccessToken($code) {
//    $response = $this->makeRequest($this->getTokenUrl($code), array(), false);
//    parse_str($response, $result);
//    return $result['access_token'];
//  }
  
  protected function getCodeUrl($redirect_uri) {
    /*if (strpos($redirect_uri, '?') !== false || strpos($redirect_uri, '&') !== false)
      throw new EAuthException('Facebook does not support url with special characters. You should use SEF urls for authentication through Facebook.', 500);*/
    if (strpos($redirect_uri, '?') !== false) {
      $url = explode('?', $redirect_uri);
      $url[1] = preg_replace('#[/]#', '%2F', $url[1]);
      $redirect_uri = implode('?', $url);
    }
    
    $this->setState('redirect_uri', $redirect_uri);
    $url = parent::getCodeUrl($redirect_uri);
    if (isset($_GET['js']))
      $url .= '&display=popup';
    
    return $url;
  }
  
  /**
   * Returns the error info from json.
   * @param stdClass $json the json response.
   * @return array the error array with 2 keys: code and message. Should be null if no errors.
   */
  protected function fetchJsonError($json) {
    if (isset($json->error)) {
      return array(
        'code' => $json->error->code,
        'message' => $json->error->message,
      );
    }
    else
      return null;
  }    
}