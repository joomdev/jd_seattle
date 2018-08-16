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


class FileViewFile extends acymailingView{
	
	function display($tpl = null){
		acymailing_addStyle(false, ACYMAILING_CSS.'frontendedition.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'frontendedition.css'));

		acymailing_setNoTemplate();

		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function css(){
		$file = acymailing_getVar('cmd', 'file');
		if(!preg_match('#^([-A-Z0-9]*)_([-_A-Z0-9]*)$#i', $file, $result)){
			acymailing_display('Could not load the file '.$file.' properly');
			exit;
		}
		$type = $result[1];
		$fileName = $result[2];

		$content = acymailing_getVar('string', 'csscontent');
		if(empty($content) && file_exists(ACYMAILING_MEDIA.'css'.DS.$type.'_'.$fileName.'.css')) $content = file_get_contents(ACYMAILING_MEDIA.'css'.DS.$type.'_'.$fileName.'.css');

		if(strpos($fileName, 'default') !== false){
			$fileName = 'custom'.str_replace('default', '', $fileName);
			$i = 1;
			while(file_exists(ACYMAILING_MEDIA.'css'.DS.$type.'_'.$fileName.'.css')){
				$fileName = 'custom'.$i;
				$i++;
			}
		}

		if(acymailing_isNoTemplate()){
			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->custom('savecss', acymailing_translation('ACY_SAVE'), 'save', false);
			$acyToolbar->setTitle($type.'_'.$fileName.'.css');
			$acyToolbar->topfixed = false;
			$acyToolbar->display();
		}

		$this->content = $content;
		$this->fileName = $fileName;
		$this->type = $type;
	}


	function language(){

		$this->setLayout('default');

		$code = acymailing_getVar('cmd', 'code');
		if(empty($code)){
			acymailing_display('Code not specified', 'error');
			return;
		}

		$file = new stdClass();
		$file->name = $code;
		$path = acymailing_getLanguagePath(ACYMAILING_ROOT, $code).DS.$code.'.com_acymailing.ini';
		$file->path = $path;

		
		$showLatest = true;
		$loadLatest = false;

		if(file_exists($path)){
			$file->content = acymailing_fileGetContent($path);
			if(empty($file->content)){
				acymailing_display('File not found : '.$path, 'error');
			}
		}else{
			$loadLatest = true;
			acymailing_enqueueMessage(acymailing_translation('LOAD_ENGLISH_1').'<br />'.acymailing_translation('LOAD_ENGLISH_2').'<br />'.acymailing_translation('LOAD_ENGLISH_3'), 'info');
			$file->content = acymailing_fileGetContent(acymailing_getLanguagePath(ACYMAILING_ROOT, ACYMAILING_DEFAULT_LANGUAGE).DS.ACYMAILING_DEFAULT_LANGUAGE.'.com_acymailing.ini');
		}

		$custompath = acymailing_getLanguagePath(ACYMAILING_ROOT, $code).DS.$code.'.com_acymailing_custom.ini';
		if(file_exists($custompath)){
			$file->customcontent = acymailing_fileGetContent($custompath);
		}

		if($loadLatest || acymailing_getVar('cmd', 'task') == 'latest'){
			if(file_exists(acymailing_getLanguagePath(ACYMAILING_ROOT, $code))){
				acymailing_addScript(false, ACYMAILING_UPDATEURL.'languageload&code='.acymailing_getVar('cmd', 'code'));
			}else{
				acymailing_enqueueMessage('The specified language "'.htmlspecialchars($code, ENT_COMPAT, 'UTF-8').'" is not installed on your site', 'warning');
			}
			$showLatest = false;
		}elseif(acymailing_getVar('cmd', 'task') == 'save'){
			$showLatest = false;
		}

		if(acymailing_isNoTemplate()){
			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->save();
			$acyToolbar->custom('share', acymailing_translation('SHARE'), 'share', false);
			$acyToolbar->setTitle(acymailing_translation('ACY_FILE').' : '.$this->escape($file->name));
			$acyToolbar->topfixed = false;
			$acyToolbar->display();
		}

		$this->showLatest = $showLatest;
		$this->file = $file;
	}

	function share(){
		$file = new stdClass();
		$file->name = acymailing_getVar('cmd', 'code');

		$acyToolbar = acymailing_get('helper.toolbar');
		$acyToolbar->custom('share', acymailing_translation('SHARE'), 'share', false, "if(confirm('".acymailing_translation('CONFIRM_SHARE_TRANS', true)."')){ acymailing.submitbutton('send');} return false;");
		$acyToolbar->setTitle(acymailing_translation('SHARE').' : '.$this->escape($file->name));
		$acyToolbar->topfixed = false;
		$acyToolbar->display();

		$this->file = $file;
	}

	function select(){
		$config = acymailing_config();
		$uploadFolders = acymailing_getFilesFolder('upload', true);
		$uploadFolder = acymailing_getVar('string', 'currentFolder', $uploadFolders[0]);
		$uploadPath = acymailing_cleanPath(ACYMAILING_ROOT.trim(str_replace('/', DS, trim($uploadFolder)), DS));
		$map = acymailing_getVar('string', 'id');

		$uploadedFile = acymailing_getVar('array', 'uploadedFile', array(), 'files');
		if(!empty($uploadedFile) && !empty($uploadedFile['name'])){
			$uploaded = acymailing_importFile($uploadedFile, $uploadPath, in_array($map, array('thumb', 'readmore')));
			if($uploaded){
				$script = 'parent.document.getElementById("'.$map.'").value = "'.str_replace(DS, '/', $uploadFolder).'/'.$uploaded.'";';
				if(in_array($map, array('thumb', 'readmore'))){
					$script .= 'parent.document.getElementById("'.$map.'preview").src = "'.acymailing_rootURI().str_replace(DS, '/', $uploadFolder).'/'.$uploaded.'";';
				}else{
					$script .= 'parent.document.getElementById("'.$map.'selection").innerHTML = "'.$uploaded.'";';
					$script .= "parent.document.getElementById('".$map."suppr').style.display = 'inline';";
				}
				$script .= 'window.parent.acymailing.closeBox();';
				acymailing_addScript(true, $script);
			}
		}

		$fileToDelete = acymailing_getVar('string', 'filename', '');
		if(!empty($fileToDelete) && file_exists($uploadPath.DS.$fileToDelete) && empty($uploadedFile)){
			$checkAttach = acymailing_loadResultArray('SELECT mailid FROM #__acymailing_mail WHERE attach LIKE \'%"'.$uploadFolder.'/'.$fileToDelete.'"%\'');

			if(!empty($checkAttach)){
				acymailing_display(acymailing_translation_sprintf('ACY_CANT_DELETEFILE', implode($checkAttach, ', ')), 'error');
			}else{
				if(acymailing_deleteFile($uploadPath.DS.$fileToDelete)){
					acymailing_display(acymailing_translation('ACY_DELETED_FILE_SUCCESS'), 'success');
				}else{
					acymailing_display(acymailing_translation('ACY_DELETED_FILE_ERROR'), 'error');
				}
			}
		}

		$displayType = acymailing_getVar('string', 'displayType', 'icons');
		$this->config = $config;
		$this->uploadFolder = $uploadFolder;
		$this->uploadFolders = $uploadFolders;
		$this->uploadPath = $uploadPath;
		$this->map = $map;
		$this->displayType = $displayType;
	}
}
