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

class ArchiveController extends acymailingController{

	function view(){

		$statsClass = acymailing_get('class.stats');
		$statsClass->countReturn = false;
		$statsClass->saveStats();

		$printEnabled = acymailing_getVar('none', 'print', 0);
		if($printEnabled){
			$js = "setTimeout(function(){
					if(document.getElementById('iframepreview')){
						document.getElementById('iframepreview').contentWindow.focus();
						document.getElementById('iframepreview').contentWindow.print();
					}else{
						window.print();
					}
				},2000);";
			acymailing_addScript(true, $js);
		}

		acymailing_setVar('layout', 'view');
		return parent::display();
	}


}
