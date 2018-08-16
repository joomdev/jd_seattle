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

	class JElementTestplug extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=cpanel&amp;task=plgtrigger&amp;plg='.$value.'&amp;plgtype='.$name;
			return acymailing_popup($link, '<button class="btn" onclick="return false">Click here</button>', '', 650, 375);
		}
	}
}else{
	class JFormFieldTestplug extends JFormField
	{
		var $type = 'testplug';

		function getInput() {
			$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=cpanel&amp;task=plgtrigger&amp;plg='.$this->value.'&amp;plgtype='.$this->fieldname;
			return acymailing_popup($link, '<button class="btn" onclick="return false">Click here</button>', '', 650, 375);
		}
	}
}
