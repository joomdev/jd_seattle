<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\SessionHandlers;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Database extends \G2\L\SessionHandler {
	
	function read($sess_id){
		$session_model = new \G2\A\M\Session();
		$data = $session_model->find('first', array(
			'conditions' => array('Session.session_id' => $sess_id),
			'fields' => array('Session.data'),
		));
		
		if(!empty($data['Session'])){
			return (string)$data['Session']['data'];
		}
		return '';
	}
	
	function write($sess_id, $data){
		$session_model = new \G2\A\M\Session();
		$update = $session_model->field('session_id', array('session_id' => $sess_id));
		if(!empty($update)){
			return $update_status = $session_model->updateAll(array('data' => $data, 'last_activity' => time()), array('session_id' => $sess_id));
		}else{
			return $insert_status = $session_model->save(array('session_id' => $sess_id, 'ip_address' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'data' => $data, 'last_activity' => time()), array('new' => true));
		}
	}

	function destroy($sess_id){
		$session_model = new \G2\A\M\Session();
		return $session_model->delete($sess_id);
	}
	
	function gc($max_life_time = 1440){
		$session_model = new \G2\A\M\Session();
		$oldest = time() - $max_life_time;
		return $session_model->deleteAll(array('last_activity <' => $oldest));
	}
}