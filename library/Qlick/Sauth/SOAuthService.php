<?php
/**
 * EOAuthService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

//require_once 'SAuthServiceBase.php';
require_once 'OAuth.php';

/**
 * EOAuthService is a base class for all OAuth providers.
 * @package application.extensions.eauth
 */
abstract class Qlick_Sauth_SOAuthService extends Qlick_Sauth_SAuthServiceBase implements Qlick_Sauth_IAuthService {
  
  /**
   * @var EOAuthUserIdentity the OAuth library instance.
   */
  private $auth;
  
    
  /**
   * @var string OAuth2 client id. 
   */
  protected $key;
  
  /**
   * @var string OAuth2 client secret key.
   */
  protected $secret;
  
  /**
   * @var string OAuth scopes. 
   */
  protected $scope = '';
  
  /**
   * @var array Provider options. Must contain the keys: request, authorize, access.
   */
  protected $providerOptions =  array(
    'request' => '',
    'authorize' => '',
    'access' => '',
  );
    
  /**
   * Authenticate the user.
   * @return boolean whether user was successfuly authenticated.
   */
  public function authenticate() {
            
    if(!empty($_GET)){
      $token = $this->getAccessToken($_GET);
      
      if (!empty($token)) {
        $this->setState('auth_token', $token);
        $this->authenticated = true;
      }
    }
    else{
      if (!$this->getIsAuthenticated()) {      
        if($this->hasState('auth_token')){
          $this->resetState('auth_token');
        }
        $token = $this->getRequestToken();
        if (isset($token)) {
          $this->setState('auth_token', $token);
          $this->authenticated = true;
          
          $this->getAuthorize();
        }
      }  
    }
    
    
    return $this->getIsAuthenticated();
  }
  
  protected function getAuthorize() {
    parse_str($this->getAuthToken(), $access_token);
    $url = $this->providerOptions['authorize'].'?oauth_token='.$access_token['oauth_token'];    
    
    $this->getComponent()->redirect($url);
  }
  
  /**
   * Returns the OAuth2 access token.
   * @param string $code the OAuth2 code. See {@link getCodeUrl}.
   * @return string the token.
   */
  protected function getRequestToken() {
    return $this->makeSignedRequest($this->providerOptions['request']);
  }
  
  /**
   * Returns the url to request to get OAuth2 access token.
   * @return string url to request. 
   */
  protected function getTokenUrl($token) {
    return array('oauth_verifier' => $token['oauth_verifier'],);
  }
  
  /**
   * Returns the OAuth2 access token.
   * @param string $code the OAuth2 code. See {@link getCodeUrl}.
   * @return string the token.
   */
  protected function getAccessToken($token) {
    return $this->makeSignedRequest($this->providerOptions['access_token'], $this->getTokenUrl($token));
  }
  
  /**
   * Initializes a new session and return a cURL handle.
   * @param string $url url to request.
   * @param array $options HTTP request options. Keys: query, data, referer.
   * @param boolean $parseJson Whether to parse response in json format.
   * @return cURL handle.
   */
  protected function initRequest($options = array()) {
    $ch = parent::initRequest($options);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    if (isset($params['data'])) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml","SOAPAction: \"/soap/action/query\"", "Content-length: ".strlen($data))); 
    }
    return $ch;
  }
  
  /**
   * Returns the protected resource.
   * @param string $url url to request.
   * @param array $options HTTP request options. Keys: query, data, referer.
   * @param boolean $parseJson Whether to parse response in json format.
   * @return string the response. 
   * @see makeRequest
   */
  public function makeSignedRequest($url, $options = array()) {
//    if (!$this->getIsAuthenticated())
//      throw new SocialException('Unable to complete the authentication because the required data was not received.');
    parse_str($this->getAuthToken(), $access_token);

    $consumer = new OAuthConsumer($this->key, $this->secret);
    if (!empty($access_token['oauth_token']) && !empty($access_token['oauth_token_secret'])) {
      $token = new OAuthConsumer($access_token['oauth_token'], $access_token['oauth_token_secret']);
    } else {
      $token = NULL;
    }
            
    $signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();

    $request = OAuthRequest::from_consumer_and_token($consumer, $token, !empty($options) ? 'POST' : 'GET', $url, $options);    
    $request->sign_request($signatureMethod, $consumer, $token);                
    $url = $request->to_url();
    
    return $this->makeRequest($url, $options, !empty($options) ? 'POST' : 'GET');
  }
}