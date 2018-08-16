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

class plgEditorAcyEditor extends JPlugin
{

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);

		include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php');

		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'acyeditor');
			$this->params = new acyParameter( $plugin->params );
		}
	}


	public function onInit()
	{
		acymailing_addScript(false, ACYMAILING_JS.'acyeditor.js?v='.@filemtime(ACYMAILING_MEDIA.'js'.DS.'acyeditor.js'));

		$websiteurl = rtrim(acymailing_rootURI(),'/').'/';

		acymailing_addStyle(false, $websiteurl.'plugins/editors/acyeditor/acyeditor/css/acyeditor.css?v='.@filemtime(JPATH_SITE.DS.'plugins'.DS.'editors'.DS.'acyeditor'.DS.'acyeditor'.DS.'css'.DS.'acyeditor.css'));

		if (ACYMAILING_J16){
			acymailing_addScript(false, $websiteurl.'plugins/editors/acyeditor/acyeditor/ckeditor/ckeditor.js?v='.@filemtime(ACYMAILING_ROOT.'plugins'.DS.'editors'.DS.'acyeditor'.DS.'acyeditor'.DS.'ckeditor'.DS.'ckeditor.js'));
		} else{
			acymailing_addScript(false, $websiteurl.'plugins/editors/acyeditor/ckeditor/ckeditor.js?v='.@filemtime(ACYMAILING_ROOT.'plugins'.DS.'editors'.DS.'acyeditor'.DS.'ckeditor'.DS.'ckeditor.js'));
		}
		acymailing_addScript(false, $websiteurl.'media/com_acymailing/js/jquery/jquery-1.9.1.min.js?v='.@filemtime(ACYMAILING_ROOT.'media'.DS.'com_acymailing'.DS.'js'.DS.'jquery'.DS.'jquery-1.9.1.min.js'));
		acymailing_addStyle(false, $websiteurl.'media/com_acymailing/js/colorpicker/css/colorpicker.css?v='.@filemtime(ACYMAILING_ROOT.'media'.DS.'com_acymailing'.DS.'js'.DS.'colorpicker'.DS.'css'.DS.'colorpicker.css'));
		acymailing_addScript(false, $websiteurl.'media/com_acymailing/js/colorpicker/js/colorpicker.js?v='.@filemtime(ACYMAILING_ROOT.'media'.DS.'com_acymailing'.DS.'js'.DS.'colorpicker'.DS.'js'.DS.'colorpicker.js'));
		acymailing_addScript(false, $websiteurl.'media/com_acymailing/js/jquery/jquery-ui.min.js?v='.@filemtime(ACYMAILING_ROOT.'media'.DS.'com_acymailing'.DS.'js'.DS.'jquery'.DS.'jquery-ui.min.js'));
		return '';
	}

	function onSave()
	{
		return;
	}

	function onGetContent($id)
	{
		return "AcyGetData();\n";
	}

	function onSetContent($id, $html)
	{
		$idIframe = "#".$id."_ifr";
		$initialisation = $this->GetInitialisationFunction($id);

		return "document.getElementById('$id').value = $html;$initialisation";
	}

	function onGetInsertMethod($id)
	{
		static $done = false;

		if($done) return true;
		$done = true;

		$js = "\tfunction jInsertEditorText(text, editor) {
				insertAtCursor(document.getElementById(editor), text);
				}";
		acymailing_addScript(true, $js);

		return true;
	}

	function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		if (empty($id)) {
			$id = $name;
		}

		if (is_numeric($width)) {
			$width .= 'px';
		}

		if (is_numeric($height)) {
			$height .= 'px';
		}

		$idIframe = $id."_ifr";
		$initialisation = $this->GetInitialisationFunction($id);

		$contentAvecOnClick = htmlspecialchars_decode($content);
		$editor  = "<textarea name=\"$name\" id=\"$id\" cols=\"$col\" rows=\"$row\" style=\"width:$width; height:$height;display:none\">$content</textarea>\n
					<script type=\"text/javascript\">
						$initialisation
					</script>";

		return $editor;
	}

	function GetInitialisationFunction($id)
	{

		$texteSuppression = acymailing_translation('ACYEDITOR_DELETEAREA');
		$tooltipSuppression = acymailing_translation('ACY_DELETE');
		$tooltipEdition = acymailing_translation('ACY_EDIT');
		$urlBase = acymailing_rootURI();
		$urlAdminBase = acymailing_baseURI();
		$cssurl = acymailing_getVar('none', 'acycssfile');
		$forceComplet = (acymailing_getVar('cmd', 'option') != 'com_acymailing' || acymailing_getVar('cmd', 'ctrl') == 'template' || acymailing_getVar('cmd', 'ctrl') == 'list');
		$modeList = (acymailing_getVar('cmd', 'option') == 'com_acymailing' && acymailing_getVar('cmd', 'ctrl') == 'list');
		$modeTemplate = (acymailing_getVar('cmd', 'option') == 'com_acymailing' && acymailing_getVar('cmd', 'ctrl') == 'template');
		$modeArticle = (acymailing_getVar('cmd', 'option') == 'com_content' && acymailing_getVar('cmd', 'view') == 'article');
		$joomla2_5 = ACYMAILING_J16;
		$joomla3 = ACYMAILING_J30;
		$titleTemplateDelete = acymailing_translation('ACYEDITOR_TEMPLATEDELETE');
		$titleTemplateText = acymailing_translation('ACYEDITOR_TEMPLATETEXT');
		$titleTemplatePicture = acymailing_translation('ACYEDITOR_TEMPLATEPICTURE');
		$titleShowAreas = acymailing_translation('ACYEDITOR_SHOWAREAS');
		$isBack = 0;
		if(acymailing_isAdmin()){
			$isBack = 1;
		};
		$tagAllowed = 0;
		$config = acymailing_config();
		if(acymailing_getVar('cmd', 'option') == 'com_acymailing'
		&& acymailing_getVar('cmd', 'ctrl') != 'list'
		&& acymailing_getVar('cmd', 'ctrl') != 'campaign'
		&& acymailing_isAllowed($config->get('acl_tags_view','all'))
		&& acymailing_getVar('cmd', 'tmpl') != 'component'){
			$tagAllowed = 1;
		}
		$type = 'news';
		if(acymailing_getVar('cmd', 'ctrl') == 'autonews' || acymailing_getVar('cmd', 'ctrl') == 'followup'){
			$type = acymailing_getVar('cmd', 'ctrl');
		}

		$pasteType = $this->params->get('pasteType', 'plain');
		$enterMode = $this->params->get('enterMode', 'br');
		$inlineSource = $this->params->get('inlineSource', 1);

		$js = "
		acyEnterMode='".$enterMode."';
		pasteType='".$pasteType."';
		urlSite='".$urlBase."';
		defaultText='".str_replace("'", "\'", acymailing_translation('ACYEDITOR_DEFAULTTEXT'))."';
		titleBtnMore='".str_replace("'", "\'", acymailing_translation('ACYEDITOR_TEMPLATEMORE'))."';
		titleBtnDupliAfter='".str_replace("'", "\'", acymailing_translation('ACYEDITOR_DUPLICATE_AFTER'))."';
		tooltipInitAreas='".str_replace("'", "\'", acymailing_translation('ACYEDITOR_REINIT_ZONE_TOOLTIP'))."';
		confirmInitAreas='".str_replace("'", "\'", acymailing_translation('ACYEDITOR_REINIT_ZONE_CONFIRMATION'))."';
		tooltipTemplateSortable='".str_replace("'", "\'", acymailing_translation('ACYEDITOR_SORTABLE_AREA_TOOLTIP'))."';
		var bgroundColorTxt='".str_replace("'", "\'", acymailing_translation('BACKGROUND_COLOUR'))."';
		var confirmDeleteBtnTxt='".str_replace("'", "\'", acymailing_translation('ACY_DELETE'))."';
		var confirmCancelBtnTxt='".str_replace("'", "\'", acymailing_translation('ACY_CANCEL'))."';
		inlineSource='".$inlineSource."';
		var emojis = false;
		";

		$installedPlugin = JPluginHelper::getPlugin('acymailing', 'emojis');
		if(!empty($installedPlugin)) {
			$params = new acyParameter($installedPlugin->params);
			if(JPluginHelper::isEnabled('acymailing', 'emojis') && $params->get('editor', 1) == 1) {
				$js .= "emojis = true;";
			}
		}

		acymailing_addScript(true, $js);

		$ckEditorFileVersion = @filemtime(ACYMAILING_ROOT.'plugins'.DS.'editors'.DS.'acyeditor'.DS.'acyeditor'.DS.'ckeditor'.DS.'ckeditor.js');
		return "Initialisation(\"$id\", \"$type\", \"$urlBase\", \"$urlAdminBase\", \"$cssurl\", \"$forceComplet\", \"$modeList\", \"$modeTemplate\", \"$modeArticle\", \"$joomla2_5\", \"$joomla3\", \"$isBack\", \"$tagAllowed\", \"$texteSuppression\", \"$tooltipSuppression\", \"$tooltipEdition\", \"$titleTemplateDelete\", \"$titleTemplateText\", \"$titleTemplatePicture\", \"$titleShowAreas\", \"$ckEditorFileVersion\");\n";
	}
}

