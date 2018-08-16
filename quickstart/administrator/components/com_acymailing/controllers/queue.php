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

class QueueController extends acymailingController{

	var $aclCat = 'queue';

	function remove(){
		if(!$this->isAllowed($this->aclCat, 'delete')) return;
		acymailing_checkToken();
		$mailid = acymailing_getVar('int', 'filter_mail', 0, 'post');

		$queueClass = acymailing_get('class.queue');
		$search = acymailing_getVar('string', 'search');
		$filters = array();
		if(!empty($search)){
			$searchVal = '\'%'.acymailing_getEscaped($search, true).'%\'';
			$searchFields = array('b.name', 'b.email', 'c.subject', 'a.mailid', 'a.subid');
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		if(!empty($mailid)){
			$filters[] = 'a.mailid = '.intval($mailid);
		}

		$total = $queueClass->delete($filters);
		acymailing_enqueueMessage(acymailing_translation_sprintf('SUCC_DELETE_ELEMENTS', $total), 'message');
		acymailing_setVar('filter_mail', 0, 'post');
		acymailing_setVar('search', '', 'post');

		return $this->listing();
	}

	function process(){
		if(!$this->isAllowed($this->aclCat, 'process')) return;
		acymailing_setVar('layout', 'process');
		return parent::display();
	}

	function preview(){
		acymailing_setVar('layout', 'preview');
		return parent::display();
	}

	function cancelNewsletter(){
		if(!$this->isAllowed($this->aclCat, 'delete')) return;
		acymailing_checkToken();
		$mailid = acymailing_getVar('int', 'mailid', 0);
		if(empty($mailid)){
			acymailing_enqueueMessage('Mail id not found', 'error');
			return;
		}
		$queueClass = acymailing_get('class.queue');
		acymailing_enqueueMessage(acymailing_translation_sprintf('SUCC_DELETE_ELEMENTS', $queueClass->delete(array('a.mailid = '.$mailid))), 'info');
	}
}
