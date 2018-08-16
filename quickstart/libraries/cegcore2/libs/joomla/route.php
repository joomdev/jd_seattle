<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\Joomla;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Route extends \G2\L\Route {
	
	public static function _($url, $xhtml = false, $absolute = false, $ssl = null){
		$alters = array(
			'chronoforms6server' => 'com_chronoforms6server',
			//'chronomigrator' => 'com_chronomigrator',
			'chronoforms' => 'com_chronoforms6',
			'chronoconnectivity' => 'com_chronoconnectivity6',
			'chronoforums' => 'com_chronoforums2',
			//'chronolistings' => 'com_chronolistings',
			//'chronocommunity' => 'com_chronocommunity',
			//'chronosearch' => 'com_chronosearch',
			'chronocontact' => 'com_chronocontact',
			'chronohyper' => 'com_chronohyper',
			'chronodirector' => 'com_chronodirector',
			'chronomarket' => 'com_chronomarket',
			'chronosocial' => 'com_chronosocial',
		);
		
		$url = self::clean($url);
		//$url = \G2\L\Route::translate($url);
		
		foreach($alters as $k => $v){
			$url = str_replace('ext='.$k, 'option='.$v, $url);
		}
		
		if(is_string($xhtml)){
			$flags = str_split($xhtml);
			$xhtml = in_array('x', $flags);
			$absolute = in_array('f', $flags);
			$ssl = in_array('s', $flags);
			$dynamic = in_array('d', $flags);
		}
		
		if(GCORE_SITE == 'front'){
			if($xhtml){
				$url = str_replace('&', '&amp;', $url);
			}
			
			if(!empty($dynamic)){
				return $url;
			}
			
			if(!$absolute){
				return \JRoute::_($url, false, $ssl);
			}else{
				return \JRoute::_($url, false, -1); //dirty hack to get the full absolute url, fix later and create the full absolute url: \JURI::getInstance()->toString(array('scheme', 'host', 'port')));
			}
		}else{
			if(!$absolute){
				return $url;
			}else{
				return Url::full($url);
			}
		}
	}
}