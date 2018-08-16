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

class fieldsClass extends acymailingClass{

	var $tables = array('fields');
	var $pkey = 'fieldid';
	var $errors = array();
	var $prefix = 'field_';
	var $suffix = '';
	var $excludeValue = array();
	var $formoption = '';

	var $labelClass = '';

	var $dispatcher;

	var $currentUserEmail;

	var $origin;

	function __construct($config = array()){
		acymailing_importPlugin('acymailing');
		return parent::__construct($config);
	}

	function getFields($area, &$user){

		if(empty($user)) $user = new stdClass();

		$where = array();
		$where[] = 'a.`published` = 1';
		if($area == 'backend'){
			$where[] = 'a.`backend` = 1';
			$where[] = 'a.`core` = 0';
		}elseif($area == 'backlisting'){
			$where[] = 'a.`listing` = 1';
			$where[] = 'a.`type` != \'category\'';
		}elseif($area == 'frontcomp'){
			$where[] = 'a.`frontcomp` = 1';
		}elseif($area == 'frontform'){
			$where[] = 'a.`frontform` = 1';
			$where[] = 'a.`core` = 0';
		}elseif($area == 'frontlisting'){
			$where[] = 'a.`frontlisting` = 1';
			$where[] = 'a.`type` != \'category\'';
		}elseif($area == 'frontjoomlaprofile'){
			$where[] = 'a.`frontjoomlaprofile` = 1';
			$where[] = 'a.`type` != \'category\'';
		}elseif($area == 'frontjoomlaregistration'){
			$where[] = 'a.`frontjoomlaregistration` = 1';
			$where[] = 'a.`type` != \'category\'';
		}elseif($area == 'joomlaprofile'){
			$where[] = 'a.`joomlaprofile` = 1';
			$where[] = 'a.`type` != \'category\'';
		}elseif($area == 'fieldcat'){
			$where[] = "a.`type`='category'";
		}elseif($area == 'module'){
		}elseif($area != 'all'){
			$area = acymailing_escapeDB($area);
			$namesField = str_replace(",", $area[0].",".$area[0], $area);
			$where[] = "a.`namekey` IN (".$namesField.")";
		}

		if(!acymailing_isAdmin() && acymailing_level(3)){
			$groups = acymailing_getGroupsByUser(acymailing_currentUserId(), false);
			$condGroup = '';
			foreach($groups as $group){
				$condGroup .= ' OR a.access LIKE (\'%,'.$group.',%\')';
			}
			$filterAccess = 'AND (a.access = \'all\''.$condGroup.')';
		}else{
			$filterAccess = '';
		}

		$fields = acymailing_loadObjectList('SELECT * FROM `#__acymailing_fields` as a WHERE '.implode(' AND ', $where).' '.$filterAccess.' ORDER BY a.`ordering` ASC', 'namekey');
		foreach($fields as $namekey => $field){
			if(!empty($fields[$namekey]->options)){
				$fields[$namekey]->options = unserialize($fields[$namekey]->options);
			}else{
				$fields[$namekey]->options = array();
			}

			if(!empty($field->value)){
				$fields[$namekey]->value = $this->explodeValues($fields[$namekey]->value);
			}
			if($field->type == 'file' || $field->type == 'gravatar') $this->formoption = 'enctype="multipart/form-data"';
			if(empty($user->subid)) $user->$namekey = $field->default;
		}
		if(acymailing_level(3)){
			$allFields = acymailing_loadObjectList('SELECT * FROM `#__acymailing_fields`', 'fieldid');

			$baseElem = array();
			$elemInCat = array();
			foreach($fields as $namekey => $field){
				if($field->fieldcat == 0){
					$baseElem[] = $field;
				} // root element
				else{
					$parentId = $this->getParentCat($field, $fields, $allFields);
					$field->fieldcat = $parentId;
					if($parentId == 0){
						$baseElem[] = $field;
					} // No parent
					else{
						if(empty($elemInCat[$field->fieldcat])) $elemInCat[$field->fieldcat] = array();
						$elemInCat[$field->fieldcat][] = $field;
					}
				}
			}
			$finalField = array();
			foreach($baseElem as $oneField){
				$finalField[$oneField->namekey] = $oneField;
				if($oneField->type == 'category' && !empty($elemInCat[$oneField->fieldid])){
					$childs = $this->getChildFields($oneField->fieldid, $elemInCat);
					$finalField = $finalField + $childs;
				}
			}
			$fields = $finalField;
		}
		return $fields;
	}

	private function getParentCat($elem, $fields, $allFields){
		$parent = $allFields[$elem->fieldcat];
		if(array_key_exists($parent->namekey, $fields)){
			return $parent->fieldid;
		}else{
			if($parent->fieldcat == 0){
				return 0;
			}else return $this->getParentCat($parent, $fields, $allFields);
		}
	}

	private function getChildFields($fieldcatid, $elemInCat){
		$childs = array();
		$childElems = $elemInCat[$fieldcatid];
		foreach($childElems as $oneField){
			$childs[$oneField->namekey] = $oneField;
			if($oneField->type == 'category' && !empty($elemInCat[$oneField->fieldid])){
				$subChilds = $this->getChildFields($oneField->fieldid, $elemInCat);
				$childs = $childs + $subChilds;
			}
		}
		return $childs;
	}

	function getFieldName($field){
		$addLabels = array('textarea', 'text', 'dropdown', 'multipledropdown', 'file');
		return '<label '.(empty($this->labelClass) ? '' : ' class="'.$this->labelClass.'" ').(in_array($field->type, $addLabels) ? ' for="'.$this->prefix.$field->namekey.$this->suffix.'" ' : '').'>'.$this->trans($field->fieldname).'</label>';
	}

	function trans($name){
		if(preg_match('#^[A-Z_]*$#', $name)){
			return acymailing_translation($name);
		}
		return $name;
	}

	function listing($field, $value, $search = ''){
		$functionType = '_listing'.ucfirst($field->type);

		if(method_exists($this, $functionType)) return $this->$functionType($field, $value);

		ob_start();
		$resultTrigger = acymailing_trigger('onAcyListingField_'.$field->type, array($field, $value));
		$pluginField = ob_get_clean();

		if(!empty($pluginField)){
			return $pluginField;
		}else return acymailing_dispSearch(nl2br($this->trans($value)), $search);
	}

	function explodeValues($values){
		$allValues = explode("\n", $values);
		$returnedValues = array();
		foreach($allValues as $id => $oneVal){
			$line = explode('::', trim($oneVal));
			$var = @$line[0];
			$val = @$line[1];
			if(strlen($val) < 1) continue;

			$obj = new stdClass();
			$obj->value = $val;
			for($i = 2; $i < count($line); $i++){
				$obj->{$line[$i]} = 1;
			}
			$returnedValues[$var] = $obj;
		}
		return $returnedValues;
	}

}
