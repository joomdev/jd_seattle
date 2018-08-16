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

class FileController extends acymailingController{
	
	function language(){
		acymailing_setVar('layout', 'language');
		return parent::display();
	}

	function save(){
		acymailing_checkToken();

		$this->_savelanguage();
		return $this->language();
	}

	function savecss(){
		if(!$this->isAllowed('configuration', 'manage')) return;
		acymailing_checkToken();

		$file = acymailing_getVar('cmd', 'file');
		if(!preg_match('#^([-a-z0-9]*)_([-_a-z0-9]*)$#i', $file, $result)){
			acymailing_display('Could not load the file '.$file.' properly');
			exit;
		}
		$type = $result[1];
		$fileName = $result[2];

		

		$path = ACYMAILING_MEDIA.'css'.DS.$type.'_'.$fileName.'.css';
		$csscontent = acymailing_getVar('string', 'csscontent');

		$alreadyExists = file_exists($path);

		if(acymailing_writeFile($path, $csscontent)){
			acymailing_enqueueMessage(acymailing_translation('JOOMEXT_SUCC_SAVED'), 'success');
			$varName = acymailing_getVar('cmd', 'var');
			if(!$alreadyExists){
				$js = "var optn = document.createElement(\"OPTION\");
						optn.text = '$fileName'; optn.value = '$fileName';
						mydrop = window.top.document.getElementById('".$varName."_choice');
						mydrop.options.add(optn);
						lastid = 0; while(mydrop.options[lastid+1]){lastid = lastid+1;} mydrop.selectedIndex = lastid;
						window.top.updateCSSLink('".$varName."','$type','$fileName');";
				acymailing_addScript(true, $js);
			}
			$config = acymailing_config();
			$newConfig = new stdClass();
			$newConfig->$varName = $fileName;
			$config->save($newConfig);
		}else{
			acymailing_enqueueMessage(acymailing_translation_sprintf('FAIL_SAVE', $path), 'error');
		}

		return $this->css();
	}

	function css(){
		acymailing_setVar('layout', 'css');
		return parent::display();
	}

	function latest(){
		return $this->language();
	}

	function send(){
		if(!$this->isAllowed('configuration', 'manage')) return;
		acymailing_checkToken();

		$bodyEmail = acymailing_getVar('string', 'mailbody');
		$code = acymailing_getVar('cmd', 'code');
		acymailing_setVar('code', $code);

		if(empty($code)) return;

		

		$config = acymailing_config();
		$mailer = acymailing_get('helper.mailer');
		$mailer->Subject = '[ACYMAILING LANGUAGE FILE] '.$code;
		$mailer->Body = 'The website '.ACYMAILING_LIVE.' using AcyMailing '.$config->get('level').' '.$config->get('version').' sent a language file : '.$code;
		$mailer->Body .= "\n"."\n"."\n".$bodyEmail;

		$extrafile = acymailing_getLanguagePath(ACYMAILING_ROOT, $code).DS.$code.'.com_acymailing_custom.ini';

		if(file_exists($extrafile)){
			$mailer->Body .= "\n"."\n"."\n".'Custom content:'."\n".file_get_contents($extrafile);
		}
		$mailer->AddAddress(acymailing_currentUserEmail(), acymailing_currentUserName());
		$mailer->AddAddress('translate@acyba.com', 'Acyba Translation Team');
		$mailer->report = false;

		$path = acymailing_cleanPath(acymailing_getLanguagePath(ACYMAILING_ROOT, $code).DS.$code.'.com_acymailing.ini');
		$mailer->AddAttachment($path);

		$result = $mailer->Send();
		if($result){
			acymailing_display(acymailing_translation('THANK_YOU_SHARING'), 'success');
			acymailing_display($mailer->reportMessage, 'success');
		}else{
			acymailing_display($mailer->reportMessage, 'error');
		}
	}

	function share(){
		if(!$this->isAllowed('configuration', 'manage')) return;
		acymailing_checkToken();

		if($this->_savelanguage()){
			acymailing_setVar('layout', 'share');
			return parent::display();
		}else{
			return $this->language();
		}
	}

