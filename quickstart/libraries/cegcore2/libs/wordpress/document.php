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
class Document extends \G2\L\Document {
	
	function _($name, $params = array()){
		if($name == 'jquery'){
			wp_enqueue_script('jquery');
			return;
		}
		if($name == 'jquery-migrate'){
			wp_enqueue_script('jquery-migrate');
			return;
		}
		if($name == 'jquery-ui'){
			$jquery_ui = array(
				"jquery-ui-core",			//UI Core - do not remove this one
				"jquery-ui-widget",
				"jquery-ui-mouse",
				"jquery-ui-accordion",
				"jquery-ui-autocomplete",
				"jquery-ui-slider",
				"jquery-ui-tabs",
				"jquery-ui-sortable",	
				"jquery-ui-draggable",
				"jquery-ui-droppable",
				"jquery-ui-selectable",
				"jquery-ui-position",
				"jquery-ui-datepicker",
				"jquery-ui-resizable",
				"jquery-ui-dialog",
				"jquery-ui-button"
			);
			foreach($jquery_ui as $script){
				wp_enqueue_script($script);
			}
			return;
		}
		
		parent::_($name, $params);
	}
	
	function title($title = null){
		if(is_null($title)){
			return wp_title('>', false);
		}else{
			add_filter('wp_title', function() use ($title){return $title;}, 10, 1);
		}
	}
	
	function meta($name, $content = null, $http = false){
		
	}
	
	public function buildHeader(){
		//$JDocument = \JFactory::getDocument();
		//$this->package();
		
		foreach($this->cssfiles as $k => $cssfile){
			//$JDocument->addStyleSheet($cssfile['href']);
			echo '<link href="'.$cssfile['href'].'" rel="stylesheet" />';
			//wp_enqueue_style($cssfile['href'], $cssfile['href']);
		}
		
		foreach($this->csscodes as $media => $codes){
			//$JDocument->addStyleDeclaration(implode("\n", $codes));
			echo '<style type="text/css">';
			echo implode("\n", $codes);
			echo '</style>';
		}
		
		foreach($this->jsfiles as$k => $jsfile){
			//$JDocument->addScript($jsfile['src']);//, 'text/javascript', true);
			echo '<script type="text/javascript" src="'.$jsfile['src'].'"></script>';
			//wp_enqueue_script($jsfile['src'], $jsfile['src']);
		}
		
		foreach($this->jscodes as $type => $codes){
			//$JDocument->addScriptDeclaration(implode("\n", $codes));
			echo '<script type="text/javascript">';
			echo implode("\n", $codes);
			echo '</script>';
		}
		
		ksort($this->headertags, SORT_STRING);
		foreach($this->headertags as $k => $code){
			//$JDocument->addCustomTag($code);
			echo $code;
		}
	}
	
}