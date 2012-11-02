<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'hellosign.php';

$hs = new HelloSign\API('email@example.com', 'yourpass', 'apikey');

$response = $hs->api('account');

var_dump($response, $hs->last_response->getError());

