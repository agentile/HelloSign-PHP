<?php
// path to hellosign.php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'hellosign.php';

$hs = new \HelloSign\API('hellosignacount@example.com', 'password', 'apikey');

//$hs::$use_curl = false;

// View account details
try {
    $response = $hs->api('account');
    
    if (!$response->containsError()) {
        var_dump($response->getResponse());
    } else {
        echo $response->getError();
    }
} catch (\HelloSign\Exception $e) {
    echo $e->getMessage();
}


// Update account
try {
    $response = $hs->api(
        'account', 
        array(
            'password' => 'XXX'
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

// Create new account
try {
    $response = $hs->api(
        'account/create', 
        array(
            'email_address' => 'email@example.com', 
            'password' => 'StrongPassword123@!'
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

