<?php
require_once('_bootstrap.php');
use RingCentral\SDK\SDK;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$env_file = "./environment/";
$tokens = "tokens_";
if (getenv('ENVIRONMENT') == "sandbox"){
   $env_file .= ".env-sandbox";
   $tokens .= "sb.txt";
}else{
   $env_file .= ".env-production";
   $tokens .= "pd.txt";
}

$dotenv = new Dotenv\Dotenv(__DIR__, $env_file);
$dotenv->load();

class RC_Authentication {
    function __construct() {}

    public function get_sdk(){
      $sdk = new SDK(getenv('RC_CLIENT_ID'),
                getenv('RC_CLIENT_SECRET'),
                getenv('RC_SERVER_URL'));
      return $sdk;
    }
    public function get_platform(){
      global $tokens;
      $sdk = $this->get_sdk();
      $platform = $sdk->platform();
      if (file_exists($tokens)){
        $saved_tokens = file_get_contents($tokens);
        $tokenObj = json_decode($saved_tokens, true);
        $platform->auth()->setData($tokenObj);
        if ($platform->loggedIn()){
          print "already logged in\r\n";
          return $platform;
        }
      }
      print "login/relogin\r\n";
      try {
          $response = $platform->login(getenv('RC_USERNAME'), getenv('RC_EXTENSION'), getenv('RC_PASSWORD'));
          file_put_contents($tokens, json_encode($platform->auth()->data(), JSON_PRETTY_PRINT));
          return $platform;
      }catch (\RingCentral\SDK\Http\ApiException $e) {
          throw $e;
      }
    }
}
