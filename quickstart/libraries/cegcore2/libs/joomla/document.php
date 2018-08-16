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
class Document extends \G2\L\Document {

	
	function _($name, $params = array()){
		if(\GApp::instance()->tvout != 'inline'){
			if($name == 'jquery'){
				\JHtml::_('jquery.framework');
				return;
			}
		}
		parent::_($name, $params);
	}
	/*
	function addCssFile($path, $media = 'screen'){
		$document = \JFactory::getDocument();
		$document->addStyleSheet($path);
	}

	function addJsFile($path, $type = 'text/javascript'){
		$document = \JFactory::getDocument();
		$document->addScript($path);
	}
	
	function addCssCode($content, $media = 'screen'){
		$document = \JFactory::getDocument();
		$document->addStyleDeclaration($content);
	}

	function addJsCode($content, $type = 'text/javascript'){
		$document = \JFactory::getDocument();
		$document->addScriptDeclaration($content);
	}
	*/
	function title($title = null){
		$document = \JFactory::getDocument();
		if(is_null($title)){
			return $document->getTitle();
		}else{
			$document->setTitle($title);
		}
	}
	
	function meta($name, $content = null, $http = false){
		$document = \JFactory::getDocument();
		
		if(is_null($content)){
			return $document->getMetaData($name);
		}else{
			$document->setMetaData($name, $content, $http);
		}
	}
	
	public function buildHeader(){
		$JDocument = \JFactory::getDocument();
		//$this->package();
		
		foreach($this->cssfiles as $k => $cssfile){
			$JDocument->addStyleSheet($cssfile['href']);
		}
		
		foreach($this->csscodes as $media => $codes){
			$JDocument->addStyleDeclaration(implode("\n", $codes));
		}
		
		foreach($this->jsfiles as$k => $jsfile){
			$JDocument->addScript($jsfile['src']);//, 'text/javascript', true);
		}
		
		foreach($this->jscodes as $type => $codes){
			$JDocument->addScriptDeclaration(implode("\n", $codes));
		}
		
		ksort($this->headertags, SORT_STRING);
		foreach($this->headertags as $k => $code){
			$JDocument->addCustomTag($code);
		}
	}
	/*
	public static function _header($return = false){
		$doc = \G2\L\Document::getInstance();
		static $used;
		if(!isset($used)){
			$used = array();
		}
		$chunks = array();
		
		$_out = [];
		//$doc->package();
		
		$JDocument = \JFactory::getDocument();
		if(!method_exists($JDocument, 'addCustomTag')){
			return;
		}
		$HtmlHelper = new \G2\H\Html();
		//add css files list
		foreach($doc->cssfiles as $k => $cssfile){
			if(empty($used['cssfiles'][$k])){
				$used['cssfiles'][$k] = true;
				$cssfile['href'] = \G2\Globals::fix_urls($cssfile['href']);
				$chunks[] = $HtmlHelper->attrs($cssfile)->tag('link');
				unset($doc->cssfiles[$k]);
				
				if(!\G2\L\Config::get('template.semantic.dynamic', 1)){
					$JDocument->addStyleSheet($cssfile['href']);
					array_pop($chunks);
				}
			}
		}
		//add css code list
		foreach($doc->csscodes as $media => $codes){
			$chunks[] = $HtmlHelper->attrs(['type' => 'text/css', 'media' => $media])->content(implode("\n", $codes))->tag('style');
			foreach($doc->csscodes[$media] as $k => $code){
				unset($doc->csscodes[$media][$k]);
			}
			if(!\G2\L\Config::get('template.semantic.dynamic', 1)){
				$JDocument->addStyleDeclaration(implode("\n", $codes));
				array_pop($chunks);
			}
		}
		//add js files list
		foreach($doc->jsfiles as$k => $jsfile){
			if(empty($used['jsfiles'][$k])){
				$used['jsfiles'][$k] = true;
				$jsfile['src'] = \G2\Globals::fix_urls($jsfile['src']);
				//$jsfile['defer'] = 'defer';
				$chunks[] = $HtmlHelper->attrs($jsfile)->content('')->tag('script');
				unset($doc->jsfiles[$k]);
				
				if(!\G2\L\Config::get('template.semantic.dynamic', 1)){
					$JDocument->addScript($jsfile['src'], 'text/javascript', true);
					array_pop($chunks);
				}
			}
		}
		//add js code list
		foreach($doc->jscodes as $type => $codes){
			foreach($doc->jscodes[$type] as $k => $code){
				$chunks[] = $HtmlHelper->attrs(['type' => $type])->content($code)->tag('script');
				unset($doc->jscodes[$type][$k]);
				
				if(!\G2\L\Config::get('template.semantic.dynamic', 1)){
					if(!empty($used['jscodes'][md5($code)])){
						array_pop($chunks);
						continue;
					}
					$used['jscodes'][md5($code)] = true;
					$JDocument->addScriptDeclaration($code);
					array_pop($chunks);
				}
			}
		}

		foreach($doc->headertags as $k => $code){
			$chunks[] = $code;
			unset($doc->headertags[$k]);
		}
		
		foreach($chunks as $chunk){
			if(in_array($chunk, $JDocument->_custom)){
				continue;
			}
			if($return){
				$_out[] = \G2\Globals::fix_urls($chunk);
			}else{
				$JDocument->addCustomTag(\G2\Globals::fix_urls($chunk));
			}
		}
		
		unset($HtmlHelper);
		
		if($return){
			return implode("\n", $_out);
		}
	}
	*/
}