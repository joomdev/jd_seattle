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

class acytoolbarHelper{
	var $buttons = array();
	var $buttonOptions = array();
	var $title = '';
	var $titleLink = '';

	var $topfixed = true;

	var $htmlclass = '';

	function setTitle($name, $link = ''){
		$this->title = $name;
		$this->titleLink = $link;
		acymailing_setPageTitle($name);
	}

	function custom($task, $text, $class, $listSelect = true, $onClick = '', $title = ''){

		$submit = "acymailing.submitbutton('".$task."')";
		$js = !empty($listSelect) ? "if(document.adminForm.boxchecked.value==0){alert('".str_replace(array("'", '"'), array("\'", '\"'), acymailing_translation('ACY_SELECT_ELEMENT'))."');return false;}else{".$submit."}" : $submit;

		$onClick = !empty($onClick) ? $onClick : $js;
		if(empty($title)) $title = $text;

		$button = '<button id="toolbar-'.$class.'" onclick="'.$onClick.'" class="acytoolbar_'.$class.'" title="'.$title.'"><i class="acyicon-'.$class.'"></i><span>'.$text.'</span></button>';
		if(empty($this->buttonOptions)){
			$this->buttons[] = $button;
			return;
		}

		$dropdownOptions = '<ul class="buttonOptions" style="margin: 0px; text-align: left;">';
		foreach($this->buttonOptions as $oneOption){
			$dropdownOptions .= '<li>'.$oneOption.'</li>';
		}
		$dropdownOptions .= '</ul>';

		$buttonArea = $button;


		$this->buttons[] = '<div style="display:inline;" class="subbuttonactions">'.$buttonArea.'<span class="acytoolbar_hover acybuttongroup_'.$class.'"><span style="vertical-align: top; display:inline-block; padding-top:10px;" class="acyicon-down"></span><span class="acytoolbar_hover_display">'.$dropdownOptions.'</span></span></div>';

		$this->buttonOptions = array();
	}

	function display(){
		acymailing_addScript(false, ACYMAILING_JS.'acytoolbar.js?v='.filemtime(ACYMAILING_MEDIA.'js'.DS.'acytoolbar.js'));
		acymailing_addStyle(true, '#system-message-container, #system-message{display:none;}');
		
		$classCtrl = acymailing_getVar('cmd', 'ctrl', '');
		echo '<div id="acymenu_top" class="acytoolbarmenu donotprint '.(empty($this->topfixed) ? '' : 'acyaffix-top ').(!empty($classCtrl) ? 'acytopmenu_'.$classCtrl.' ' : '').$this->htmlclass.'" >';
		echo '<table cellspacing="0" border="0" cellpadding="0" style="width: 100%;height: 40px;">
				<colgroup>
					<col width="100%" />
					<col width="0%" />
				</colgroup>
				<tr><td class="acytoolbartitle">';
		if(!empty($this->title)){
			$title = htmlspecialchars($this->title, ENT_COMPAT, 'UTF-8');
			if(!empty($this->titleLink)) $title = '<a style="color:white;" href="'.acymailing_completeLink($this->titleLink).'">'.$title.'</a>';
			echo $title;
		}
		echo '</td><td style="white-space: nowrap;" class="acytoolbarmenu_menu">';
		echo implode(' ', $this->buttons);
		echo '</td></tr></table></div>';

		acymailing_displayMessages();  
		if(!empty($this->topfixed)) acymailing_navigationTabs();
	}

	function add(){
		$this->custom('add', acymailing_translation('ACY_NEW'), 'new', false);
	}

	function edit(){
		$this->custom('edit', acymailing_translation('ACY_EDIT'), 'edit', true);
	}