	function _savelanguage(){
		if(!$this->isAllowed('configuration', 'manage')) return;
		acymailing_checkToken();
		
		$code = acymailing_getVar('cmd', 'code');
		acymailing_setVar('code', $code);
		$content = acymailing_getVar('string', 'content', '', '', ACY_ALLOWHTML);
		$content = str_replace('</textarea>', '', $content);

		if(empty($code) || empty($content)) return;

		$path = acymailing_getLanguagePath(ACYMAILING_ROOT, $code).DS.$code.'.com_acymailing.ini';
		$result = acymailing_writeFile($path, $content);
		if($result){
			acymailing_enqueueMessage(acymailing_translation('JOOMEXT_SUCC_SAVED'), 'success');
			$js = "window.top.document.getElementById('image$code').className = 'acyicon-edit'";
			acymailing_addScript(true, $js);

			$updateHelper = acymailing_get('helper.update');
			$updateHelper->installMenu($code);
		}else{
			acymailing_enqueueMessage(acymailing_translation_sprintf('FAIL_SAVE', $path), 'error');
		}

		$customcontent = acymailing_getVar('string', 'customcontent', '', '', ACY_ALLOWHTML);
		$customcontent = str_replace('</textarea>', '', $customcontent);
		$custompath = acymailing_getLanguagePath(ACYMAILING_ROOT, $code).DS.$code.'.com_acymailing_custom.ini';
		$customresult = acymailing_writeFile($custompath, $customcontent);
		if(!$customresult) acymailing_enqueueMessage(acymailing_translation_sprintf('FAIL_SAVE', $custompath), 'error');

		if($code == acymailing_getLanguageTag()) acymailing_loadLanguage();

		return $result;
	}

	function installLanguages($ajax = true){
		$messagesMethod = $ajax ? 'acymailing_display' : 'acymailing_enqueueMessage';

		$languages = acymailing_getVar('string', 'languages');
		ob_start();
		$languagesContent = acymailing_fileGetContent(ACYMAILING_UPDATEURL.'loadLanguages&json=1&codes='.$languages);
		$warnings = ob_get_clean();
		if(!empty($warnings) && acymailing_isDebug()) echo $warnings;

		if(empty($languagesContent)){
			$messagesMethod('Could not load the language files from our server, you can update them in the AcyMailing configuration page, tab "Languages" or start your own translation and share it', 'error');
			if($ajax) exit;
			else return;
		}

		$decodedLanguages = json_decode($languagesContent, true);

		$updateHelper = acymailing_get('helper.update');
		$success = array();
		$error = array();

		foreach($decodedLanguages as $code => $content){
			if(empty($content)){
				$error[] = 'The language '.$code.' was not found on our server, you can start your own translation in the AcyMailing configuration page, tab "Languages" then share it';
				continue;
			}

			if(acymailing_writeFile(acymailing_getLanguagePath(ACYMAILING_ROOT, $code).DS.$code.'.com_acymailing.ini', $content)){
				$updateHelper->installMenu($code);
				$success[] = 'Successfully installed language: '.$code;
			}else{
				$error[] = acymailing_translation_sprintf('FAIL_SAVE', $code.'.com_acymailing.ini');
			}
		}

		if(!empty($success)) $messagesMethod($success, 'success');
		if(!empty($error)) $messagesMethod($error, 'error');
		if($ajax) exit;
	}

	function select(){
		acymailing_setVar('layout', 'select');
		return parent::display();
	}

	function downloadAcySMS(){
		$headers = get_headers('https://www.acyba.com/download-area/download/component-acysms/level-express.html',1);
		$package = acymailing_fileGetContent('https://www.acyba.com/download-area/download/component-acysms/level-express.html');
		if(empty($headers['Content-Disposition']) || empty($package)) exit;

		$fileName = strpos($headers['Content-Disposition'], '.zip') === false ? 'com_acysms.tar.gz' : 'com_acysms.zip';
		if(acymailing_writeFile(ACYMAILING_ROOT.'tmp'.DS.'acysms'.DS.$fileName, $package) && acymailing_extractArchive(ACYMAILING_ROOT.'tmp'.DS.'acysms'.DS.$fileName, ACYMAILING_ROOT.'tmp'.DS.'acysms')) echo 'success';

		exit;
	}

	function installPackage(){
		if(!ACYMAILING_J16) include_once(ACYMAILING_ROOT.'libraries'.DS.'joomla'.DS.'installer'.DS.'installer.php');
		
		$installer = JInstaller::getInstance();

		if($installer->install(ACYMAILING_ROOT.'tmp'.DS.'acysms')){
			acymailing_deleteFolder(ACYMAILING_ROOT.'tmp'.DS.'acysms');
			echo 'success';
		}

		exit;
	}
}
