<?php
require 'config.php';

//Retrieving 2x 3DS device printing fields from the device fingerprinting step
$incomingJSON = json_decode(file_get_contents('php://input'));

//print_r($incomingJSON);

$result=sendCURL($incomingJSON,"https://checkout-test.adyen.com/v41/payments/details",$apiKey);
print_r(json_encode($result));
?>
