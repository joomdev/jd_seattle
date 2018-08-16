<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(!empty($view['files'])){
		$files = explode("\n", $view['files']);
		$files = array_map('trim', $files);
		$files = array_filter($files);
		
		if(!empty($files)){
			foreach($files as $file){
				\GApp::document()->addJsFile($file);
			}
		}
	}
	
	$js = $this->Parser->parse($view['content'], true, true);
	
	if(!empty($view['domready'])){
		$js = implode("\n", ['jQuery(document).ready(function($){', $js, '});']);
	}
	
	\GApp::document()->addJsCode($js);