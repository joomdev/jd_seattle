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

class jflanguagesType extends acymailingClass{
	var $onclick = '';
	var $id = 'jflang';
	var $jid = 'jlang';
	var $sef = false;
	var $multilingue = false;
	var $languages;
	var $found = false;

	function __construct(){
		parent::__construct();
		$this->values = array();

		$defines = ACYMAILING_ROOT.'components'.DS.'com_joomfish'.DS.'helpers'.DS.'defines.php';
		if(file_exists($defines) && ((ACYMAILING_J16 && file_exists(ACYMAILING_ROOT.'libraries'.DS.'joomfish'.DS.'manager.php')) || (!ACYMAILING_J16 && file_exists(ACYMAILING_ROOT.'administrator'.DS.'components'.DS.'com_joomfish'.DS.'classes'.DS.'JoomfishManager.class.php')))){
			include_once($defines);
			if(!ACYMAILING_J16){
				include_once(JOOMFISH_ADMINPATH.DS.'classes'.DS.'JoomfishManager.class.php');
			}else{
				include_once(ACYMAILING_ROOT.'libraries'.DS.'joomfish'.DS.'manager.php');
			}
			$jfManager = JoomFishManager::getInstance();
			$langActive = $jfManager->getActiveLanguages();
			$this->values[] = acymailing_selectOption('', acymailing_translation('DEFAULT_LANGUAGE'));
			foreach($langActive as $oneLanguage){
				$this->values[] = acymailing_selectOption($oneLanguage->shortcode.', '.$oneLanguage->id, $oneLanguage->name);
			}
			$this->found = true;
		}

		$defines = ACYMAILING_ROOT.'components'.DS.'com_falang'.DS.'helpers'.DS.'defines.php';
		if(empty($this->values) && file_exists($defines) && include_once($defines)){
			JLoader::register('FalangManager', FALANG_ADMINPATH.'/classes/FalangManager.class.php');
			$fManager = FalangManager::getInstance();
			$langActive = $fManager->getActiveLanguages();
			$this->values[] = acymailing_selectOption('', acymailing_translation('DEFAULT_LANGUAGE'));
			foreach($langActive as $oneLanguage){
				$this->values[] = acymailing_selectOption($oneLanguage->lang_code.', '.$oneLanguage->lang_id, $oneLanguage->title);
			}
			$this->found = true;
		}

		if(ACYMAILING_J16){
			$this->languages = acymailing_getLanguages(true);
			$this->multilingue = (count($this->languages) > 1);
		}
	}

	function display($map, $value = ''){
		if(empty($this->values)) return '';
		return acymailing_select($this->values, $map, 'size="1" style="max-width:150px" '.$this->onclick, 'value', 'text', $value, $this->id);
	}

	function displayJLanguages($map, $value = ''){
		if(!ACYMAILING_J16 || !$this->multilingue) return '';

		$default = new stdClass();
		$default->name = ' - - - ';
		$default->sef = '';
		$default->language = '';

		array_unshift($this->languages, $default);

		return acymailing_select($this->languages, $map, 'size="1" style="width:150px;" '.$this->onclick, $this->sef ? 'sef' : 'language', 'name', $value, $this->jid);
	}
}
