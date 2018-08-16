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

	class JElementCustomfields extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl='.(acymailing_isAdmin() ? '' : 'front').'chooselist&amp;task=customfields&amp;values='.$value.'&amp;control='.$control_name;
			$text = '<input class="inputbox" id="'.$control_name.'customfields" name="'.$control_name.'['.$name.']" type="text" style="width:100px" value="'.$value.'">';
			$text .= acymailing_popup($link, '<button class="btn" onclick="return false">'.acymailing_translation('Select').'</button>', '', 650, 375, 'link'.$control_name.'customfields');

			return $text;

		}
	}
}else{
	class JFormFieldCustomfields extends JFormField
	{
		var $type = 'help';

		function getInput() {
			$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl='.(acymailing_isAdmin() ? '' : 'front').'chooselist&amp;task=customfields&amp;values='.$this->value.'&amp;control=';
			$text = '<input class="inputbox" id="customfields" name="'.$this->name.'" type="text" style="width:100px" value="'.$this->value.'">';
			$text .= acymailing_popup($link, '<button class="btn" onclick="return false">'.acymailing_translation('Select').'</button>', '', 650, 375, 'linkcustomfields');

			return $text;

		}
	}
}
