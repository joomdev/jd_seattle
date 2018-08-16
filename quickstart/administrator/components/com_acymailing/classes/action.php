<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class actionClass extends acymailingClass{

	var $tables = array('action');
	var $pkey = 'action_id';

	function getActions($index = '', $actionIds = 'all'){
		$onlyActionIds = array();
		if(strtolower($actionIds) != 'all'){
			$onlyActionIds = explode(',', $actionIds);
			acymailing_arrayToInteger($onlyActionIds);
		}

		return acymailing_loadObjectList('SELECT * FROM '.acymailing_table('action').(empty($onlyActionIds) ? '' : ' WHERE listid IN ('.implode(',', $onlyActionIds).')').' ORDER BY ordering ASC', $index);
	}

	function delete($elements){
		if(!is_array($elements)) $elements = array($elements);
		acymailing_arrayToInteger($elements);
		if(empty($elements)) return 0;

		return parent::delete($elements);
	}

	function get($actionid, $default = null){
		$query = 'SELECT a.*, b.'.$this->cmsUserVars->name.' AS creatorname, b.'.$this->cmsUserVars->username.' AS creatorusername, b.'.$this->cmsUserVars->email.' AS email FROM '.acymailing_table('action').' AS a LEFT JOIN '.acymailing_table($this->cmsUserVars->table, false).' AS b on a.userid = b.'.$this->cmsUserVars->id.' WHERE action_id = '.intval($actionid).' LIMIT 1';
		return acymailing_loadObject($query);
	}

	function saveForm(){
		$action = new stdClass();
		$action->action_id = acymailing_getCID('action_id');

		$formData = acymailing_getVar('array', 'data', array(), '');

		foreach($formData['action'] as $column => $value){
			if(acymailing_isAdmin()){
				acymailing_secureField($column);
				$action->$column = strip_tags($value);
			}
		}
		if(!empty($action->username)) $action->username = acymailing_punycode($action->username);

		if(empty($action->action_id)) $action->nextdate = time() + intval($action->frequency);
		if($action->password == '********') unset($action->password);

		$action->conditions = json_encode($formData['conditions']);
		$action->actions = json_encode($formData['actions']);

		if(isset($action->published) && $action->published != 1) $action->published = 0;
		$action_id = $this->save($action);
		if(!$action_id) return false;

		acymailing_setVar('action_id', $action_id);
		return true;
	}

	function save($action){
		if(empty($action->action_id) && empty($action->userid)){
			$action->userid = acymailing_currentUserId();
		}

		acymailing_importPlugin('acymailing');
		if(empty($action->action_id)){
			acymailing_trigger('onAcyBeforeActionCreate', array(&$action));
			$status = acymailing_insertObject(acymailing_table('action'), $action);
		}else{
			acymailing_trigger('onAcyBeforeActionModify', array(&$action));
			$status = acymailing_updateObject(acymailing_table('action'), $action, 'action_id');
		}

		if($status) return empty($action->action_id) ? $status : $action->action_id;
		return false;
	}
}
