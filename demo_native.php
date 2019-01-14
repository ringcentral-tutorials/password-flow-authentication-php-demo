<?php
include('auth_native.php');

$auth = new Authentication();

function get_account_extensions($access_token){
    $endpoint = "/restapi/v1.0/account/~/extension";
    $url = getenv("RC_SERVER_URL") . $endpoint;
    $headers = array (
          'Accept: application/json',
          'Authorization: Bearer ' . $access_token
        );
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5000);

        $strResponse = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            print $ecurlError;
            return null;
        } else {
            curl_close($ch);
            print $strResponse;
        }
    } catch (Exception $e) {
        print $e->getMessage();
    }
}
try{
    $at = $auth->get_token();
    get_account_extensions($at);
}catch (Exception $e) {
    print $e->getMessage();
}
