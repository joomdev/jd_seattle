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


class TagViewTag extends acymailingView{

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function tag(){
		acymailing_addStyle(false, ACYMAILING_CSS.'frontendedition.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'frontendedition.css'));

		acymailing_importPlugin('acymailing');
		$tagsfamilies = acymailing_trigger('acymailing_getPluginType');

		$defaultFamily = reset($tagsfamilies);
		if(!is_object($defaultFamily)) $defaultFamily = end($tagsfamilies);
		$fctplug = acymailing_getUserVar(ACYMAILING_COMPONENT.".tag", 'fctplug', $defaultFamily->function, 'cmd');

		ob_start();
		$defaultContents = acymailing_trigger($fctplug);
		$defaultContent = ob_get_clean();

		$js = 'function insertTag(){if(window.parent.insertTag(window.document.getElementById(\'tagstring\').value)) {acymailing.closeBox(true);}}';
		$js .= 'function setTag(tagvalue){window.document.getElementById(\'tagstring\').value = tagvalue;}';
		$js .= 'function showTagButton(){window.document.getElementById(\'insertButton\').style.display = \'inline\'; window.document.getElementById(\'tagstring\').style.display=\'inline\';}';
		$js .= 'function hideTagButton(){}';
		$js .= 'try{window.parent.previousSelection = window.parent.getPreviousSelection(); }catch(err){window.parent.previousSelection=false; }';

		acymailing_addScript(true, $js);


		$this->fctplug = $fctplug;
		$type = acymailing_getVar('string', 'type', 'news');
		$this->type = $type;
		$this->defaultContent = $defaultContent;
		$this->tagsfamilies = $tagsfamilies;
		$ctrl = acymailing_getVar('string', 'ctrl');
		$this->ctrl = $ctrl;
	}

	function form(){
		$plugin = acymailing_getVar('string', 'plugin');
		$plugin = preg_replace('#[^a-zA-Z0-9_]#Uis', '', $plugin);
		$templatePath = ACYMAILING_MEDIA.'plugins'.DS.$plugin.'.php';
		$body = '';
		if(file_exists($templatePath)) $body = file_get_contents($templatePath);
		$help = acymailing_getVar('string', 'help');
		$help = preg_replace('#[^a-zA-Z0-9]#Uis', '', $help);
		$help = empty($help) ? $plugin : $help;

		$this->help = $help;
		$this->plugin = $plugin;
		$this->body = $body;
	}
}
