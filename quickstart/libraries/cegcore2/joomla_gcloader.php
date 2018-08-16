<?php
/**
* COMPONENT FILE HEADER
**/
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
//basic checks
$success = array();
$fails = array();
if(version_compare(PHP_VERSION, '5.5.0') >= 0){
	$success[] = "PHP 5.5.0 or later found.";
}else{
	$fails[] = "Your PHP version is outdated: ".PHP_VERSION;
}

if(!empty($fails)){
	JError::raiseWarning(100, "Your PHP version should be 5.5 or later.");
	return;
}
//end basic checks
if(empty($fails)){
	
	require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'gcloader.php');
	
	class JoomlaGCLoader2 extends \G2\L\AppLoader{
		
	}
}