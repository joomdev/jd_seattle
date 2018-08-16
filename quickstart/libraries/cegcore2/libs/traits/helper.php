<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Helper{
	public function Helper($alias){
		static $helpers;
		
		if(isset($this->helpers[$alias])){
			if(isset($helpers[$alias])){
				return $helpers[$alias];
			}else{
				if(!is_array($this->helpers[$alias])){
					//$view = $this->View();
					return $helpers[$alias] = new $this->helpers[$alias]($this);
				}
			}
		}
	}
	
}