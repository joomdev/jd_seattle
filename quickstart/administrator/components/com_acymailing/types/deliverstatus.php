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

class deliverstatusType extends acymailingClass{

	function __construct(){
		parent::__construct();

		$this->values = array();
		$this->values[] = acymailing_selectOption('0', acymailing_translation('ALL_STATUS'));
		$this->values[] = acymailing_selectOption('open', acymailing_translation('OPEN'));
		$this->values[] = acymailing_selectOption('notopen', acymailing_translation('NOT_OPEN'));
		$this->values[] = acymailing_selectOption('failed', acymailing_translation('FAILED'));
		if(acymailing_level(3)) $this->values[] = acymailing_selectOption('bounce', acymailing_translation('BOUNCES'));

	}

	function display($map,$value){
		return acymailing_select(  $this->values, $map, 'class="inputbox" size="1" style="width:150px;" onchange="document.adminForm.submit( );"', 'value', 'text', $value );
	}
}
