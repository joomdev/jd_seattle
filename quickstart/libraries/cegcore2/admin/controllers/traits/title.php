<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Title {
	
	function setTitle($text, $url = ''){
		\GApp::document()->title(\GApp::document()->title().' - '.$text);
		
		$this->helpers['Header'] = ['name' => '\G2\H\Header'];
		$this->helpers['Header']['params']['text'] = $text;
		$this->helpers['Header']['params']['url'] = $url;
	}
	
}
?>