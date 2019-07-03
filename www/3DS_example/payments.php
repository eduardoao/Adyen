<!----- Please run this script in Firefox instead of Chrome for now! --->
<html>

<?php
require 'config.php';
 $request =array(
	"merchantAccount" => "Barateiro",
	"amount" => array(
		"currency" => "BRL",
		"value" => "12120",
	),
  //"threeDS2RequestData" => array(
    //"deviceChannel" => "browser",
    //"notificationURL" => "http://localhost:8088/3DS2_component_for_training/payments.php"
  //),
	"reference" => "3DS2-[jordan]".time(),
	"shopperIP" => "2.207.255.255",
	"shopperEmail" => "Test-3DS2@adyen.com",
	"shopperReference" => "3DS2_person",
	"channel" => "web",
	"origin" => "http://localhost:8088",
	"returnUrl" => "http://localhost:8088",
	"additionalData" => array (
		"allow3DS2" => true
	),
	"browserInfo" =>array(
		"acceptHeader"=>$_SERVER['HTTP_ACCEPT'],
		"userAgent"=>$_SERVER['HTTP_USER_AGENT'],
		"language"=>"en",// these should be troubleshooted and stopped at this stage, otherwise the challenge will fail at a later stage
		"colorDepth"=>24,
		"screenHeight"=>723,
		"screenWidth"=>1536,
		"timeZoneOffset"=>0,
		"javaEnabled"=>false
	),
	"paymentMethod" => array(
		"expiryMonth" => "03",
		"expiryYear" => "2030",
		"holderName" => "Payer",
		"number" => "6011111111111117",
		"cvc" => "737",
		"type"=> "scheme"
	)
	);
echo "<b>Payment Request</b><br><textarea rows=30 cols=70>";
print json_encode($request,JSON_PRETTY_PRINT);
echo "</textarea>";
$result=sendCURL($request,"https://checkout-test.adyen.com/v41/payments",$apiKey);

echo "<textarea rows=30 cols=70>";
print json_encode($result,JSON_PRETTY_PRINT);
echo "</textarea>";
?>
<body>

<!-------DOM  elements for the Adyen 3DS 2 components to act on --->
<div id="threeDS2"></div>
<p id="stage">Pending DFP ... ...<p>


<p id="finalResultsLabel"></p>
<textarea id="finalResults"></textarea>

<!--- Start of components code---->
<script src="https://checkoutshopper-test.adyen.com/checkoutshopper/sdk/2.5.0/adyen.js"></script>
<script>
var resultObject = JSON.parse('<?php echo json_encode($result)?>'); // self-added, converting initial /payments response JSON to JS object


//1st STEP: Device Fingerprint!
//The  following lines are based on https://docs.adyen.com/developers/risk-management/3d-secure-2/3ds2-checkout-api-integration#getthe3dsecure2devicefingerprint
const checkout = new AdyenCheckout();

const threeDS2IdentifyShopper = checkout
        .create('threeDS2DeviceFingerprint', {
            fingerprintToken: resultObject.authentication['threeds2.fingerprintToken'],
            onComplete: function(value) {
            	//START SELF-ADDED CODE
							console.log("DFP Successful!")
            	document.getElementById("stage").innerHTML="DFP Successful!" // to display on the frontend that DFP is completed

				var detailsRequest={ // preparing for the first details call later
					details: {
					//leaving blank for assignment later
				  	},
				  	paymentData: '<?php echo ($result->paymentData) ?>'
				};

            	detailsRequest.details["threeds2.fingerprint"]=value.data.details["threeds2.fingerprint"]// Assigning DFP value to object for later use

				var request = JSON.stringify(detailsRequest);

				sendDetailsrequest(request); // sending out request
				//END SELF-ADDED CODE
				}, // Gets triggered whenever the ThreeDS2 Component has a result
            onError: function() {
            	console.log("ERROR IN DFP!")
            } // Gets triggered on error
        })
        .mount('#threeDS2');
// End of DFP code

function sendDetailsrequest(request){
	xhr_new = new XMLHttpRequest();
	var url = "backend.php";
	xhr_new.open("POST", url, true);
	xhr_new.setRequestHeader("Content-type", "application/json");
	xhr_new.send(request);

	checkForCompletion(xhr_new); // call the function to check for completion of the request, which then executes the next step

}

function checkForCompletion(xhr){
	xhr.onreadystatechange = function () {
		    if (xhr.readyState === 4 && xhr.status === 200) {
		    	console.log("TRYING TO FIND: ");
		        var resultObject = JSON.parse(xhr.responseText);


		        if (resultObject.resultCode=="ChallengeShopper") {
			        console.log("1st Details result: "+JSON.stringify(resultObject));

// 2nd STEP: Present Challenge!
// Code is from this part: https://docs.adyen.com/developers/risk-management/3d-secure-2/3ds2-checkout-api-integration#presentachallenge
					const threeDS2Challenge = checkout
					        .create('threeDS2Challenge', {
					            challengeToken: resultObject.authentication['threeds2.challengeToken'],
					            onComplete: function(value) {
					            	//START SELF-ADDED CODE
					            	document.getElementById("stage").innerHTML="Challenge Complete!" // to display on the frontend that Challenge is completed

									var detailsRequest={ // recreating the detailsRequest
										details: {
											"threeds2.challengeResult":value.data.details["threeds2.challengeResult"]
									  	},
									  	paymentData: resultObject.paymentData
									};


									var request = JSON.stringify(detailsRequest);

                	sendDetailsrequest(request); //recursively calling itself if needed, in case of multiple challenges

									//END SELF-ADDED CODE

					            }, // Gets triggered whenever the ThreeDS2 Component has a result

					            onError: function() {
					            	console.log ("ERROR in CHALLENGE!")
					            }, // Gets triggered on error
					            size: '05' // Defaults to '01'
					        })
					        .mount('#threeDS2');
				} else {
					console.log("FINAL2 : " +JSON.parse(xhr.responseText));
					document.getElementById("finalResults").innerHTML=JSON.stringify(JSON.parse(xhr.responseText),null,2) // to display on the frontend that DFP is completed
					document.getElementById("finalResultsLabel").innerHTML="<b>FINAL RESULTS"
					document.getElementById("finalResults").style.width="500px";
					document.getElementById("finalResults").style.height="500px";
				}

		    }
		};
}
</script>


</body>

</html>
