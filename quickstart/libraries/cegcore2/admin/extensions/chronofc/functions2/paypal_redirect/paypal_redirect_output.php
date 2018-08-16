<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(!empty($function['sandbox'])){
		$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
	}else{
		$url = 'https://www.paypal.com/cgi-bin/webscr?';
	}
	
	//$settings = \GApp::extension()->settings();
	if(!\GApp::extension()->valid('paypal') AND !\GApp::extension()->valid('extras')){
		$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
	}
	
	$vars = [
		'cmd' => $function['cmd'],
		'business' => $this->Parser->parse($function['business'], true),
		'currency_code' => $this->Parser->parse($function['currency_code'], true),
		'quantity' => $this->Parser->parse($function['quantity'], true),
		
		'item_name' => $this->Parser->parse($function['item_name'], true),
		'item_number' => $this->Parser->parse($function['item_number'], true),
		'amount' => $this->Parser->parse($function['amount'], true),
		'shipping' => $this->Parser->parse($function['shipping'], true),
		'shipping2' => $this->Parser->parse($function['shipping2'], true),
		'handling' => $this->Parser->parse($function['handling'], true),
		'on0' => $this->Parser->parse($function['on0'], true),
		'os0' => $this->Parser->parse($function['os0'], true),
		'on1' => $this->Parser->parse($function['on1'], true),
		'os1' => $this->Parser->parse($function['os1'], true),
		
		'tax' => $this->Parser->parse($function['tax'], true),
		'no_shipping' => $this->Parser->parse($function['no_shipping'], true),
		'no_note' => $this->Parser->parse($function['no_note'], true),
		'cn' => $this->Parser->parse($function['cn'], true),
		'notify_url' => $this->Parser->parse($function['notify_url'], true),
		'return' => $this->Parser->parse($function['return'], true),
		'cancel_return' => $this->Parser->parse($function['cancel_return'], true),
		'image_url' => $this->Parser->parse($function['image_url'], true),
		'custom' => $this->Parser->parse($function['custom'], true),
		'invoice' => $this->Parser->parse($function['invoice'], true),
		
		'email' => $this->Parser->parse($function['email'], true),
		'first_name' => $this->Parser->parse($function['first_name'], true),
		'last_name' => $this->Parser->parse($function['last_name'], true),
		'address1' => $this->Parser->parse($function['address1'], true),
		'address2' => $this->Parser->parse($function['address2'], true),
		'city' => $this->Parser->parse($function['city'], true),
		'state' => $this->Parser->parse($function['state'], true),
		'zip' => $this->Parser->parse($function['zip'], true),
		'country' => $this->Parser->parse($function['country'], true),
		'lc' => $this->Parser->parse($function['lc'], true),
	];
	
	if($function['cmd'] == '_cart'){
		$vars['upload'] = 1;
	}else if($function['cmd'] == '_ext-enterd'){
		//$vars['redirect_cmd'] = '_xclick';
		$vars['cmd'] = '_xclick';
	}
	
	$data = [];
	
	$multiple_fields = [
		'item_name',
		'item_number',
		'amount',
		'shipping',
		'shipping2',
		'handling',
		'on0',
		'os0',
		'on1',
		'os1',
		'quantity',
	];
	
	if(is_array($vars['item_name'])){
		
		foreach($multiple_fields as $multiple_field){
			if(!is_array($vars[$multiple_field])){
				$vars[$multiple_field] = array_fill(0, count($vars['item_name']), $vars[$multiple_field]);
			}
			$vars[$multiple_field] = array_values($vars[$multiple_field]);
		}
		
		foreach($multiple_fields as $multiple_field){
			foreach($vars['item_name'] as $k => $item){
				$vars[$multiple_field.'_'.($k + 1)] = $vars[$multiple_field][$k];
			}
		}
		
		foreach($multiple_fields as $multiple_field){
			unset($vars[$multiple_field]);
		}
	}
	
	foreach($vars as $key => $var){
		$data[$key] = $var;
	}
	
	$query = http_build_query($data);
	
	$url = $url.$query;
	
	if(!empty($function['debug'])){
		echo $url;
		$this->Parser->debug[$function['name']]['data'] = $data;
		
	}else{
		$this->Parser->end();
		
		if(empty(\GApp::instance()->tvout)){
			\G2\L\Env::redirect($url);
		}else{
			echo '
			<script type="text/javascript">
				jQuery(document).ready(function($){
					window.location = "'.r2($url, false, true).'";
				});
			</script>';
		}
	}