	function delete(){
		$onClick = 'if(document.adminForm.boxchecked.value==0){
						alert(\''.str_replace("'", "\\'", acymailing_translation('ACY_SELECT_ELEMENT')).'\');
					}else{
						if(confirm(\''.str_replace("'", "\\'", acymailing_translation('ACY_VALIDDELETEITEMS', true)).'\')){
							acymailing.submitbutton(\'remove\');
						}
					}';
		$this->custom('remove', acymailing_translation('ACY_DELETE'), 'delete', true, $onClick);
	}

	function copy(){
		$this->custom('copy', acymailing_translation('ACY_COPY'), 'copy', true);
	}

	function link($link, $text, $class){
		$onClick = "location.href='".$link."';return false;";
		$this->custom('link', $text, $class, false, $onClick);
	}

	function help($helpname, $anchor = ''){
		$config = acymailing_config();
		$level = $config->get('level');

		$url = ACYMAILING_HELPURL.$helpname.'&level='.$level.(!empty($anchor) ? '#'.$anchor : '');
		$iFrame = "'<iframe frameborder=\"0\" src=\'$url\' width=\'100%\' height=\'100%\' scrolling=\'auto\'></iframe>'";

		$js = "var openHelp = true;
				function displayDoc(){
					var box=document.getElementById('iframedoc');
					if(openHelp){
						box.innerHTML = ".$iFrame.";
						box.className = 'slide_open';
					}else{
						box.className = 'slide_close';
					}
					openHelp = !openHelp;
				}";
		acymailing_addScript(true, $js);

		$onClick = 'displayDoc();return false;';

		$this->custom('help', acymailing_translation('ACY_HELP'), 'help', false, $onClick);
	}

	function divider(){
		$this->buttons[] = '<span class="acytoolbar_divider"></span>';
	}

	function cancel(){
		$this->custom('cancel', acymailing_translation('ACY_CANCEL'), 'cancel', false);
	}

	function save(){
		$this->custom('save', acymailing_translation('ACY_SAVE'), 'save', false);
	}

	function apply(){
		$this->custom('apply', acymailing_translation('ACY_APPLY'), 'apply', false);
	}

	function popup($name = '', $text = '', $url = '', $width = 0, $height = 480){
		$this->buttons[] = $this->_popup($name, $text, $url, $width, $height);
	}

	function directPrint(){
		$this->buttons[] = $this->_directPrint();
	}

	private function _popup($name = '', $text = '', $url = '', $width = 0, $height = 480){
		$ids = '';
		if(in_array($name, array('ABtesting', 'action'))){
			$js = "
			function getAcyPopupUrl(){
				i = 0;
				ids = '';
				while(window.document.getElementById('cb'+i)){
					if(window.document.getElementById('cb'+i).checked) ids += window.document.getElementById('cb'+i).value+',';
					i++;
				}
				return ids.slice(0,-1);
			}";
			acymailing_addScript(true, $js);

			if($name == 'ABtesting'){
				$ids = '&mailid=';
			}elseif($name == 'action'){
				$ids = '&subid=';
			}

			$ids .= "'+getAcyPopupUrl()+'";
		}

		return acymailing_popup($url.$ids, '<button id="toolbar-'.$name.'" class="acytoolbar_'.$name.'" title="'.$text.'"><i class="acyicon-'.$name.'"></i><span>'.$text.'</span></button>', '', $width, $height, 'a_'.$name);
	}

	private function _directPrint(){

		acymailing_addStyle(false, ACYMAILING_CSS.'acyprint.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'acyprint.css'), 'text/css', 'print');

		$function = "if(document.getElementById('iframepreview')){document.getElementById('iframepreview').contentWindow.focus();document.getElementById('iframepreview').contentWindow.print();}else{window.print();}return false;";

		return '<button class="acytoolbar_print" onclick="'.$function.'" title="'.acymailing_translation('ACY_PRINT', true).'"><i class="acyicon-print"></i><span>'.acymailing_translation('ACY_PRINT', true).'</span></button>';
	}

	function addButtonOption($task, $text, $class, $listSelect, $onClick = ''){

		$submit = "acymailing.submitbutton('".$task."')";
		$js = !empty($listSelect) ? "if(document.adminForm.boxchecked.value==0){alert('".str_replace(array("'", '"'), array("\'", '\"'), acymailing_translation('ACY_SELECT_ELEMENT'))."');return false;}else{".$submit."}" : $submit;

		$onClick = !empty($onClick) ? $onClick : $js;

		$this->buttonOptions[] = '<button onclick="'.$onClick.'" class="acytoolbar_'.$class.'" title="'.$text.'"><span class="acyicon-'.$class.'"></span><span>'.$text.'</span></button>';
	}
}
