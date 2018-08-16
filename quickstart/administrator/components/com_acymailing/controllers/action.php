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

class ActionController extends acymailingController{

	var $pkey = 'action_id';
	var $table = 'action';
	var $aclCat = 'distribution';

	function listing(){
		$actionColumns = acymailing_getColumns('#__acymailing_action');
		if(empty($actionColumns['senderfrom'])){
			acymailing_query("ALTER TABLE #__acymailing_action ADD `senderfrom` tinyint NOT NULL DEFAULT 0");
		}
		if(empty($actionColumns['senderto'])){
			acymailing_query("ALTER TABLE #__acymailing_action ADD `senderto` tinyint NOT NULL DEFAULT 0");
		}
		if(empty($actionColumns['delete_wrong_emails'])){
			acymailing_query("ALTER TABLE #__acymailing_action ADD `delete_wrong_emails` tinyint NOT NULL DEFAULT 0");
		}

		if(!acymailing_level(3)){
			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->setTitle(acymailing_translation('ACY_DISTRIBUTION'), 'action');
			$acyToolbar->help('distributionlists#listing');
			$acyToolbar->display();
			$config = acymailing_config();
			$level = $config->get('level');
			$url = ACYMAILING_HELPURL.'paidversion&utm_source=acymailing-'.$level.'&utm_medium=back-end&utm_content=distributionlist-display&utm_campaign=upgrade';
			$iFrame = "<iframe class='paidversion' frameborder='0' src='$url' width='100%' height='100%' scrolling='auto'></iframe>";
			echo $iFrame.'<div id="iframedoc"></div>';
			return;
		}

		return parent::listing();
	}

}
