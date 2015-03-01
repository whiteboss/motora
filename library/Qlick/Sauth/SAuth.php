<?php
//require_once 'SocialException.php';

class Qlick_Sauth_SAuth{
  public $services = array();
  
  public function getServices() {
    $services = array();
    foreach ($this->services as $service => $options) {        
      $class = $this->getIdentity($service);
      $services[$class->getServiceName()] = (object) array(
        'id' => $class->getServiceName(),
        'title' => $class->getServiceTitle(),
        'type' => $class->getServiceType(),
        'jsArguments' => $class->getJsArguments(),
        'redirectUrl' => isset($options['redirectUrl']) ? $options['redirectUrl']:'',
        'cancelUrl' => isset($options['cancelUrl']) ? $options['cancelUrl']:'',
      );
    }
    
    return $services;
  }
  
  /**
   * Returns the settings of the service.
   * @param string $service the service name.
   * @return array the service settings.
   */
  protected function getService($service) {
    $service = strtolower($service);
    $services = $this->getServices();
    
    if (!isset($services[$service]))
      throw new Qlick_Sauth_SocialException('Undefined service name: '.$service);
      
    return $services[$service];
  }
    
  /**
   * Returns the service identity class.
   * @param string $service the service name.
   * @return IAuthService the identity class.
   */
  public function getIdentity($service) {
    $service = strtolower($service);    
    if (!isset($this->services[$service]))
      throw new Qlick_Sauth_SocialException('Undefined service name: '.$service);
      
    $service = $this->services[$service];    

    $class = $service['class'];
    
    $identity = false;
    
//    $service_path = dirname(dirname(__FILE__)).'/sauth/services/'.$class.'.php';
//    if(is_file($service_path)){
//      require_once($service_path);      
//      $class = substr($class, $point + 1);
      
      unset($service['class']);
      $identity = new $class();
      $identity->init($this, $service);
//    }
    
    return $identity;
  }
  
  /**
   * Serialize the identity class.
   * @param EAuthServiceBase $identity the class instance.
   * @return string serialized value.
   */
  public function toString($identity) {
    return serialize($identity);
  }
  
  /**
   * Serialize the identity class.
   * @param string $identity serialized value.
   * @return EAuthServiceBase the class instance.
   */
  public function fromString($identity) {
    return unserialize($identity);
  }
  

  public function setServices($services) {
    $this->services = $services;
  }
  
  public function redirect($url){
    header("Location: ".$url);
    exit;
  }
  
  public function redirect_w($url){
    header("Location: ".$url);
  }
  
}