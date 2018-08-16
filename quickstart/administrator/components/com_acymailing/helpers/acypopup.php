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

class acypopupHelper{

	function display($text, $title, $url, $id, $width, $height, $attr = '', $icon = '', $type = 'button', $dynamicUrl = false){
		static $loaded = false;

		if(!$loaded) {
			acymailing_addScript(false, ACYMAILING_JS . 'acymailing.js?v=' . filemtime(ACYMAILING_MEDIA . 'js' . DS . 'acymailing.js'));
			acymailing_addStyle(false, ACYMAILING_CSS . 'acypopup.css?v=' . filemtime(ACYMAILING_MEDIA . 'css' . DS . 'acypopup.css'));
			$loaded = true;
		}
		
		$params = ' id="'.$id.'" onclick="window.acymailing.openpopup(\''.$url.'\', '.intval($width).', '.intval($height).'); return false;"';
		if($type == 'button'){
			$html = '<button '.$this->getAttr($attr, 'btn btn-small').$params.'>';
		}else{
			$html = '<a '.$attr.' href="#"'.$params.'>';
		}

		if(!empty($icon)){
			$html .= '<i class="icon-16-'.$icon.'"></i> ';
		}
		$html .= $text.(($type == 'button') ? '</button>' : '</a>');

		return $html;
	}

	function getAttr($attr, $class){
		if(empty($attr)){
			return 'class="'.$class.'"';
		}
		$attr = ' '.$attr;
		if(strpos($attr, ' class="') !== false){
			$attr = str_replace(' class="', ' class="'.$class.' ', $attr);
		}elseif(strpos($attr, ' class=\'') !== false){
			$attr = str_replace(' class=\'', ' class=\''.$class.' ', $attr);
		}else{
			$attr .= ' class="'.$class.'"';
		}
		return trim($attr);
	}
}
