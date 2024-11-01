<?php
/**
 * This class for handling the Instagram OAuth process.
 *
 * @author Asep.co
 */ 
namespace AC\OAuth;
defined('ABSPATH') or die ('No script kiddies, please!');

class Auth {
  private $code;
  private $client_id;
  private $client_secret;
  private $grant_type;
  private $redirect_uri;

  public function __construct($code, $client_id, $client_secret, $redirect_uri) {
    $this->code          = $code;
    $this->client_id     = $client_id;
    $this->client_secret = $client_secret;
    $this->grant_type    = 'authorization_code';
    $this->redirect_uri  = $redirect_uri; 
  }

  /**
   * Exchange code from OAuth link.
   *
   * @param none
   * @return array
   */ 
  public function code_exchange() {
    $params = array(
      'code' => $this->code,
      'client_id' => $this->client_id,
      'client_secret' => $this->client_secret,
      'grant_type' => $this->grant_type,
      'redirect_uri' => $this->redirect_uri
    );
    return $this->http_post("https://api.instagram.com/oauth/access_token", $params); 
  }
  
  /**
   * Do POST http request to Instagram server.
   *
   * 
   */ 
  private function http_post($url, $params) {
    $postData = '';

    foreach($params as $k => $v) { 
      $postData .= $k . '='.$v.'&'; 
    }
        
    rtrim($postData, '&');
    $ch = curl_init();  
     
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_POST, count($postData));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
             
    $output = curl_exec($ch);
    
    curl_close($ch);
    return $output;
  }
}
