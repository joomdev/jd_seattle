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

class BouncesController extends acymailingController{
	var $pkey = 'ruleid';
	var $table = 'rules';
	var $groupMap = '';
	var $groupVal = '';

	function listing(){
		if(!acymailing_level(3)){
			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->setTitle(acymailing_translation('BOUNCE_HANDLING'), 'bounces');
			$acyToolbar->help('bounce');
			$acyToolbar->display();
			$config = acymailing_config();
			$level = $config->get('level');
			$url = ACYMAILING_HELPURL.'bounce-paidversion&utm_source=acymailing-'.$level.'&utm_medium=back-end&utm_content=bounces-display&utm_campaign=upgrade';
			$iFrame = "<iframe class='paidversion' frameborder='0' src='$url' width='100%' height='100%' scrolling='auto'></iframe>";
			echo $iFrame.'<div id="iframedoc"></div>';
			return;
		}

		return parent::listing();
	}

}
