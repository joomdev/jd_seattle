<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\H;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Parser2 extends \G2\L\Helper{
	
	public function parse($content, $return = true){
		$pattern = '/\{(.*?)\}/i';
		
		preg_match_all($pattern, $content, $matches);
		
		if(!empty($matches[1])){
			foreach($matches[1] as $k => $match){
				$tag = $matches[0][$k];
				$default = null;
				if(strpos($match, '/') !== false){
					$parts = explode('/', $match);
					$match = $parts[0];
					$default = $parts[1];
				}
				
				$result = $this->data($match, $this->get($match, $default));
				if(strlen($content) == strlen($tag)){
					return $result;
				}
				
				if(is_array($result)){
					$result = json_encode($result);
				}
				
				$content = substr_replace($content, $result, strpos($content, $tag), strlen($tag));
			}
		}
		
		return $content;
	}
}