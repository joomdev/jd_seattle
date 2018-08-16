<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Clean{
	
	public function html($string){
		$tags = ['pre', 'p', 'blockquote', 'ol', 'ul', 'li', 'a', 'strong', 'em', 'u', 'img', 'br', 'span'];
		$voids = ['br', 'img'];
		$string = strip_tags($string, '<'.implode('><', $tags).'>');
		pr($string);
		pr('xx');
		//$tags = ['pre', 'p', 'h3', 'a', 'b', 'i', 'u'];
		
		foreach($tags as $tag){
			$open_pattern = '/<('.$tag.')(.*?)>/i';
			preg_match_all($open_pattern, $string, $matches);
			
			$close_pattern = '/<\/'.$tag.'>/i';
			preg_match_all($close_pattern, $string, $closes);
			
			if(!empty($matches[0])){
				if((count($matches[0]) != count($closes[0])) AND !in_array($tag, $voids)){
					$remove = array_merge($matches[0], $closes[0]);
					$string = str_replace($remove, '', $string);
					continue;
				}
				
				foreach($matches[0] as $k => $tag){
					if(empty($matches[2][$k])){
						continue;
					}
					$newTag = false;
					//create new attrs
					$attrs = $matches[2][$k];
					preg_match_all('/(style|href|src)="(.*?)"/i', $attrs, $atmatches);
					pr($atmatches);
					if(!empty($atmatches[0])){
						$newAttrs = [];
						foreach($atmatches[1] as $a => $attr){
							$attrVal = $atmatches[2][$a];
							if($attr == 'href'){
								$attrVal = urlencode($attrVal);
							}
							$newAttrs[] = $attr.'="'.$attrVal.'"';
						}
						$newTag = '<'.$matches[1][$k].' '.implode(' ', $newAttrs).'>';
					}else{
						$newTag = '<'.$matches[1][$k].'>';
					}
					
					$string = substr_replace($string, $newTag, strpos($string, $tag), strlen($tag));
				}
			}
		}
		
		return $string;
	}
	
}