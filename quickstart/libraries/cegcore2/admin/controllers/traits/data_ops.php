<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait DataOps {
	
	function DataOps(){
		return DataOpsObject::getInstance($this);
	}
	
}

class DataOpsObject extends \G2\L\Component{
	
	function chunk($vinput){
		if(!empty($this->data[$vinput])){
			$tot = [];
			foreach($this->data[$vinput] as $ch){
				parse_str($ch, $d);
				$tot = array_replace_recursive($tot, $d);
			}
			unset($this->data[$vinput]);
			$this->data = array_merge($this->data, $tot);
		}
	}
	
}
?>