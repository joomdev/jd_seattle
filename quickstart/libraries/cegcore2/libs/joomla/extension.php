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
class Extension extends \G2\L\Extension{
	var $map = [
		'chronoforms6server' => 'com_chronoforms6server',
		'chronoforms' => 'com_chronoforms6',
		'chronoconnectivity' => 'com_chronoconnectivity6',
		'chronoforums' => 'com_chronoforums2',
		'chronodirector' => 'com_chronodirector',
		'chronomarket' => 'com_chronomarket',
		'chronosocial' => 'com_chronosocial',
	];
	
	public function path($area = 'admin'){
		if(empty($this->map[$this->name])){
			return parent::path($area);
		}
		
		$path = '';
		if($area == 'admin'){
			$path .= JPATH_SITE.DS.'administrator'.DS.'components'.DS;
		}else{
			$path .= JPATH_SITE.DS.'components'.DS;
		}
		
		$path .= $this->map[$this->name].DS.$this->name.DS;
		return $path;
	}
	
	public function url($area = 'admin'){
		if(empty($this->map[$this->name])){
			return parent::url($area);
		}
		
		$path = '';
		if($area == 'admin'){
			$path .= \JFactory::getURI()->root().'administrator/components/';
		}else{
			$path .= \JFactory::getURI()->root().'components/';
		}
		
		$path .= $this->map[$this->name].'/'.$this->name.'/';
		return $path;
	}
}