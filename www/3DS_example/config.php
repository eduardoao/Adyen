
<?php
//FILE containing all webservice related stuff

$PaymentMethodsURL="https://checkout-test.adyen.com/checkout/v41/paymentMethods";
$PaymentsURL="https://checkout-test.adyen.com/checkout/v41/payments";
$PaymentDetailsURL="https://checkout-test.adyen.com/checkout/v41/payments/details";
$syncTerminalAPIURL ="https://terminal-api-test.adyen.com/sync";
$asyncTerminalAPIURL="https://terminal-api-test.adyen.com/async";



$apiKey="AQEkhmfuXNWTK0Qc+iSGm2UOpvaeQoRoMs0Qaa6opl3fM/LzzZieEMFdWw2+5HzctViMSCJMYAc=-epVcSWXwy36F5bKNwIyZGdUChs0u9g+e6x8aTUA7fQ8=-Pn6XJcYVzYWhJG8W";


//Generic function to do JSON requests
function sendCURL($request,$url,$apiKey) {

 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, $url);
 curl_setopt($ch, CURLOPT_HEADER, false);
 curl_setopt($ch, CURLOPT_POST,count(json_encode($request)));
 curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
 curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($request));
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-type: application/json","X-API-KEY: $apiKey"));
 $result = curl_exec($ch);
 
 return json_decode($result);
}

?>
