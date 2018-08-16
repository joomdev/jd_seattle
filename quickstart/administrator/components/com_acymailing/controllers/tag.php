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

class TagController extends acymailingController
{
	var $aclCat = 'tags';

	function __construct($config = array()){
		parent::__construct($config);
		acymailing_setNoTemplate();

		$this->registerDefaultTask('tag');
	}

	function tag(){
		if(!$this->isAllowed($this->aclCat,'view')) return;
		acymailing_setVar( 'layout', 'tag'  );
		return parent::display();
	}

	function plgtrigger(){
		if(!require_once(ACYMAILING_BACK.DS.'controllers'.DS.'cpanel.php')) return;
		$cPanelController = acymailing_get('controller.cpanel');
		$cPanelController->plgtrigger();
		return;
	}

	function customtemplate(){
		acymailing_setVar('layout', 'form');
		return parent::display();
	}

	function store(){
		acymailing_checkToken();

		$plugin = acymailing_getVar('string', 'plugin');
		$plugin = preg_replace('#[^a-zA-Z0-9]#Uis', '', $plugin);
		$body = acymailing_getVar('string', 'templatebody', '', '', ACY_ALLOWRAW);

		if(empty($body)){ acymailing_enqueueMessage(acymailing_translation('FILL_ALL'),'error'); return; }

		$pluginsFolder = ACYMAILING_MEDIA.'plugins';
		if(!file_exists($pluginsFolder)) acymailing_createDir($pluginsFolder);

		try{
			
			$status = acymailing_writeFile($pluginsFolder.DS.$plugin.'.php',$body);
		}catch(Exception $e){
			$status = false;
		}

		if($status) acymailing_enqueueMessage(acymailing_translation('JOOMEXT_SUCC_SAVED'),'success');
		else acymailing_enqueueMessage(acymailing_translation_sprintf('FAIL_SAVE', $pluginsFolder.DS.$plugin.'.php'),'error');
	}
}
