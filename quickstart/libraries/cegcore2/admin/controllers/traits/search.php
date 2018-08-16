<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Search {
	
	function Search($Model, $fields){
		
		if(!empty($this->data('search'))){
			foreach($fields as $k => $field){
				$Model->where($field, '%'.$this->data('search').'%', 'LIKE');
				
				if($k < count($fields) - 1){
					$Model->where('OR');
				}
			}
		}
	}
	
}
?>