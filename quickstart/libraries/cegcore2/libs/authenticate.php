<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Authenticate {
	static $public_groups;
	
	function __construct(){
		
	}
	
	public static function get_public_groups(){
		if(empty(self::$public_groups)){
			$settings = \GApp::extension('users')->settings();
			self::$public_groups = $settings->get('public_groups', array(1, 2));
		}
		return self::$public_groups;
	}
	
	public static function get_user_groups($user_id = null){
		if(is_null($user_id)){
			$user = \GApp::user();
			$usergroups = $user->get('groups');
			$inheritance = ($user->get('inheritance') AND is_array($user->get('inheritance'))) ? $user->get('inheritance') : array();
			
			$groups = [];
			foreach($inheritance as $g){
				$groups[$g] = 0;
			}
			foreach($usergroups as $g){
				$groups[$g] = 1;
			}
			//$groups = array_unique(array_merge($usergroups, $inheritance)); // may comment this line to disable inheritance
		}else{
			$user_model = new \G2\A\M\User();
			$user = $user_model->find('first', array(
				'conditions' => array('id' => $user_id),
			));
			if(!empty($user)){
				$groups = Arr::getVal($user, array('GroupUser', '[n]', 'group_id'), self::get_public_groups());
				$user_groups_paths = Arr::getVal($user, array('Group', '[n]', 'path'), array());
				$user_inheritance = array();
				foreach($user_groups_paths as $user_groups_path){
					$user_inheritance = array_merge($user_inheritance, array_filter(explode('.', $user_groups_path)));
				}
				$user_inheritance = array_unique($user_inheritance);
				$groups = array_unique(array_merge($groups, $user_inheritance)); // may comment this line to disable inheritance
				
				$user = $user['User'];
				if(!empty($user['activation'])){
					return self::get_public_groups();
				}
				if($user['blocked'] == 1){
					return self::get_public_groups();
				}
			}
		}
		return $groups;
	}
	
	public static function set_public_user(){
		$p_gs = self::get_public_groups();
		
		$Group = new \G2\A\M\Group();
		$infos = $Group->fields(['Group.id', 'Group.path'])->where('Group.id', $p_gs, 'in')->select('list');
		$d_inh = array();
		foreach($infos as $info){
			$d_inh = $d_inh + explode('.', $info);
		}
		$inheritance = array_values(array_filter(array_unique($d_inh)));
		$user = array('id' => 0, 'logged_in' => 0, 'guest' => 1, 'groups' => $p_gs, 'inheritance' => $inheritance);
		$session = \GApp::session();
		$session->set('user', $user);
		return $user;
	}
	
	public static function is_logged_in(){
		$session = \GApp::session();
		$user = $session->get('user', null);
		return !empty($user['id']) AND !empty($user['logged_in']);
	}
	
	public static function hash_password($password){
		$salt = Str::rand();
		return $password = sha1($salt.$password).':'.$salt;
	}
	
	public static function check_password($user_password, $db_password){
		$chunks = explode(':', $db_password);
		$salt = $chunks[1];
		return (sha1($salt.$user_password) == $chunks[0]);
	}
	
	public static function login($credentials){
		$session = \GApp::session();
		$username_field = \GApp::config()->get('username.field', 'username');
		if(isset($credentials[$username_field]) AND isset($credentials['password'])){
			$user_model = new \G2\A\M\User();
			$user = $user_model->find('first', array(
				'conditions' => array($username_field => $credentials[$username_field]),
			));
			if(!empty($user)){
				$user_groups = Arr::getVal($user, array('GroupUser', '[n]', 'group_id'), self::get_public_groups());
				$user_groups_paths = Arr::getVal($user, array('Group', '[n]', 'path'), array());
				$user_inheritance = array();
				foreach($user_groups_paths as $user_groups_path){
					$user_inheritance = array_merge($user_inheritance, array_filter(explode('.', $user_groups_path)));
				}
				$user_inheritance = array_unique($user_inheritance);
				
				$user = $user['User'];				
				$password_correct = self::check_password($credentials['password'], $user['password']);
				if(!$password_correct){
					$session->setFlash('error', l_('AUTHENTICATE_INCORRECT_LOGIN_CREDENTIALS'));
					return false;
				}
				if(!empty($user['activation'])){
					$session->setFlash('error', l_('AUTHENTICATE_ACCOUNT_NOT_ACTIVATED'));
					return false;
				}
				if($user['blocked'] == 1){
					$session->setFlash('error', l_('AUTHENTICATE_ACCOUNT_BLOCKED'));
					return false;
				}
				//account is found and can login, insert session data
				$user_session = array();
				$user_session['id'] = $user['id'];
				$user_session['name'] = $user['name'];
				$user_session['username'] = $user['username'];
				$user_session['email'] = $user['email'];
				$user_session['last_login'] = $user['last_visit'];
				$user_session['logged_in'] = 1;
				$user_session['groups'] = $user_groups;
				$user_session['inheritance'] = $user_inheritance;
				//get referer
				$referer = $session->get('_referer');
				
				$session->restart();
				$session->set('_referer', $referer);
				$session->set('user', array_merge($session->get('user', array()), $user_session));
				if(\GApp::config()->get('session.handler', 'php') == 'database'){
					$session_model = new \G2\A\M\Session();
					//$update = $session_model->updateAll(array('user_id' => $user['id'], 'site' => GCORE_SITE), array('session_id' => $session->get_id()));
					$insert_status = $session_model->save(array(
						'session_id' => $session->get_id(), 
						'user_id' => $user['id'],
						'site' => GCORE_SITE,
						'ip_address' => $_SERVER['REMOTE_ADDR'], 
						'user_agent' => $_SERVER['HTTP_USER_AGENT'], 
						'last_activity' => time()
						), array('new' => true)
					);
				}
				//update last visit
				$user_model->updateAll(array('last_visit' => \G2\L\Dater::datetime('Y-m-d H:i:s', 'now')), array('id' => $user['id']), array('modified' => false));
				//after login hook
				$hook_results = Event::trigger('on_after_user_login');
				return true;
			}else{				
				$session->setFlash('error', l_('AUTHENTICATE_INCORRECT_LOGIN_CREDENTIALS'));
				return false;
			}
		}else{
			return false;
		}
	}
	
	function logout(){
		$session = \GApp::session();
		$referer = $session->get('_referer');		
		$result = $session->restart();
		$session->set('_referer', $referer);
		self::set_public_user();
		return $result;
	}
	
}