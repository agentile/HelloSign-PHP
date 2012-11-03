<?php
// path to hellosign.php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'hellosign.php';

$hs = new \HelloSign\API('user@example.com', 'password', 'apikey');

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

// Retrieve specific signature request
try {
    $signature_request_id = 'hashgoeshere';
    $response = $hs->api('signature_request/' . $signature_request_id);
    
    if (!$response->containsError()) {
        var_dump($response->getResponse());
    } else {
        echo $response->getError();
    }
} catch (\HelloSign\Exception $e) {
    echo $e->getMessage();
}

// Send signature request for a file
try {
    $response = $hs->api(
        'signature_request/send', 
        array(
            'title' => 'Example document',
            'subject' => 'Test Subject',
            'message' => 'Oh hi there, please sign this.',
            'file' => array(
                realpath('example.pdf')
            ),
            'signers' => array(
                array(
                    'name' => 'Joe',
                    'email_address' => 'user@example.com',
                    'order' => 0,
                ),
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

// Send signature request with reusable form
try {
    $response = $hs->api(
        'signature_request/send_with_reusable_form', 
        array(
            'reusable_form_id' => 'hashgoeshere',
            'title' => 'Example document',
            'subject' => 'Test Subject',
            'message' => 'Oh hi there, please sign this.',
            'signers' => array(
                'Clients' => array(
                    'name' => 'Joe',
                    'email_address' => 'user@example.com',
                ),
            ),
            //'custom_fields' => array(
            //    'Cost' => '$100.00',
            //),
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

// Remind someone to sign a file.
try {
    $signature_request_id = 'hashgoeshere';
    $response = $hs->api(
        'signature_request/remind/' . $signature_request_id, 
        array(
            'email_address' => 'user@example.com',
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

// Cancel a signature request
try {
    $signature_request_id = 'hashgoeshere';
    $response = $hs->api('signature_request/cancel/' . $signature_request_id, array(), 'POST');
    
    if (!$response->containsError()) {
        var_dump($response->getStatusCode());
    } else {
        echo $response->getError();
    }
} catch (\HelloSign\Exception $e) {
    echo $e->getMessage();
}

// Retrieve final copy 
try {
    $signature_request_id = 'hashgoeshere';
    $response = $hs->api('signature_request/final_copy/' . $signature_request_id);
    
    if (!$response->containsError()) {
        file_put_contents('/tmp/final.pdf', $response->getResponse());
    } else {
        echo $response->getError();
    }
} catch (\HelloSign\Exception $e) {
    echo $e->getMessage();
}
