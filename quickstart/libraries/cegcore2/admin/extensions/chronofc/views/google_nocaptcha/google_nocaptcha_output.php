<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$lang = $this->Parser->parse($view['lang'], true);
	
	if(0){
		echo '<button class="g-recaptcha ui button blue" data-sitekey="'.$view['site_key'].'" data-callback="">Submit</button>';
	}else{
		echo '<div class="g-recaptcha" data-sitekey="'.$view['site_key'].'" data-theme="'.$view['theme'].'"></div>';
	}
	
	\GApp::document()->addJsFile('https://www.google.com/recaptcha/api.js?hl='.$lang);