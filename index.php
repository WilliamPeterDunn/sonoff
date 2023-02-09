<html>
  <head>
    <title>Sonoff controller</title>
	<link rel="icon" href="/sonoff/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">	
	<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
	<style>
	.bg-yellow{
		background-color: yellow;
	}
	</style>
  </head>
  <body class='bg-dark'>
  
  <div class='container d-flex justify-content-center'>
	<div class='border bg-light p-4 m-4 rounded'>
		<form method='POST'>			
			<div class='d-flex-inline'>
				<div class='d-flex'>
				  <div id='light-status' onclick="lightClicked();" class="h1 bi bi-lightbulb border-dark border rounded p-2 mx-1 bg-yellow" >
				  <div id='spinner' class="d-none spinner-border"></div>
				  </div>
				  

					<div class='d-flex border rounded border-dark border-2 mb-2'>
						<div class="h1 bi bi-wifi rounded p-2"></div>
						<div class='p-2 bg-dark text-light text-start m-0 rounded-end'>
						<table class='text-light'>
							<tr><td class='pe-2'>Signal</td><td><div class='badge bg-light text-dark mt-1'>-69 dBm</div>		</td></tr>
							<tr><td class='pe-2'>SSID</td><td><div class='badge bg-light text-dark mt-1'>48:8F:5A:D:FD:50</div>	</td></tr>
							<tr><td class='pe-2'>BSSID</td><td><div class='badge bg-light text-dark mt-1'>Qwerty</div>			</td></tr>
						</table>
						</div>					
					</div>
					
					<div class='configinput'>
						<label for="IP_address">IP</label>
						<input type='text' id='IP_address' name='IP_address' size='11' value = '192.168.101.191'>
						<label for="IP_address">Port</label>			 
						<input type='text' id='port' name='port' value = '8101' size='1'>			 <br>
						<label for="IP_address">Target device ID</label>			 
						<input class='my-1' type='text' id='target' name='target' value = '1000EADD3D' size='9'>
					</div>
					  
					<div class="d-none h1 bi bi-wifi-off border-dark border border-2 rounded p-2 mx-1"></div>
				</div>
			</div>		
				
			<!--button onclick="setEndPoint('switch')" name='switchOffBtn' class='btn btn-danger' type='submit'>Switch</button-->
			<!--button onclick="setEndPoint('info')" name='btnGetStatus' class='btn btn-primary' type='submit'>Get status</button-->
			
			<input hidden id='endpoint'  name='endpoint' value='info'>
			<input hidden id='newState'  name='newState' value='off'>
			<div class='d-flex'>	
				<select id='endpoint-select' class="form-select">				  
				  <option selected value="info">info</option>
				  <option value="switch">switch</option>
				  <option value="startup">startup</option>
				  <option value="signal_strength">signal_strength</option>
				  <option value="pulse">pulse</option>
				  <option value="wifi">wifi</option>			   
				  <option value="ota_unlock">ota_unlock</option>			   
				  <option value="ota_flash">ota_flash</option>			   
				</select>
				<button name='btnSend' class='m-2 btn btn-primary text-nowrap' type='submit'>Send request</button>
			</div>
			<div id='results' class='m-1'></div>
			<div>
				<div id='info' class='d-none bg-dark text-light border endpoint-details'>			
					<i>info has no parameters</i>
				</div>
				<div id='switch' class='d-none bg-dark text-light border endpoint-details rounded'>			
					<span class='m-1'>switch (on / off)</span>
					<div class="m-1 form-check form-switch">					
					  <input class="form-check-input" type="checkbox" role="switch" id="chkstate">
					  <label class="form-check-label" for="chkstate">Light state</label>			 
					</div>
				</div>			
				<div id='startup' class='d-none bg-dark text-light border endpoint-details rounded'>					
					<span class='m-1'>startup 
					startup_value=(on / off / stay)</span>
					<div class="m-1 form-check form-switch">					
					  <input class="form-check-input" type="checkbox" role="switch" id="setStartup" >
					  <label class="form-check-label" for="chkstate">Startup value</label>			 
					</div>
				</div>
				<div id='pulse' class='d-none bg-dark text-light border endpoint-details rounded'>							
					
					<span class='m-1'>inching settings (on / off)</span>
					<div class="m-1 form-check form-switch">					
					  <input class="form-check-input" type="checkbox" role="switch" id="setStartup" >
					  <label class="form-check-label" for="chkstate">Inching enabled</label>
					</div>
					<div class='m-1'>
						Duration
						<input id='pulseWidth_value'  name='pulseWidth_value' value='3000'>
					</div>
				</div>				
				<div id='signal_strength' class='d-none bg-dark text-light border endpoint-details rounded'>			
					<i>signal_strength has no parameters</i>
				</div>
				<div id='wifi' class='d-none bg-dark text-light border endpoint-details'>			
					wifi
					$ssid_value = '';
					$password_value = '';
					<div>	
						<input id='ssid_value'  name='ssid_value' value=''>
						<input id='password_value'  name='password_value' value=''>
					</div>
				</div>
				<div id='ota_unlock' class='d-none bg-dark text-light border endpoint-details rounded'>			
					<i>ota_unlock has no parameters</i>
				</div>
				<div id='ota_flash' class='d-none bg-dark text-light border endpoint-details rounded'>			
					ota_flash
					$downloadUrl_value = '';
					$sha256sum_value = '';
					<div>
						<input id='downloadUrl_value'  name='downloadUrl_value' value=''>
						<input id='sha256sum_value'  name='sha256sum_value' value=''>
					</div>
				</div>
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
		if(chkstate){
			document.getElementById("newState").value = 'on';
		} else {
			document.getElementById("newState").value = 'off';
		}
	});
	
	$('#endpoint-select').change(function(){
		var alldivs = $('.endpoint-details');
		alldivs.addClass("d-none");
		var showdiv = document.getElementById(this.value);
		showdiv.classList.remove("d-none");
		document.getElementById("endpoint").value = this.value;		
	});
	
	function lightClicked(){
		var chkstate = $('#chkstate').prop('checked');		 
		if(chkstate){
			//alert('Light is on');
			document.getElementById("newState").value = 'on';
			callDeviceAPI('switch','on');
			//callDeviceAPI('debug','on');
		} else {
			//alert('Light is off');
			document.getElementById("newState").value = 'off';
			callDeviceAPI('switch', 'off');
			//callDeviceAPI('debug','on');
		}		
	}
	
	function callDeviceAPI(endpoint, newState) {
		console.log('Starting ajax post');
		$('#spinner').removeClass("d-none");
		
	  $.ajax({
		type: "POST",
		url: "CallSonoffApi.php",
		timeout: 10000,
		data: {	
		  'endpoint':endpoint,
		  'newState':newState
		},
		
		success: function(result) {
		  $("#results").html(result);
		  console.log('ajax post complete');
		  $('#spinner').addClass("d-none");
		},
		error: function(xhr) {
		  console.log('****** ajax post error');
		  console.log(xhr.status);
		  console.log(xhr.statusText);		  
		  $('#spinner').addClass("d-none");
		  $('#light-status').removeClass("bg-yellow");
		  $('#light-status').addClass("bg-danger");
		}
	  });
	}
	
	function updateStatus(){
		callDeviceAPI('info','');
	}

  </script>  
  </body>
 </html>