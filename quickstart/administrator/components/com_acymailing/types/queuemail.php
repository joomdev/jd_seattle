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

class queuemailType extends acymailingClass{
	function __construct(){
		parent::__construct();
		$allmails = acymailing_loadObjectList('SELECT COUNT(*) as total, mailid FROM #__acymailing_queue GROUP BY mailid', 'mailid');

		$subjects = array();
		if(!empty($allmails)){
			$subjects = acymailing_loadObjectList('SELECT mailid,subject FROM #__acymailing_mail WHERE mailid IN ('.implode(',',array_keys($allmails)).') ORDER BY subject ASC', 'mailid');
		}

		$this->values = array();
		$this->values[] = acymailing_selectOption('0', acymailing_translation('ALL_EMAILS'));
		foreach($subjects as $mailid => $oneMail){
			$this->values[] = acymailing_selectOption($mailid, $oneMail->subject.' ( '.$allmails[$mailid]->total.' )' );
		}
	}

	function display($map,$value){
		return acymailing_select(  $this->values, $map, 'class="inputbox" style="max-width:600px;width:auto;" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int) $value );
	}
}
