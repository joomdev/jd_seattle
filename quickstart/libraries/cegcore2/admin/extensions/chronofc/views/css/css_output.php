<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$css = $this->Parser->parse($view['content'], true, true);
	\GApp::document()->addCssCode($css);