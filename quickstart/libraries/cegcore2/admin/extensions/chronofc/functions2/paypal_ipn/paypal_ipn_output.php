<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(!count($_POST)){
		$this->Parser->debug[$function['name']]['_error'] = rl('Missing post data.');
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		return;
	}

	$raw_post_data = file_get_contents('php://input');
	$raw_post_array = explode('&', $raw_post_data);
	$myPost = array();
	foreach ($raw_post_array as $keyval) {
		$keyval = explode('=', $keyval);
		if(count($keyval) == 2){
			// Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it.
			if($keyval[0] === 'payment_date'){
				if(substr_count($keyval[1], '+') === 1){
					$keyval[1] = str_replace('+', '%2B', $keyval[1]);
				}
			}
			$myPost[$keyval[0]] = urldecode($keyval[1]);
		}
	}

	// Build the body of the verification post request, adding the _notify-validate command.
	$req = 'cmd=_notify-validate';
	
	foreach($myPost as $key => $value){
		$value = urlencode($value);
		$req .= "&$key=$value";
	}

	// Post the data back to PayPal, using curl. Throw exceptions if errors occur.
	if(!empty($function['sandbox'])){
		$vurl= 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
	}else{
		$vurl= 'https://ipnpb.paypal.com/cgi-bin/webscr';
	}
	
	if(!\GApp::extension()->valid('paypal') AND !\GApp::extension()->valid('extras')){
		$vurl= 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
	}
	
	$ch = curl_init($vurl);
	
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	curl_setopt($ch, CURLOPT_SSLVERSION, 6);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

	// This is often required if the server is missing a global cert bundle, or is using an outdated one.
	/*if($this->use_local_certs) {
		curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cert/cacert.pem");
	}*/
	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
	$res = curl_exec($ch);
	if(!($res)){
		$errno = curl_errno($ch);
		$errstr = curl_error($ch);
		curl_close($ch);
		//throw new Exception("cURL error: [$errno] $errstr");
		$this->Parser->debug[$function['name']]['_error'] = 'cURL error: '.$errno.' '.$errstr;
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		return;
	}

	$info = curl_getinfo($ch);
	$http_code = $info['http_code'];
	if($http_code != 200){
		//throw new Exception("PayPal responded with http code $http_code");
		$this->Parser->debug[$function['name']]['_error'] = 'PayPal responded with http code '.$http_code;
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		return;
	}

	curl_close($ch);

	// Check if PayPal verifies the IPN data, and if so, return true.
	if($res == 'VERIFIED'){
		$this->set($function['name'], true);
		
		
		if($this->data('payment_status') == 'Completed'){
			if(empty($function['receiver_email']) OR ($function['receiver_email'] == $this->data('receiver_email'))){
				$this->Parser->fevents[$function['name']]['success'] = true;
			}else{
				$this->set($function['name'], false);
				$this->Parser->fevents[$function['name']]['fail'] = true;
				$this->Parser->debug[$function['name']]['_error'] = 'INVALID received email';
			}
		}else{
			$this->Parser->fevents[$function['name']][strtolower($this->data('payment_status'))] = true;
		}
	}else{
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		$this->Parser->debug[$function['name']]['_error'] = 'INVALID response:'.$res;
		return;
	}