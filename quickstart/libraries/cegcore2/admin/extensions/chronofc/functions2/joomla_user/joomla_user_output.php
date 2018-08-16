<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$name = $this->Parser->parse(trim($function['name_provider']), true);
	$username = $this->Parser->parse(trim($function['username_provider']), true);
	$password = $this->Parser->parse(trim($function['password_provider']), true);
	$email = $this->Parser->parse(trim($function['email_provider']), true);
	$block = $this->Parser->parse(trim($function['block_provider']), true);
	$activation = $this->Parser->parse(trim($function['activation_provider']), true);
	$groups = $this->Parser->parse(trim($function['groups_provider']), true);
	
	$userData = [
		'name' => trim($name),
		'username' => trim($username),
		'email' => $email,
		'password' => trim($password),
		'block' => $block,
		'activation' => trim($activation),
	];
	
	if(!empty($function['data_override'])){
		list($new_data) = $this->Parser->multiline($function['data_override']);
		
		if(is_array($new_data)){
			foreach($new_data as $new_data_line){
				
				if(!empty($new_data_line['valuep'])){
					$check_sign = $new_data_line['valuep'];
					$field_value = $this->Parser->parse($new_data_line['value'], true);
					
					if($check_sign == '+' AND empty($field_value)){
						$this->set($function['name'], false);
						$this->Parser->fevents[$function['name']]['fail'] = true;
						$this->Parser->debug[$function['name']]['_error'] = rl('Data save aborted because %s value is missing', [$new_data_line['name']]);
						return false;
					}
					
					if($check_sign == '-' AND empty($field_value)){
						if(isset($userData[$new_data_line['name']])){
							unset($userData[$new_data_line['name']]);
						}
						
						$this->Parser->debug[$function['name']][] = rl('The field %s value has been skipped', [$new_data_line['name']]);
						continue;
					}
				}
				
				$new_data_value = $this->Parser->parse($new_data_line['value'], true);
				$userData[$new_data_line['name']] = $new_data_value;
			}
		}
	}
	
	if(!empty($userData['password'])){
		//$userData['password'] = JUserHelper::hashPassword($userData['password']);
	}
	
	if(empty($userData['id']) AND empty($userData['registerDate'])){
		$userData['registerDate'] = \G2\L\Dater::datetime('Y-m-d H:i:s');
	}
	
	foreach(['name', 'username', 'password', 'email'] as $req){
		if(isset($userData[$req]) AND empty($userData[$req])){
			$this->Parser->debug[$function['name']]['_error'] = rl('Missing user data.');
			$this->set($function['name'], false);
			$this->Parser->fevents[$function['name']]['fail'] = true;
			return;
		}
	}
	
	$userModel = new \G2\A\M\User();
	
	if(empty($userData['id'])){
		//check if username/email are unique
		$exists = $userModel->where('username', $username)->where('OR')->where('email', $email)->select('first');
		
		if(!empty($exists)){
			if(!isset($function['userexists_error']) OR !empty($function['userexists_error'])){
				$this->Parser->messages['error'][$function['name']][] = $this->Parser->parse($function['userexists_error'], true);
			}
			$this->Parser->debug[$function['name']]['_error'] = rl('A user with the same username or email already exists.');
			$this->set($function['name'], false);
			$this->Parser->fevents[$function['name']]['fail'] = true;
			return;
		}
	}
	//save the user
	$userSave = $userModel->save($userData);
	
	if($userSave !== false){
		$user_id = $userModel->id;
		$userData['id'] = $user_id;
		
		if(!empty($groups)){
			$groups = (array)$groups;
			$groups = array_filter(array_unique($groups));
			
			$userGroupModel = new \G2\A\M\GroupUser();
			
			foreach($groups as $group){
				$groupSave = $userGroupModel->insert(['group_id' => $group, 'user_id' => $user_id]);
				
				if($groupSave === false){
					$this->Parser->debug[$function['name']]['_error'] = rl('Error assignning the user to a group.');
					$this->set($function['name'], false);
					$this->Parser->fevents[$function['name']]['fail'] = true;
					return;
				}
			}
		}
		
		$this->set($function['name'], $userData);
		$this->Parser->fevents[$function['name']]['success'] = true;
		$this->Parser->debug[$function['name']]['_success'] = rl('User saved successfully under id %s', [$user_id]);
	}else{
		$this->Parser->debug[$function['name']]['_error'] = rl('Error saving user.');
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		return;
	}