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
class Extension extends \G2\L\Extension{
	var $map = [
		'chronoforms6server' => 'chronoforms6server',
		'chronoforms' => 'chronoforms6',
		'chronoconnectivity' => 'chronoconnectivity6',
		'chronoforums' => 'chronoforums2',
		'chronodirector' => 'chronodirector',
		'chronomarket' => 'chronomarket',
		'chronosocial' => 'chronosocial',
	];
	
	public function path($area = 'admin'){
		if(empty($this->map[$this->name])){
			return parent::path($area);
		}
		
		$path = '';
		if($area == 'admin'){
			$path .= WP_PLUGIN_DIR.DS.$this->map[$this->name].DS.'admin'.DS;
		}else{
			$path .= WP_PLUGIN_DIR.DS.$this->map[$this->name].DS.'front'.DS;
		}
		
		$path .= $this->name.DS;
		return $path;
	}
	
	public function url($area = 'admin'){
		if(empty($this->map[$this->name])){
			return parent::url($area);
		}
		
		$path = '';
		if($area == 'admin'){
			$path .= plugins_url().$this->map[$this->name].'/admin/'.$this->name.'/';
		}else{
			$path .= plugins_url().$this->map[$this->name].'/front/'.$this->name.'/';
		}
		
		//$path .= $this->map[$this->name].'/'.$this->name.'/';
		return $path;
	}
}