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

class contentfilterType extends acymailingClass{
	var $onclick = 'updateTag();';
	function __construct(){
		parent::__construct();
	}

	function display($map,$value,$label = true,$modified = true){
		$prefix = $label ? '|filter:' : '';
		$this->values = array();
		$this->values[] = acymailing_selectOption("", acymailing_translation('ACY_ALL'));
		$this->values[] = acymailing_selectOption($prefix."created", acymailing_translation('ONLY_NEW_CREATED'));
		if($modified) $this->values[] = acymailing_selectOption($prefix."modify", acymailing_translation('ONLY_NEW_MODIFIED'));
		return acymailing_select($this->values, $map , 'size="1" onchange="'.$this->onclick.'" style="max-width:200px;"', 'value', 'text', (string) $value);
	}
}
