<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Model{
	public function Model($alias){
		static $models;
		if(isset($this->models[$alias])){
			if(isset($models[$alias])){
				return $models[$alias];
			}else{
				if(!is_array($this->models[$alias])){
					return $models[$alias] = new $this->models[$alias];
				}else{
					if(!empty($this->models[$alias]['name'])){
						return $models[$alias] = new $this->models[$alias]['name'];
					}else if(!empty($this->models[$alias]['table'])){
						return $models[$alias] = new \G2\L\Model(['name' => $alias, 'table' => $this->models[$alias]['table']]);
					}
				}
			}
		}
	}
	
}
?>