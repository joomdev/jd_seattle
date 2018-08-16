<?php
/**
* COMPONENT FILE HEADER
**/
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
class pkg_chronoforms6InstallerScript {
	function postflight($type, $parent){
		$mainframe = JFactory::getApplication();
		$parent->getParent()->setRedirectURL('index.php?option=com_chronoforms6&cont=installer');
	}
}
?>