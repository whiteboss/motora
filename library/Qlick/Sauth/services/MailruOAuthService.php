<?php
/**
 * MailRuOAuthService class file.
 *
 * @author ChooJoy <choojoy.work@gmail.com>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).'/SOAuth2Service.php';

/**
 * Mail.Ru provider class.
 * @package application.extensions.eauth.services
 */
class MailruOAuthService extends SOAuth2Service {	
	
	protected $name = 'mailru';
	protected $title = 'Mail.ru';
	protected $type = 'OAuth';
	protected $jsArguments = array('popup' => array('width' => 580, 'height' => 400));

	protected $client_id = '';
	protected $client_secret = '';
	protected $scope = '';
	protected $providerOptions = array(
		'authorize' => 'https://connect.mail.ru/oauth/authorize',
		'access_token' => 'https://connect.mail.ru/oauth/token',
    'using_access_token' => 'http://www.appsmail.ru/platform/api',
    'user_logout' => '',
	);
  
  
		
	protected function fetchAttributes() {    
    $access_token = $this->getAuthToken();    
    
    $data['app_id'] = $this->client_id;
    $data['method'] = 'users.getInfo';
    $data['secure'] = 1;
    $data['session_key'] = $access_token['access_token'];    
    
    $params = '';
    foreach($data as $key=>$val){
     $params.=$key.'='.$val;
    }
    
    $data['sig'] = md5($params.$this->client_secret);
    
    $query = $this->makeQuery($data);
    
    $url = $this->providerOptions['using_access_token'].$query;
		
    $info = $this->makeSignedRequest($url);
		$info = $info[0];    
    
    $this->attributes['id'] = $info['uid'];
    $this->attributes['name'] = $info['first_name'].' '.$info['last_name'];
    $this->attributes['url'] = $info['link'];
    $this->attributes['photo'] = $info['pic_small'];    
    $this->attributes['gender'] = $info['sex'] == 0 ? 'M' : 'F';
    $this->attributes['email'] = $info['email'];
	}
  
  protected function userLogout() {    
    return true;
  }
	
	protected function getTokenUrl($code = null) {
    $data = parent::getTokenUrl($code);
    
    $data['grant_type'] = 'authorization_code';
    $data['redirect_uri'] = $this->getRedirectUrl();
    return $data;
	}
	
	protected function getCodeUrl($redirect_uri) {
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
				'code' => $json->error_code,
				'message' => $json->error_description,
			);
		}
		else
			return null;
	}
}