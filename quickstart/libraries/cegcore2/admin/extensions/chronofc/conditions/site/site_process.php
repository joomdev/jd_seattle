<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$app = JFactory::getApplication();
	if($app->isAdmin()){
		$this->set('_chronodirector.site.area', ['admin']);
	}else{
		$this->set('_chronodirector.site.area', ['front']);
	}