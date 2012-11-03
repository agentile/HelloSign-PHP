<?php
// path to hellosign.php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'hellosign.php';

$hs = new \HelloSign\API('hellosignacount@example.com', 'password', 'apikey');

//$hs::$use_curl = false;

// Retrieve paged list of signature requests
try {
    $response = $hs->api('signature_request/list', array('page' => 1));
    
    if (!$response->containsError()) {
        var_dump($response->getResponse());
    } else {
        echo $response->getError();
    }
} catch (\HelloSign\Exception $e) {
    echo $e->getMessage();
}

