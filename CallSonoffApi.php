<?php
$status = ''; 		// Reponse from API call
$switchstate = "";	// Incoming switch state for checkbox state alignment
$seq = $error = ''; // Keys common to all responses

//Variables used in get messages
$getData = $switch = $startup = $pulse = $pulseWidth = $ssid = $otaUnlock = $fwVersion = $deviceid = $bssid = '';	
$signalStrength = '';

//Variables used in set messages
$switch_value = $startup_value = $pulse_value = $pulseWidth_value = $ssid_value = $password_value = $downloadUrl_value = $sha256sum_value = '';

$target_ip = '192.168.101.191';
$target_port = '8081';

$base_url = 'http://' . $target_ip . ':' . $target_port . '/zeroconf/';

	if($_SERVER["REQUEST_METHOD"] == "POST"){
		
		//START OF ENDPOINT ASSIGNMENT
		//Get status
		if($_POST["endpoint"] == "info"){
			$endpoint = 'info';
			$json = '{"data": {}}';
		}
		
		//Change switch state
		if($_POST["endpoint"] == "switch"){
			$endpoint = 'switch';			
			$switch_value = $_POST["newState"];		// [ON | OFF]	
			$json = '{"data": {"switch":"' . $switch_value . '"}}';
		}
		
		//Power-on state
		if($_POST["endpoint"] == "startup"){
			$endpoint = 'startup';			
			$startup_value	= 'off'; // [ON | OFF | STAY]
			$json = '{"data": {"startup":"' . $startup_value . '"}}';
		}
		
		//WIFI signal strength
		if($_POST["endpoint"] == "signal_strength"){
			$endpoint = 'signal_strength';
			$json = '{"data": {}}';
		}
	
		//Inching
		if($_POST["endpoint"] == "pulse"){
			$endpoint = 'pulse';
			$pulse_value = 'on';		// [on | off]
			$pulseWidth_value = '3000'; //  500~36000000ms in multiples of 500
			$json = '{"data": {"pulse":"' . $pulse_value . '", "pulseWidth":"' . $pulseWidth_value . '"}}';
		}
		
		//WiFi settings
		if($_POST["endpoint"] == "wifi"){
			$endpoint = 'wifi';
			$ssid_value = '';
			$password_value = '';			
			$json = '{"data": {"ssid":"' . $ssid_value . '", "password":"' . $password_value . '"}}';
		}
		
		//OTA unlock
		if($_POST["endpoint"] == "ota_unlock"){
			$endpoint = 'ota_unlock';
			$json = '{"data": {}}';
		}

		//OTA flash
		if($_POST["endpoint"] == "ota_flash"){
			$endpoint = 'ota_flash';
			$downloadUrl_value = '';
			$sha256sum_value = '';
			$json = '{"data": {"downloadUrl":"' . $downloadUrl_value . '", "sha256sum":"' . $sha256sum_value . '"}}';
		}
		
		//Container for debug
		if($_POST["endpoint"] == "debug"){
			sleep(5);
			echo"<script>console.log('Debug done');</script>";
			echo "Debug response";
		}	
		//END OF ENDPOINT ASSIGNMENT
		

		
		// Call API only if an endpoint has been set
		if(isset($endpoint)){
			$url = $base_url . $endpoint;
			$status = json_decode(callDeviceAPI($url, $json), true);
			
			if($endpoint == 'info'){			
				$getData = $status['data'];			
				$switch = $getData['switch'];
				$startup = $getData['startup'];
				$pulse = $getData['pulse'];
				$pulseWidth = $getData['pulseWidth'];
				$ssid = $getData['ssid'];
				$otaUnlock = $getData['otaUnlock'];
				$fwVersion = $getData['fwVersion'];
				$deviceid = $getData['deviceid'];
				$bssid = $getData['bssid'];
			}
			
			if($endpoint == 'signal_strength'){
				$getData = $status['data'];
				$signalStrength = $getData['signalStrength'];
			}			
		}
	}
	
function callDeviceAPI($url, $json){
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_POST, 1);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  )
	  );

	$response = curl_exec($ch);
	$err = curl_error($ch);
	
	if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return $response;
		}
	
	curl_close($ch);
}
?>

<?php

if ($status!=''){
	$seq = $status['seq'];
	$error = $status['error'];
	
	if ($endpoint == 'info'){
		$switch = 		strtoupper($getData['switch']);
		$startup = 		strtoupper($getData['startup']);				
		$pulse =  		strtoupper($getData['pulse']);
		$pulseWidth =  	strtoupper($getData['pulseWidth']);
		$ssid =  		$getData['ssid'];
		if($getData['otaUnlock'] ==''){$otaUnlock = 'NO';} else {$otaUnlock = strtoupper($getData['otaUnlock']);}
		$fwVersion =  	strtoupper($getData['fwVersion']);
		$deviceid =  	strtoupper($getData['deviceid']);
		$bssid =  		strtoupper($getData['bssid']);		
		$signalStrength = strtoupper($getData['signalStrength']);
		
		echo "<div class='d-flex p-1 m-1 border border-dark border-2 text-center rounded'>";
		echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Switch status<br><div class='badge bg-light text-dark mt-1'>" . $switch . "</div></div>";
		echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Startup status<br><div class='badge bg-light text-dark mt-1'>" . $startup . "</div></div>";
		echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Device ID<br><div class='badge bg-light text-dark mt-1'>" . $deviceid . "</div></div>";
		echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Inching mode<br><div class='badge bg-light text-dark mt-1'>" . $pulse . "</div></div>";
		echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Duration<br><div class='badge bg-light text-dark mt-1'>" . $pulseWidth . "</div></div>";
		
		echo "</div>";
		echo "<div class='d-flex p-1 m-1 border border-dark border-2 text-center rounded'>";
		echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>OTA upgrade unlocked<br><div class='badge bg-light text-dark mt-1'>" . $otaUnlock . "</div></div>";
		echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Firmware version<br><div class='badge bg-light text-dark mt-1'>" . $fwVersion . "</div></div>";
		
		echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>SSID<br><div class='badge bg-light text-dark mt-1'>" . $ssid . "</div></div>";
		echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>BSSID<br><div class='badge bg-light text-dark mt-1'>" . $bssid . "</div></div>";
		echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Signal strength<br><div class='badge bg-light text-dark mt-1'>" . $signalStrength . "</div></div>";
		echo "</div>";
	}	
	
	if ($endpoint == 'signal_strength'){
		$signalStrength = $getData['signalStrength'];				
	}
}
?>