<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(empty($view['display']) OR $view['display'] == 'navigation' OR $view['display'] == 'both'){
		echo $this->Paginator->navigation();
		echo '&nbsp;';
	}
	if(empty($view['display']) OR $view['display'] == 'limiter' OR $view['display'] == 'both'){
		echo $this->Paginator->limiter();
	}