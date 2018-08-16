<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\Joomla;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class User extends \G2\L\User{
	
	public static function login($username, $password){
		$mainframe = \JFactory::getApplication();
		$credentials = array();
		$credentials['username'] = $username;
		$credentials['password'] = $password;
		
		return ($mainframe->login($credentials) === true);
	}
	
	public static function save($data, $update = false){
		foreach($data as $k => $v){
			if(is_string($v)){
				$data[$k] = trim($v);
			}
		}
		
		if(!empty($data['id']) AND empty($update)){
			unset($data['id']);
		}
		
		if(!empty($data['password'])){
			//$data['password'] = \JUserHelper::hashPassword($data['password']);
		}
		
		if(!empty($data['email']) AND empty(\G2\L\Validate::email($data['email']))){
			return 11;
		}
		
		if(empty($data['id']) AND empty($data['registerDate'])){
			$data['registerDate'] = \G2\L\Dater::datetime('Y-m-d H:i:s');
		}
		
		foreach(['name', 'username', 'password', 'email'] as $req){
			if(empty($data['id']) AND empty($data[$req])){
				return 1;
			}else if(!empty($data['id']) AND empty($data[$req])){
				unset($data[$req]);
			}
		}
		
		$userModel = new \G2\A\M\User();
		
		if(empty($data['id'])){
			//check if username/email are unique
			$exists = $userModel->where('username', $data['username'])->where('OR')->where('email', $data['email'])->select('first');
			
			if(!empty($exists)){
				return 2;
			}
		}else{
			if(!empty($data['username']) OR !empty($data['email'])){
				$exists = $userModel
				->where('(')
				->where('username', $data['username'])
				->where('OR')
				->where('email', $data['email'])
				->where(')')
				->where('AND')
				->where('id', $data['id'], '!=')
				->select('first');
				
				if(!empty($exists)){
					return 2;
				}
			}
		}
		//save the user
		$userSave = $userModel->save($data);
		
		if($userSave !== false){
			$user_id = $userModel->id;
			$data['id'] = $user_id;
			
			if(!empty($data['groups'])){
				$groups = (array)$data['groups'];
				$groups = array_filter(array_unique($groups));
				
				$userGroupModel = new \G2\A\M\GroupUser();
				
				foreach($groups as $group){
					$groupSave = $userGroupModel->insert(['group_id' => $group, 'user_id' => $user_id]);
					
					if($groupSave === false){
						return 4;
					}
				}
			}
			
			return $data;
		}else{
			return 3;
		}
	}
}