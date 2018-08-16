<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Antispam{
	public function Antispam(){
		return AntispamObject::getInstance($this);
	}
	
}

class AntispamObject extends \G2\L\Component{
	
	function recaptcha($settings){
		if(empty($settings['secret_key'])){
			return false;
		}
		
		if(ini_get('allow_url_fopen')){
			$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$settings['secret_key'].'&response='.$this->data('g-recaptcha-response'));
		}else{
			$ch = curl_init('https://www.google.com/recaptcha/api/siteverify?secret='.$settings['secret_key'].'&response='.$this->data('g-recaptcha-response'));
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$response = curl_exec($ch);
			curl_close($ch);
		}
		
		$response = json_decode($response, true);
		
		if($response['success'] === true){
			return true;
		}else{
			return false;
		}
	}
	
}
?>