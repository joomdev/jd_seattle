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

class plgAcymailingTagtime extends JPlugin{

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'tagtime');
			$this->params = new acyParameter($plugin->params);
		}
	}


	function acymailing_getPluginType(){

		if($this->params->get('frontendaccess') == 'none' && !acymailing_isAdmin()) return;
		$onePlugin = new stdClass();
		$onePlugin->name = acymailing_translation('ACY_TIME');
		$onePlugin->function = 'acymailingtagtime_show';
		$onePlugin->help = 'plugin-tagtime';

		return $onePlugin;
	}

	function acymailingtagtime_show(){

		$text = '<br style="clear:both;"/><div class="onelineblockoptions"><table class="acymailing_table" cellpadding="1">';

		$others = array();
		$others['{date}'] = 'DATE_FORMAT_LC';
		$others['{date:1}'] = 'DATE_FORMAT_LC1';
		$others['{date:2}'] = 'DATE_FORMAT_LC2';
		$others['{date:3}'] = 'DATE_FORMAT_LC3';
		$others['{date:4}'] = 'DATE_FORMAT_LC4';
		$others['{date:%m/%d/%Y}'] = '%m/%d/%Y';
		$others['{date:%d/%m/%y}'] = '%d/%m/%y';
		$others['{date:%A}'] = '%A';
		$others['{date:%B}'] = '%B';


		$k = 0;
		foreach($others as $tagname => $tag){
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\''.$tagname.'\');insertTag();" ><td class="acytdcheckbox"></td><td>'.$tag.'</td><td>'.acymailing_getDate(time(), acymailing_translation($tag)).'</td></tr>';
			$k = 1 - $k;
		}

		$text .= '</table></div>';

		echo $text;
	}

	function acymailing_replacetags(&$email, $send = true){

		$match = '#{date:?([^:].*)?}#Ui';
		$variables = array('subject', 'body', 'altbody');

		foreach($variables as $var){
			$email->$var = str_replace(array('{mailid}', '%7Bmailid%7D', '{emailsubject}'), array($email->mailid, $email->mailid, $email->subject), $email->$var);
		}
		$email->body = str_replace('{textversion}', nl2br($email->altbody), $email->body);


		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match, $email->$var, $results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}

		if(!$found) return;

		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				$arguments = explode('|', strip_tags($allresults[1][$i]));
				$parameter = new stdClass();
				$parameter->format = $arguments[0];
				for($i = 1; $i < count($arguments); $i++){
					$args = explode(':', $arguments[$i]);
					$arg0 = trim($args[0]);
					if(isset($args[1])){
						$parameter->$arg0 = $args[1];
					}else{
						$parameter->$arg0 = true;
					}
				}

				$time = time();
				if(!empty($parameter->senddate) && !empty($email->senddate)) $time = $email->senddate;
				if(!empty($parameter->add)) $time += intval($parameter->add);
				if(!empty($parameter->remove)) $time -= intval($parameter->remove);

				if(empty($parameter->format) OR is_numeric($parameter->format)){
					$tags[$oneTag] = acymailing_getDate($time, acymailing_translation('DATE_FORMAT_LC'.$parameter->format));
				}else{
					$tags[$oneTag] = acymailing_getDate($time, $parameter->format);
				}
			}
		}

		foreach(array_keys($results) as $var){
			$email->$var = str_replace(array_keys($tags), $tags, $email->$var);
		}
	}
}//endclass
