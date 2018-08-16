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

class FilterController extends acymailingController{
	var $pkey = 'filid';
	var $table = 'filter';

	function listing(){
		return $this->add();
	}

	function countresults(){
		$num = acymailing_getVar('int', 'num');
		$filters = acymailing_getVar('none', 'filter');

		foreach($filters['type'] as $block => $oneType){
			if(!empty($oneType[$num])){
				$currentType = $oneType[$num];
				break;
			}
		}
		if(empty($currentType)) die('No filter type found for the num '.intval($num));
		if(empty($filters[$num][$currentType])) die('No filter parameters found for the num '.intval($num));

		$filterClass = acymailing_get('class.filter'); // Keep it, it loads the acyQuery class
		$query = new acyQuery();

		$currentFilterData = $filters[$num][$currentType];
		acymailing_importPlugin('acymailing');
		$messages = acymailing_trigger('onAcyProcessFilterCount_'.$currentType, array(&$query,$currentFilterData,$num));
		echo implode(' | ',$messages);
		exit;
	}

	function displayCondFilter(){
		acymailing_importPlugin('acymailing');
		$fct = acymailing_getVar('none', 'fct');

		$message = acymailing_trigger('onAcyTriggerFct_'.$fct);
		echo implode(' | ',$message);
		exit;
	}

	function process(){
		if(!$this->isAllowed('lists','filter')) return;
		acymailing_checkToken();

		$filid = acymailing_getVar('int', 'filid');
		if(!empty($filid)){
			$this->store();
		}

		$filterClass = acymailing_get('class.filter');
		$filterClass->subid = acymailing_getVar('string', 'subid');
		$filterClass->execute(acymailing_getVar('none', 'filter'),acymailing_getVar('none', 'action'), 100000);

		if(!empty($filterClass->report)){
			if(acymailing_isNoTemplate()){
				acymailing_display($filterClass->report,'info');
				return;
			}else{
				foreach($filterClass->report as $oneReport){
					acymailing_enqueueMessage($oneReport);
				}
			}
		}
		return $this->edit();
	}

	function filterDisplayUsers(){
		if(!$this->isAllowed('lists','filter')) return;
		acymailing_checkToken();
		return $this->edit();
	}

	function store(){
		if(!$this->isAllowed('lists','filter')) return;
		acymailing_checkToken();

		$class = acymailing_get('class.filter');
		$status = $class->saveForm();
		if($status){
			acymailing_enqueueMessage(acymailing_translation( 'JOOMEXT_SUCC_SAVED' ), 'message');
		}else{
			acymailing_enqueueMessage(acymailing_translation( 'ERROR_SAVING' ), 'error');
			if(!empty($class->errors)){
				foreach($class->errors as $oneError){
					acymailing_enqueueMessage($oneError, 'error');
				}
			}
		}
	}
}
