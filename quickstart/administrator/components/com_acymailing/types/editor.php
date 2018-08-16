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

class editorType extends acymailingClass{

	function __construct(){
		parent::__construct();
		if(!ACYMAILING_J16){
			$query = 'SELECT DISTINCT element,name FROM '.acymailing_table('plugins',false).' WHERE folder=\'editors\' AND published=1 ORDER BY ordering ASC, name ASC';
 		}else{
			$query = 'SELECT element,name FROM '.acymailing_table('extensions',false).' WHERE folder=\'editors\' AND enabled=1 AND type=\'plugin\' ORDER BY ordering ASC, name ASC';
		}

		$joomEditors = acymailing_loadObjectList($query);

		$this->values = array();
		$this->values[] = acymailing_selectOption('0', acymailing_translation('ACY_DEFAULT'));
		if(!empty($joomEditors)){
			foreach($joomEditors as $myEditor){
				$this->values[] = acymailing_selectOption($myEditor->element, $myEditor->name);
			}
		}
	}

	function display($map,$value){
		return acymailing_select($this->values, $map , 'size="1"', 'value', 'text', $value);
	}

}
