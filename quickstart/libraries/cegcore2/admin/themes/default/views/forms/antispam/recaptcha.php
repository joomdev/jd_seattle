<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$lang = !empty($field['params']['lang']) ? $field['params']['lang'] : '';
	\GApp::document()->addJsFile('https://www.google.com/recaptcha/api.js?hl='.$lang);
?>
<div class="g-recaptcha" data-sitekey="<?php echo $field['params']['site_key']; ?>" data-theme="<?php echo $field['params']['theme']; ?>"></div>