<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\Wordpress;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Route extends \G2\L\Route {
	
	public static function _($url, $xhtml = false, $absolute = false, $ssl = null){
		$alters = array(
			'chronoforms6server' => 'chronoforms6server',
			'chronoforms' => 'Chronoforms6',
			'chronoconnectivity' => 'chronoconnectivity6',
			'chronoforums' => 'chronoforums2',
			'chronocontact' => 'chronocontact',
			'chronohyper' => 'chronohyper',
			'chronodirector' => 'chronodirector',
			'chronomarket' => 'chronomarket',
			'chronosocial' => 'chronosocial',
		);
		
		$url = self::clean($url);
		//$url = \G2\L\Route::translate($url);
		
		foreach($alters as $k => $v){
			$url = str_replace('ext='.$k, 'page='.$v, $url);
		}
		
		return $url;
	}
}