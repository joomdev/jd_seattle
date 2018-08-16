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

class statusType extends acymailingClass{
	function __construct(){
		parent::__construct();
		$this->values = array();
		$this->values[] = acymailing_selectOption('-1', acymailing_translation('UNSUBSCRIBED'));
		$this->values[] = acymailing_selectOption('0', acymailing_translation('NO_SUBSCRIPTION'));
		$this->values[] = acymailing_selectOption('2', acymailing_translation('PENDING_SUBSCRIPTION'));
		$this->values[] = acymailing_selectOption('1', acymailing_translation('SUBSCRIBED'));
	}

	function display($map,$value){
		static $i = 0;
		return acymailing_radio($this->values, $map , 'class="radiobox" size="1"', 'value', 'text', (int) $value,'status'.$i++);
	}

}
