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

	class JElementNewsletters extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			$results = acymailing_loadObjectList("SELECT `mailid`, CONCAT(subject,' ( ',mailid,' )') as `title` FROM #__acymailing_mail WHERE `type`='news' AND (`senddate` IS NULL OR `senddate` < 1)AND `type` = 'news' ORDER BY `subject` ASC");
			$novalue = new stdClass();
			$novalue->mailid = 0;
			$novalue->title = ' - - - - - ';
			array_unshift($results,$novalue);

			return acymailing_select($results, $control_name.'['.$name.']' , 'size="1"', 'mailid', 'title', $value);
		}
	}

}else{
	class JFormFieldNewsletters extends JFormField
	{
		var $type = 'newsletters';

		function getInput() {

			$results = acymailing_loadObjectList("SELECT `mailid`, CONCAT(subject,' ( ',mailid,' )') as `title` FROM #__acymailing_mail WHERE `type`='news' AND (`senddate` IS NULL OR `senddate` < 1)AND `type` = 'news' ORDER BY `subject` ASC");
			$novalue = new stdClass();
			$novalue->mailid = 0;
			$novalue->title = ' - - - - - ';
			array_unshift($results,$novalue);

			return acymailing_select($results, $this->name , 'size="1"', 'mailid', 'title', $this->value);
		}
	}
}
