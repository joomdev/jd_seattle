<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$class = 'ui divider';
	
	if(!empty($view['text'])){
		$text = $this->Parser->parse($view['text'], true);
		$class .= ' horizontal header';
	}
	
	echo '<'.$view['tag'].' class="'.$class.'">';
	
	if(!empty($text)){
		echo $text;
	}
	
	echo '</'.$view['tag'].'>';