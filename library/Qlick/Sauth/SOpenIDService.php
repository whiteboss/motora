<?php
/**
 * EOpenIDService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once 'SAuthServiceBase.php';
require_once 'SOpenID.php';

/**
 * EOpenIDService is a base class for all OpenID providers.
 * @package application.extensions.eauth
 */
abstract class Qlick_Sauth_SOpenIDService extends Qlick_Sauth_SAuthServiceBase implements Qlick_Sauth_IAuthService {
  
  /**
   * @var EOpenID the openid library instance.
   */
  private $auth;
  
  /**
   * @var string the OpenID authorization url.
   */
  protected $url;
  
  /**
   * @var array the OpenID required attributes.
   */
  protected $requiredAttributes = array();
  
  
  /**
   * Initialize the component.
   * @param EAuth $component the component instance.
   * @param array $options properties initialization.
   */
  public function init($component, $options = array()) {
    parent::init($component, $options);
//    $this->auth = Yii::app()->loid->load();
    $this->auth = new SOpenID();
  }
    
  /**
   * Authenticate the user.
   * @return boolean whether user was successfuly authenticated.
   */
  public function authenticate() { 
    if (!empty($_REQUEST['openid_mode'])) {
      switch ($_REQUEST['openid_mode']) {
        case 'id_res':
          try {
            if ($this->auth->validate()) {
              $this->attributes['id'] = $this->auth->identity;
    
              $attributes = $this->auth->getAttributes();
              foreach ($this->requiredAttributes as $key => $attr) {
                if (isset($attributes[$attr[1]])) {
                  $this->attributes[$key] = $attributes[$attr[1]];
                }
                else {
                  throw new Qlick_Sauth_SocialException('Unable to complete the authentication because the required data was not received.'.ucfirst($this->getServiceName()));
                  return false;
                }
              }

              $this->authenticated = true;
              return true;
            }
            else {
              throw new Qlick_Sauth_SocialException('eauth', 'Unable to complete the authentication because the required data was not received.'.ucfirst($this->getServiceName()));
              return false;
            }
          }
          catch (Exception $e) {
            throw new Qlick_Sauth_SocialException($e->getMessage() .'  '. $e->getCode());
          }
          break;
        
        case 'cancel':
          $this->cancel();
          break;
        
        default: 
          throw new Qlick_Sauth_SocialException('Your request is invalid.');
          break;
      }
    } 
    else {
      $this->auth->setIdentity($this->url); //Setting identifier
//      $this->auth->identity = $this->url; //Setting identifier
      $this->auth->required = array(); //Try to get info from openid provider
      foreach ($this->requiredAttributes as $attribute)
        $this->auth->required[$attribute[0]] = $attribute[1];      
        
//      $this->auth->realm = 'http://'.$_SERVER['HTTP_HOST'];
//      $this->auth->returnUrl = $_SERVER['REQUEST_URI']; //getting return URL

      $this->auth->realm = '';
            
      try {
        $url = $this->auth->authUrl();        
        header("Location:".$url);
      }
      catch (Exception $e) {
        throw new Qlick_Sauth_SocialException($e->getMessage() .'  '. $e->getCode());
      }
    }
        
    return false;
  }
}
