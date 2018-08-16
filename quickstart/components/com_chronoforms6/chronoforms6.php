<?php
/**
* COMPONENT FILE HEADER
**/
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or define("GCORE_SITE", "front");
jimport('cegcore2.joomla_gcloader');
if(!class_exists('JoomlaGCLoader2')){
	JError::raiseWarning(100, "Please download the CEGCore framework from www.chronoengine.com then install it using the 'Extensions Manager'");
	return;
}

$chronoforms6_setup = function(){
	$mainframe = \JFactory::getApplication();
	//$conn = G2\L\Request::data('chronoform', '');
	$mparams = $mainframe->getPageParameters('com_chronoforms6');
	$connection = $mparams->get('form_name', '');
	$extra = $mparams->get('form_params', '');
	$controller = null;//'manager';//G2\L\Request::data('cont', 'manager');
	$params = [];
	if(!empty($connection)){
		if(!empty($extra)){
			parse_str($extra, $params);
			foreach($params as $pk => $pv){
				\G2\L\Request::set($pk, $pv);
			}
		}
		return array_merge(array('chronoform' => $connection, 'controller' => $controller), $params);
	}else{
		return array('controller' => $controller);
	}
};

$output = new JoomlaGCLoader2('front', 'chronoforms6', 'chronoforms', $chronoforms6_setup);