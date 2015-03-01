<?php
/**
 * TwitterOAuthService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).'/SOAuthService.php';

/**
 * Twitter provider class.
 * @package application.extensions.eauth.services
 */
class TwitterOAuthService extends SOAuthService {	
	
	protected $name = 'twitter';
	protected $title = 'Twitter';
	protected $type = 'OAuth';
	protected $jsArguments = array('popup' => array('width' => 900, 'height' => 550));
			
	protected $key = '';
	protected $secret = '';
	protected $providerOptions = array(
		'request' => 'http://api.twitter.com/oauth/request_token',    
		'authorize' => 'https://api.twitter.com/oauth/authorize',
		'access_token' => 'https://api.twitter.com/oauth/access_token',
    'using_access_token' => 'http://api.twitter.com/1/account/verify_credentials.json',
    'user_logout' => '',
	);
	
	protected function fetchAttributes() {
    parse_str($this->getAuthToken(), $access_token);
    
    $info = $this->makeSignedRequest($this->providerOptions['using_access_token']);
		                                         
		$this->attributes['id'] = $info['id_str'];
    $this->attributes['name'] = $info['name'];
    $this->attributes['url'] = 'http://twitter.com/#!/'.$info['screen_name'];
    $this->attributes['photo'] = $info['profile_image_url'];    
    $this->attributes['gender'] = '';
    $this->attributes['email'] = '';

	}
  
  protected function userLogout() {    
    return true;
  }
}