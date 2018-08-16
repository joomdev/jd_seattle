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

class statusfilterType extends acymailingClass{
	function __construct(){
		parent::__construct();
		$this->values = array();
		$this->values[] = acymailing_selectOption('0', acymailing_translation('ALL_STATUS'));
		$this->values[] = acymailing_selectOption( '<OPTGROUP>', acymailing_translation( 'ACCEPT_REFUSE' ));
		$this->values[] = acymailing_selectOption('1', acymailing_translation('ACCEPT_EMAIL'));
		$this->values[] = acymailing_selectOption('-1', acymailing_translation('REFUSE_EMAIL'));
		$this->values[] = acymailing_selectOption( '</OPTGROUP>');
		$config = acymailing_config();
		if($config->get('require_confirmation',0)){
			$this->values[] = acymailing_selectOption( '<OPTGROUP>', acymailing_translation( 'SUBSCRIPTION' ));
			$this->values[] = acymailing_selectOption('2', acymailing_translation('PENDING_SUBSCRIPTION'));
			$this->values[] = acymailing_selectOption( '</OPTGROUP>');
		}
		$this->values[] = acymailing_selectOption( '<OPTGROUP>', acymailing_translation( 'ENABLED_DISABLED' ));
		$this->values[] = acymailing_selectOption('3', acymailing_translation('ENABLED'));
		$this->values[] = acymailing_selectOption('-3', acymailing_translation('DISABLED'));
		$this->values[] = acymailing_selectOption( '</OPTGROUP>');
	}

	function display($map,$value){
		return acymailing_select(  $this->values, $map, 'size="1" onchange="document.adminForm.limitstart.value=0;document.adminForm.submit( );"', 'value', 'text', (int) $value );
	}
}
