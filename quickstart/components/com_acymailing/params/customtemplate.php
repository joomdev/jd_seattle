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
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
	echo 'This module can not work without the AcyMailing Component';
}

if(!ACYMAILING_J16){
	class JElementCustomtemplate extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			$link = 'index.php?option=com_acymailing&ctrl=tag&task=customtemplate&tmpl=component&plugin='.$value;
			if(!empty($node->_attributes['help'])) $link .= '&help='.(string)$node->_attributes['help'];
			$text = acymailing_popup($link, '<button class="btn" onclick="return false">'.acymailing_translation('ACY_CUSTOMTEMPLATE').'</button>');
			return $text;
		}
	}
}else{
	class JFormFieldCustomtemplate extends JFormField
	{
		var $type = 'help';

		function getInput(){
			$link = 'index.php?option=com_acymailing&ctrl=tag&task=customtemplate&tmpl=component&plugin='.$this->value;
			if(!empty($this->element['help'])) $link .= '&help='.(string)$this->element['help'];
			$text = acymailing_popup($link, '<button class="btn" onclick="return false">'.acymailing_translation('ACY_CUSTOMTEMPLATE').'</button>');
			return $text;
		}
	}
}
