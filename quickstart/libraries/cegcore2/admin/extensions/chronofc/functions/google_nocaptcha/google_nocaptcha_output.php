<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	
	if(!empty($function['secret_key'])){
		
		if(ini_get('allow_url_fopen')){
			$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$function['secret_key'].'&response='.$this->data('g-recaptcha-response'));
		}else{
			$ch = curl_init('https://www.google.com/recaptcha/api/siteverify?secret='.$function['secret_key'].'&response='.$this->data('g-recaptcha-response'));
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$response = curl_exec($ch);
			curl_close($ch);
		}
		
		$response = json_decode($response, true);
		
		$this->Parser->debug[$function['name']]['response'] = $response;
		
		if($response['success'] === true){
			$this->Parser->debug[$function['name']]['_success'] = rl('The NoCaptcha verification was successfull.');
			$this->set($function['name'], true);
			$this->Parser->fevents[$function['name']]['success'] = true;
			return;
		}else{
			$this->Parser->messages['error'][$function['name']][] = $this->Parser->parse($function['failed_error'], true);
			
			$this->Parser->debug[$function['name']]['_error'] = rl('The NoCaptcha verification has failed.');
			$this->set($function['name'], false);
			$this->Parser->fevents[$function['name']]['fail'] = true;
			return;
		}
		
	}else{
		$this->Parser->messages['error'][$function['name']][] = $this->Parser->parse($function['failed_error'], true);
		
		$this->Parser->debug[$function['name']]['_error'] = rl('No secret key is provided.');
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
	}