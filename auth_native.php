<?php
require_once('vendor/autoload.php');
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$env_file = "./environment/";
$tokens_file = "tokens_";
if (getenv('ENVIRONMENT') == "sandbox"){
   $env_file .= ".env-sandbox";
   $tokens_file .= "sb.txt";
}else{
  $env_file .= ".env-production";
  $tokens_file .= "pd.txt";
}
$dotenv = new Dotenv\Dotenv(__DIR__, $env_file);
$dotenv->load();

class Authentication {
    function __construct() {}

    public function get_token(){
        global $tokens_file;
        $endpoint = "/restapi/oauth/token";
        $url = getenv("RC_SERVER_URL") . $endpoint;
        $basic = getenv("RC_CLIENT_ID") .":". getenv("RC_CLIENT_SECRET");
        $encoded = base64_encode($basic);
        $headers = array (
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Accept: application/json',
            'Authorization: Basic ' . $encoded,
          );
        $data = null;
        if (file_exists($tokens_file)){
            $saved_tokens = file_get_contents($tokens_file);
            $tokenObj = json_decode($saved_tokens);
            $date = new DateTime();
            $now = $date->getTimestamp() - $tokenObj->timestamp;
            if ($tokenObj->tokens->expires_in > $now){
              return $tokenObj->tokens->access_token;
            }else if ($tokenObj->tokens->refresh_token_expires_in > $now) {
                print "refresh_token not expired\r\n";
                $data = array (
                  'grant_type' => 'refresh_token',
                  'refresh_token' => $tokenObj->tokens->refresh_token
                );
            }else{
                print "refresh_token expired\r\n";
                $data = array (
                    'grant_type' => 'password',
                    'username' => getenv("RC_USERNAME"),
                    'password' => getenv("RC_PASSWORD")
                  );
            }
        }else{
          $data = array (
              'grant_type' => 'password',
              'username' => urlencode(getenv("RC_USERNAME")),
              'password' => getenv("RC_PASSWORD")
            );
        }
        $body = http_build_query($data);
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

            $strResponse = curl_exec($ch);
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                throw new Exception($curlErrno);
            } else {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
	              if ($httpCode == 200) {
                    $date = new DateTime();
                    $tokens = json_decode($strResponse);
                    $tokenObj = array(
                      "tokens" => $tokens,
                      "timestamp" => $date->getTimestamp()
                    );
                    file_put_contents($tokens_file, json_encode($tokenObj, JSON_PRETTY_PRINT));
                    return $tokens->access_token;
                }else{
                    throw new Exception($strResponse);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}

// function get_account_extensions($access_token){
//     $endpoint = "/restapi/v1.0/account/~/extension";
//     $url = getenv("RC_SERVER_URL") . $endpoint;
//     $headers = array (
//           'Accept: application/json',
//           'Authorization: Bearer ' . $access_token
//         );
//     try {
//         $ch = curl_init($url);
//         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//         curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//         curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
//
//         $strResponse = curl_exec($ch);
//         $curlErrno = curl_errno($ch);
//         if ($curlErrno) {
//             print $ecurlError;
//             return null;
//         } else {
//             curl_close($ch);
//             print $strResponse;
//             //return json_decode($strResponse);
//         }
//     } catch (Exception $e) {
//         print $e->getMessage();
//         throw $e;
//     }
// }
// $at = get_token();
// if ($at != null){
//   $res = get_account_extensions($at);
//   if ($res != null)
//   foreach ($res->records as $record){
//     print "Extension id: ".$record->id."\r\n";
//     if (array_key_exists('extensionNumber', $record))
//         print "Extension number: " + $record->extensionNumber."\r\n";
//     if (array_key_exists('name', $record))
//         print "Extension name: ".$record->name."\r\n";
//     print "Extension status: ".$record->status."\r\n";
//     if (array_key_exists('type', $record))
//         print "Extension type: ".$record->type."\r\n";
//     print "==================\r\n";
//   }
// }
