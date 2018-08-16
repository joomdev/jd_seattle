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
if(!include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
	echo 'This module can not work without the AcyMailing Component';
}

if(!ACYMAILING_J16){
	class JElementPluginsfield extends JElement{
		function fetchElement($name, $value, &$node, $control_name){
			$link = 'index.php?option=com_acymailing&ctrl='.(acymailing_isAdmin() ? '' : 'front').'tag&task=plgtrigger&plg='.$value.'&fctName='.$value.'&tmpl=component';
			$text = acymailing_popup($link, '<button class="btn" onclick="return false">'.acymailing_translation('ACY_CONFIGURATION').'</button>');
			return $text;
		}
	}
}else{
	class JFormFieldPluginsfield extends JFormField{
		var $type = 'pluginsfield';

		function getInput(){
			$link = 'index.php?option=com_acymailing&ctrl='.(acymailing_isAdmin() ? '' : 'front').'tag&task=plgtrigger&plg='.$this->value.'&fctName='.$this->value.'&tmpl=component';
			$text = acymailing_popup($link, '<button class="btn" onclick="return false">'.acymailing_translation('ACY_CONFIGURATION').'</button>');
			return $text;
		}
	}
}
