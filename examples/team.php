<?php
// path to hellosign.php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'hellosign.php';

$hs = new \HelloSign\API('user@example.com', 'password', 'apikey');

//$hs::$use_curl = false;

// Create team
try {
    $response = $hs->api(
        'team/create', 
        array(
            'name' => 'The Badgers'
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


// View team details
try {
    $response = $hs->api('team');
    
    if (!$response->containsError()) {
        var_dump($response->getResponse());
    } else {
        echo $response->getError();
    }
} catch (\HelloSign\Exception $e) {
    echo $e->getMessage();
}

// Update team
try {
    $response = $hs->api(
        'team', 
        array(
            'name' => 'The Wolves'
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

// Add member
try {
    $response = $hs->api(
        'team/add_member', 
        array(
            'email_address' => 'user@example.com'
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

// Remove member
try {
    $response = $hs->api(
        'team/remove_member', 
        array(
            'email_address' => 'user@example.com'
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

// Delete 
try {
    $response = $hs->api('team/destroy', array(), 'POST');
    
    if (!$response->containsError()) {
        var_dump($response->getStatusCode());
    } else {
        echo $response->getError();
    }
} catch (\HelloSign\Exception $e) {
    echo $e->getMessage();
}
