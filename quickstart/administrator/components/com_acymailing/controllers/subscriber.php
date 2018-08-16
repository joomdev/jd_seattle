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

class SubscriberController extends acymailingController{

	var $pkey = 'subid';
	var $allowedInfo = array();
	var $aclCat = 'subscriber';

	function choose(){
		if(!$this->isAllowed('subscriber', 'view')) return;
		acymailing_setVar('layout', 'choose');
		return parent::display();
	}

	function export(){
		if(!$this->isAllowed('subscriber', 'export')) return;
		$cids = acymailing_getVar('none', 'cid');
		$selectedList = acymailing_getVar('int', 'filter_lists');
		$_SESSION['acymailing'] = array();
		$redirection = (acymailing_isAdmin() ? '' : 'front').'data&task=export';
		if(!empty($cids) || !empty($selectedList)){
			if(!empty($cids)){
				$_SESSION['acymailing']['exportusers'] = $cids;
			}else{
				$_SESSION['acymailing']['exportlist'] = $selectedList;
				$_SESSION['acymailing']['exportliststatus'] = acymailing_getVar('int', 'filter_statuslist');
			}
			$redirection .= '&sessionvalues=1';
		}


		acymailing_redirect(acymailing_completeLink($redirection, false, true));
	}

	function store(){
		if(!$this->isAllowed('subscriber', 'manage')) return;
		acymailing_checkToken();

		$subscriberClass = acymailing_get('class.subscriber');
		$subscriberClass->sendConf = false;
		$subscriberClass->sendNotif = false;
		$subscriberClass->sendWelcome = false;
		$subscriberClass->allowModif = true;
		$subscriberClass->checkAccess = false;
		$subscriberClass->triggerFilterBE = true;
		$subscriberClass->checkVisitor = false;

		$status = $subscriberClass->saveForm();
		if($status){
			acymailing_enqueueMessage(acymailing_translation('JOOMEXT_SUCC_SAVED'), 'message');
		}else{
			acymailing_enqueueMessage(acymailing_translation('ERROR_SAVING'), 'error');
			if(!empty($subscriberClass->errors)){
				foreach($subscriberClass->errors as $oneError){
					acymailing_enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		acymailing_checkToken();
		$config = acymailing_config();
		$deleteBehaviour = $config->get('frontend_delete_button', 'delete');
		$subscriberIds = acymailing_getVar('array', 'cid', array(), '');
		if(acymailing_isAdmin() || $deleteBehaviour == 'delete'){
			if(!$this->isAllowed('subscriber', 'delete')) return;

			$subscriberObject = acymailing_get('class.subscriber');
			$num = $subscriberObject->delete($subscriberIds);

			acymailing_enqueueMessage(acymailing_translation_sprintf('SUCC_DELETE_ELEMENTS', $num), 'message');
		}else{
			if(!$this->isAllowed('subscriber', 'manage')) return;

			$listId = acymailing_getVar('int', 'filter_lists', 0);
			if(empty($listId)){
				acymailing_enqueueMessage('List not found', 'error');
			}else{
				$listsubClass = acymailing_get('class.listsub');
				foreach($subscriberIds as $subid){
					$listsubClass->removeSubscription($subid, array($listId));
				}

				$listClass = acymailing_get('class.list');
				$list = $listClass->get($listId);

				acymailing_enqueueMessage(acymailing_translation_sprintf('IMPORT_REMOVE', count($subscriberIds), $list->name), 'message');
			}
		}

		acymailing_setVar('layout', 'listing');
		return parent::display();
	}

	function getSubscribersByEmail(){
		$NameSearched = acymailing_getVar('string', 'search', '');
		if(empty($NameSearched) || !acymailing_isAdmin() || !$this->isAllowed('subscriber', 'view')) exit;

		$NameSearched = '\'%'.acymailing_getEscaped($NameSearched, true).'%\'';
		$users = acymailing_loadObjectList('SELECT name, email FROM #__acymailing_subscriber WHERE email LIKE '.$NameSearched.' OR name LIKE '.$NameSearched.' ORDER BY email ASC LIMIT 30');
		if(empty($users)) exit;

		echo '<table style="width:100%;">';
		foreach($users as $oneUser){
			echo '<tr class="row_user" onclick="setUser(\''.str_replace("'", "\'", $oneUser->email).'\');"><td>'.htmlspecialchars($oneUser->name, ENT_COMPAT, 'UTF-8').'</td><td>'.htmlspecialchars($oneUser->email, ENT_COMPAT, 'UTF-8').'</td></tr>';
		}
		echo '</table>';
		exit;
	}

	function exportdata(){
		if(!$this->isAllowed('subscriber', 'export')) return;
		acymailing_checkToken();

		$id = acymailing_getCID();

		$userHelper = acymailing_get('helper.user');
		$userHelper->exportdata($id);
	}
}
