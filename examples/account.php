<?php
// path to hellosign.php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'hellosign.php';

$hs = new \HelloSign\API('user@example.com', 'password', 'apikey');

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
// This is dumb, why should I be able to create accounts for people?
// 1. It results in 'emma' spam for that account, and they probably 
// have no clue what Hello Sign is to begin with.
// 2. This should be account/invite that gives back a token/statement that account exists.
// This would result in an invitation to the email address to create an account with a 
// password that THEY set.
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

