<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$activation = $this->Parser->parse(trim($function['activation_provider']), true);
	$block = $this->Parser->parse(trim($function['block_provider']), true);
	
	if(empty($activation)){
		$this->Parser->debug[$function['name']]['_error'] = rl('Missing activation data.');
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		return;
	}
	
	$activation = trim($activation);
	
	if(!empty($function['data_override'])){
		list($new_data) = $this->Parser->multiline($function['data_override']);
		
		if(is_array($new_data)){
			foreach($new_data as $new_data_line){
				$new_data_value = $this->Parser->parse($new_data_line['value'], true);
				$userData[$new_data_line['name']] = $new_data_value;
			}
		}
	}
	
	$userModel = new \G2\A\M\User();
	//check if username/email are unique
	$exists = $userModel->where('activation', $activation)->select('first');
	
	if(empty($exists)){
		//$this->Parser->messages['error'][$function['name']][] = rl('The activation code does not exist or the account is already active.');
		$this->Parser->debug[$function['name']]['_error'] = rl('A user with this activation code does not exist.');
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		return;
	}
	//save the user
	$userSave = $userModel->where('id', $exists['User']['id'])->update(['activation' => '', 'block' => $block]);
	
	if($userSave !== false){
		$this->set($function['name'], $exists['User']);
		$this->Parser->fevents[$function['name']]['success'] = true;
		$this->Parser->debug[$function['name']]['_success'] = rl('User activated successfully.');
	}else{
		$this->Parser->debug[$function['name']]['_error'] = rl('Error updating user account.');
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		return;
	}