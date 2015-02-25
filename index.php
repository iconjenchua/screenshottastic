<?php
require 'ScreenShotTastic.php';

$username = '';
$secret = '';

// Instantiate class
$ss = new ScreenShotTastic($username, $secret);
// Authorize user and get token
$token = $ss->authorize();

// Format data into array
$data = array('url' => '', 'size' => '', 'zoom' => '');
// Make the API call
$result = $ss->make_api_call('ss', $data, 'POST');

print_r($result);
?>