<?php
/**
 * @package	Acymailing for Joomla!
 * @version	4.0.0
 * @author	deanimaconsulting.com
 * @copyright	(C) 2009-2012 De Anima Consulting Ltd. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

acymailing_cmsLoaded();

class ipinfodbInc{
	var $errors = array();
	var $service = 'api.ipinfodb.com';
	var $version = 'v3';
	var $apiKey = '';
	var $timeout = 5;

	function setKey($key){
		if(!empty($key)) $this->apiKey = $key;
	}
	function setTimeout($key){
		if(!empty($key)) $this->timeout = $key;
	}

	function getError(){
		return implode("\n", $this->errors);
	}

	function getCountry($host){
		return $this->getResult($host, 'ip-country');
	}

	function getCity($host){
		return $this->getResult($host, 'ip-city');
	}

	function getResult($host, $name){
		$ip = @gethostbyname($host);

		if(preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $ip)){
			return $this->curlRequest($ip, $name);
		}

		$this->errors[] = '"' . $host . '" is not a valid IP address or hostname.';
		return;
	}
	function curlRequest($ip, $name) {
		$qs = 'http://' . $this->service . '/' . $this->version . '/' . $name . '/' . '?ip=' . $ip . '&format=json&key=' . $this->apiKey;
		if(!function_exists('curl_init')){
			//$app->enqueueMessage('The AcyMailing geolocation plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			$this->errors[] = 'The AcyMailing geolocation plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.';
			return false;
		}
		if(!function_exists('json_decode')){
			//$app->enqueueMessage('The AcyMailing geolocation plugin can only work with PHP 5.2 at least. Please ask your web hosting to update your PHP version','error');
			$this->errors[] = 'The AcyMailing geolocation plugin can only work with PHP 5.2 at least. Please ask your web hosting to update your PHP version';
			return false;
		}
		if (!isset($this->curl)) {
			$this->curl = curl_init();
			curl_setopt ($this->curl, CURLOPT_FAILONERROR, TRUE);
			if (@ini_get('open_basedir') == '' && @ini_get('safe_mode' == 'Off')) {
				curl_setopt ($this->curl, CURLOPT_FOLLOWLOCATION, TRUE);
			}
			curl_setopt ($this->curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt ($this->curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			curl_setopt ($this->curl, CURLOPT_TIMEOUT, $this->timeout);
		}

		curl_setopt ($this->curl, CURLOPT_URL, $qs);

		$json = curl_exec($this->curl);

		if(curl_errno($this->curl) || $json === FALSE) {
			$this->errors[] = 'cURL failed. Error: ' . curl_error($this->curl);
			//$app->enqueueMessage('cURL failed. Error: ' . $err);
			return false;
		}

		$response = json_decode($json);

		return $response;
	}
}