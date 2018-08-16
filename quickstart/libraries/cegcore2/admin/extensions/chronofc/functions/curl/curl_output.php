<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	
	if(!empty($function['url'])){
		
		$url = $this->Parser->parse(trim($function['url']), true);
		
		if(!empty($function['data_provider'])){
			$data = $this->Parser->parse($function['data_provider'], true);
		}else{
			$data = [];
		}
		
		if(!empty($function['data_override'])){
			list($new_data) = $this->Parser->multiline($function['data_override']);
			
			if(is_array($new_data)){
				foreach($new_data as $new_data_line){
					$new_data_value = $this->Parser->parse($new_data_line['value'], true);
					$data[$new_data_line['name']] = $new_data_value;
				}
			}
		}
		
		$query = http_build_query($data);
		
		$this->Parser->debug[$function['name']]['url'] = $url;
		$this->Parser->debug[$function['name']]['query'] = $query;
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, (int)$function['header']);// set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);// use HTTP POST to send form data
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($ch);//execute post and get results
		
		$this->Parser->debug[$function['name']]['errors'] = curl_error($ch);
		$this->Parser->debug[$function['name']]['info'] = print_r(curl_getinfo($ch), true);
		
		curl_close($ch);
		
		$this->set($function['name'], $response);
		$this->Parser->fevents[$function['name']]['success'] = true;
	}else{
		$this->Parser->debug[$function['name']]['_error'] = rl('No URL is provided.');
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
	}