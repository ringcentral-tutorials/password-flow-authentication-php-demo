<?php
include('auth_rcsdk.php');
use RingCentral\SDK\Http\ApiException;

function get_account_extensions(){
    $endpoint = "/restapi/v1.0/account/~/extension";
    $auth = new Authentication();
    $platform = $auth->get_platform();
    try {
      $response = $platform->get($endpoint);
      print (json_encode($response->json(), JSON_PRETTY_PRINT));
    } catch (\RingCentral\SDK\Http\ApiException $e) {
        print $e->getMessage();
    }
}

get_account_extensions();
