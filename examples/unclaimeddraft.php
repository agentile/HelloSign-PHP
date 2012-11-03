<?php
// path to hellosign.php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'hellosign.php';

$hs = new \HelloSign\API('user@example.com', 'password', 'apikey');

//$hs::$use_curl = false;

// Create unclaimed draft
try {
    $response = $hs->api(
        'unclaimed_draft/create', 
        array(
            'file' => array(
                realpath('example.pdf'),
                realpath('example.pdf')
            ),
        ),
        'POST'
    );
    
    if (!$response->containsError()) {
        var_dump($response->getResponse());
    } else {
        echo $response->getError();
    }
} catch (\HelloSign\Exception $e) {
    echo $e->getMessage();
}
