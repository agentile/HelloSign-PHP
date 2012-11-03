<?php
// path to hellosign.php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'hellosign.php';

$hs = new \HelloSign\API('user@example.com', 'password', 'apikey');

//$hs::$use_curl = false;

// Retrieve paged list of reusable forms
try {
    $response = $hs->api('reusable_form/list', array('page' => 1));
    
    if (!$response->containsError()) {
        var_dump($response->getResponse());
    } else {
        echo $response->getError();
    }
} catch (\HelloSign\Exception $e) {
    echo $e->getMessage();
}

// Retrieve specific reusable form.
try {
    $reusable_form_id = 'hashgoeshere';
    $response = $hs->api('reusable_form/' . $reusable_form_id);
    
    if (!$response->containsError()) {
        var_dump($response->getResponse());
    } else {
        echo $response->getError();
    }
} catch (\HelloSign\Exception $e) {
    echo $e->getMessage();
}

// Add user to reusable form.
try {
    $reusable_form_id = 'hashgoeshere';
    $response = $hs->api(
        'reusable_form/add_user/' . $reusable_form_id, 
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

// Remove user from reusable form.
try {
    $reusable_form_id = 'hashgoeshere';
    $response = $hs->api(
        'reusable_form/remove_user/' . $reusable_form_id, 
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
