<html>
  <head>
    <title>Sonoff controller</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">	
	<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
  </head>
  <body class='bg-dark'>  
<?php
$status = ''; 		// Reponse from API call
$switchstate = "";	// Incoming switch state for checkbox state alignment
$seq = $error = ''; // Keys common to all responses

//Variables used in get messages
$data = $switch = $startup = $pulse = $pulseWidth = $ssid = $otaUnlock = $fwVersion = $deviceid = $bssid = '';	
$signalStrength = '';

//Variables used in set messages
$switch_value = $startup_value = $pulse_value = $pulseWidth_value = $ssid_value = $password_value = $downloadUrl_value = $shag256sum_value = '';

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
		//END OF ENDPOINT ASSIGNMENT
		
		//Get settings for form switch state
		if($_POST["newState"]=='on'){
			$switchstate = "checked";				
		}
		
		// Call API only if an endpoint has been set
		if($endpoint){
			$url = $base_url . $endpoint;
			$status = json_decode(callDeviceAPI($url, $json), true);
			
			if($endpoint == 'info'){			
				$data = $status['data'];			
				$switch = $data['switch'];
				$startup = $data['startup'];
				$pulse = $data['pulse'];
				$pulseWidth = $data['pulseWidth'];
				$ssid = $data['ssid'];
				$otaUnlock = $data['otaUnlock'];
				$fwVersion = $data['fwVersion'];
				$deviceid = $data['deviceid'];
				$bssid = $data['bssid'];
			}
			
			if($endpoint == 'signal_strength'){
				$data = $status['data'];
				$signalStrength = $data['signalStrength'];
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
  <div class='container d-flex justify-content-center'>
	<div class='border bg-light p-4 m-4 rounded'>
		<form method='POST'>
		<h1>
		 <span class="bi bi-lightbulb-fill"></span>
		 <span class="bi bi-lightbulb-fill text-success"></span>
		 <span class="bi bi-lightbulb-fill text-danger"></span>
		 </h1>
			<div class="form-check form-switch">
			  <input class="form-check-input" type="checkbox" role="switch" id="chkstate" <?= $switchstate ?>>
			  <label class="form-check-label" for="chkstate">Light state</label>
			 
			</div>		
			<!--button onclick="setEndPoint('switch')" name='switchOffBtn' class='btn btn-danger' type='submit'>Switch</button-->
			<!--button onclick="setEndPoint('info')" name='btnGetStatus' class='btn btn-primary' type='submit'>Get status</button-->
			<button name='btnSend' class='btn btn-primary' type='submit'>Send request</button>
			<input id='endpoint'  name='endpoint' value='info'>			
			<input id='newState'  name='newState' value='off'>
			
			
			<select id='endpoint-select' class="form-select">
			  <option selected>Select action</option>
			  <option value="info">info</option>
			  <option value="switch">switch</option>
			  <option value="startup">startup</option>
			  <option value="signal_strength">signal_strength</option>
			  <option value="pulse">pulse</option>
			  <option value="wifi">wifi</option>			   
			  <option value="ota_unlock">ota_unlock</option>			   
			  <option value="ota_flash">ota_flash</option>			   
			</select>
			
			<!--div id='result'><pre><?php print_r($data); ?></pre></div-->

			<?php
			if ($status!=''){
				$seq = $status['seq'];
				$error = $status['error'];
				
				if ($endpoint == 'info'){
					$switch = 		strtoupper($data['switch']);
					$startup = 		strtoupper($data['startup']);				
					$pulse =  		strtoupper($data['pulse']);
					$pulseWidth =  	strtoupper($data['pulseWidth']);
					$ssid =  		$data['ssid'];
					if($data['otaUnlock'] ==''){$otaUnlock = 'NO';} else {$otaUnlock = strtoupper($data['otaUnlock']);}
					$fwVersion =  	strtoupper($data['fwVersion']);
					$deviceid =  	strtoupper($data['deviceid']);
					$bssid =  		strtoupper($data['bssid']);		
					$signalStrength = strtoupper($data['signalStrength']);
					
					echo "<div class='d-flex p-1 m-1 border border-dark border-2 text-center rounded'>";
					echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Switch status<br><div class='badge bg-light text-dark mt-1'>" . $switch . "</div></div>";
					echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Startup status<br><div class='badge bg-light text-dark mt-1'>" . $startup . "</div></div>";
					echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Device ID<br><div class='badge bg-light text-dark mt-1'>" . $deviceid . "</div></div>";
					echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Inching mode<br><div class='badge bg-light text-dark mt-1'>" . $pulse . "</div></div>";
					echo 	"<div class='border border-dark bg-dark text-light m-1 p-1 rounded'>Pulse width<br><div class='badge bg-light text-dark mt-1'>" . $pulseWidth . "</div></div>";
					
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
					$signalStrength = $data['signalStrength'];				
				}
			}
			?>
			
			<div id='info' class='d-none bg-dark text-light border endpoint-details'>			
				info (n/a)
			</div>
			<div id='switch' class='d-none bg-dark text-light border endpoint-details'>			
				switch (on / off)
			</div>			
			<div id='startup' class='d-none bg-dark text-light border endpoint-details'>			
				startup 
				startup_value=(on / off / stay)
			</div>
			<div id='pulse' class='d-none bg-dark text-light border endpoint-details'>			
				inching settings
				$pulse_value = 'on';		// [on | off]
				$pulseWidth_value = '3000'; //  500~36000000ms in multiples of 500
			</div>
			<div id='signal_strength' class='d-none bg-dark text-light border endpoint-details'>			
				signal_strength
			</div>
			<div id='wifi' class='d-none bg-dark text-light border endpoint-details'>			
				wifi
				$ssid_value = '';
				$password_value = '';
			</div>
			<div id='ota_unlock' class='d-none bg-dark text-light border endpoint-details'>			
				ota_unlock
			</div>
			<div id='ota_flash' class='d-none bg-dark text-light border endpoint-details'>			
				ota_flash
				$downloadUrl_value = '';
				$sha256sum_value = '';
			</div>
			
			
		</form>		
	</div>
  </div>  
  <script>
	function setEndPoint(endpoint){		
		document.getElementById("endpoint").value = endpoint;
	}
	
	$('#chkstate').click(function(){
		var chkstate = $('#chkstate').prop('checked');
		console.log(chkstate);
		if(chkstate){
			document.getElementById("newState").value = 'on';
		} else {
			document.getElementById("newState").value = 'off';
		}
	});
	
	$('#endpoint-select').change(function(){
		var alldivs = $('.endpoint-details');
		alldivs.addClass("d-none");
		//var ep = $('#endpoint-select').value;
		//console.log(alldivs);
		var showdiv = document.getElementById(this.value);
		//console.log(showdiv);
		showdiv.classList.remove("d-none");
		document.getElementById("endpoint").value = this.value;
		
	});
  </script>  
  </body>
 </html>