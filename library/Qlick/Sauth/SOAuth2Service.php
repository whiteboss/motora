<?php
/**
 * EOAuth2Service class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

//require_once 'SAuthServiceBase.php';

/**
 * EOAuth2Service is a base class for all OAuth 2.0 providers.
 * @package application.extensions.eauth
 */
abstract class Qlick_Sauth_SOAuth2Service extends Qlick_Sauth_SAuthServiceBase implements Qlick_Sauth_IAuthService {

  /**
   * @var string OAuth2 client id. 
   */
  protected $client_id;
  
  /**
   * @var string OAuth2 client secret key.
   */
  protected $client_secret;
  
  /**
   * @var string OAuth scopes. 
   */
  protected $scope = '';
  
  /**
   * @var array Provider options. Must contain the keys: authorize, access_token.
   */
  protected $providerOptions = array(
    'authorize' => '',
    'access_token' => '',
  );
  
  
  /**
   * @var string current OAuth2 access token.
   */
  private $access_token = '';
  
    
  /**
   * Authenticate the user.
   * @return boolean whether user was successfuly authenticated.
   */
  public function authenticate() {
    // user denied error
    if (isset($_GET['error']) && $_GET['error'] == 'access_denied') {
      $this->cancel();
      return false;
    }    
    
    //Получаем "access_token" и сохр. в сессионной переменной
    
    if (isset($_GET['code'])) {        
      $code = $_GET['code'];
      $token = $this->getAccessToken($code);
      if (isset($token)) {
        $this->setState('auth_token', $token);
        $this->authenticated = true;
      }
    }
    //Получаем "code"
    else{        
      // Use the URL of the current page as the callback URL.
//      $server = 'http://'.$_SERVER['HTTP_HOST'];
//      $path = $_SERVER['REQUEST_URI'];      
      $url = $this->getCodeUrl($this->getRedirectUrl()); 
      $this->getComponent()->redirect($url);
    }
    
    return $this->getIsAuthenticated();
  }
  
  /**
   * Returns the url to request to get OAuth2 code.
   * @param string $redirect_uri url to redirect after user confirmation.
   * @return string url to request. 
   */
  protected function getCodeUrl($redirect_uri) {
    return $this->providerOptions['authorize'].'?client_id='.$this->client_id.'&redirect_uri='.urlencode($redirect_uri).'&scope='.$this->scope.'&response_type=code';
  }
  
  /**
   * Returns the url to request to get OAuth2 access token.
   * @return string url to request. 
   */
  protected function getTokenUrl($code) {
    return array('client_id' => $this->client_id,
                 'client_secret' => $this->client_secret,
                 'code' => $code
                 );
  }

  /**
   * Returns the OAuth2 access token.
   * @param string $code the OAuth2 code. See {@link getCodeUrl}.
   * @return string the token.
   */
  protected function getAccessToken($code) { 
    return $this->makeRequest($this->providerOptions['access_token'], $this->getTokenUrl($code));
  }
  
  /**
   * Returns the protected resource.
   * @param string $url url to request.
   * @param array $options HTTP request options. Keys: query, data, referer.
   * @param boolean $parseJson Whether to parse response in json format.
   * @return string the response. 
   * @see makeRequest
   */
  public function makeSignedRequest($url, $options = array(), $method='GET') {
    if (!$this->getIsAuthenticated())
      throw new Qlick_Sauth_SocialException('Unable to complete the authentication because the required data was not received.');
      
    $result = $this->makeRequest($url, $options, $method);
    return $result;
  }
}