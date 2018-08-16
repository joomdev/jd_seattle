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

$config = acymailing_config();
acymailing_addScript(false, ACYMAILING_JS.'acymailing.js?v='.filemtime(ACYMAILING_MEDIA.'js'.DS.'acymailing.js'));

if(!ACYMAILING_J16){

	class JElementTermscontent extends JElement
	{

		function fetchElement($name, $value, &$node, $control_name)
		{
			$link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object=content';
			$text = '<input class="inputbox" id="'.$control_name.'termscontent" name="'.$control_name.'[termscontent]" type="text" style="width:100px" value="'.$value.'">';
			$text .= acymailing_popup($link, '<button class="btn" onclick="return false">'.acymailing_translation('SELECT').'</button>', '', 650, 375, 'termscontent');

			$js = "function jSelectArticle(id, title, object) {
				document.getElementById('".$control_name."termscontent').value = id;
				acymailing.closeBox(true);
			}";
			acymailing_addScript(true, $js);

			return $text;
		}
	}
}else{
	class JFormFieldTermscontent extends JFormField
	{
		var $type = 'termscontent';

		function getInput() {
			$method = 'acySelectArticle'.rand(1000, 9000);

			$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;object=content&amp;function='.$method;
			$text = '<input class="inputbox" id="termscontent" name="'.$this->name.'" type="text" style="width:100px" value="'.$this->value.'">';
			$text .= acymailing_popup($link, '<button class="btn" onclick="return false">'.acymailing_translation('SELECT').'</button>', '', 650, 375, 'termscontent');

			$js = "window.".$method." = function(id, title, catid, object) {
					document.querySelector('input[name=\"".$this->name."\"]').value = id;
					acymailing.closeBox(true);
				}";
			acymailing_addScript(true, $js);
			return $text;
		}
	}
}
