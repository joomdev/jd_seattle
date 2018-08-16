<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */
defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or define("GCORE_SITE", "front");
jimport('cegcore2.joomla_gcloader');
if(!class_exists('JoomlaGCLoader2')){
	JError::raiseWarning(100, "Please download the CEGCore framework from www.chronoengine.com then install it using the 'Extensions Manager'");
	return;
}

$chronoforms6_setup = function() use($params){
	$formname = $params->get('chronoform', '');
	$chronoform = G2\L\Request::data('chronoform', '');
	$event = G2\L\Request::data('event', '');
	if(!empty($event)){
		if($formname != $chronoform){
			$event = 'load';
		}
	}
	return array('chronoform' => $formname, 'event' => $event);
};

$output = new JoomlaGCLoader2('front', 'chronoforms6', 'chronoforms', $chronoforms6_setup, array('controller' => '', 'action' => ''));