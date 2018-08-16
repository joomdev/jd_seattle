<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$type = $this->Parser->parse($function['message_type'], true);
	$content = $this->Parser->parse($function['content'], true);
	
	//$this->Parser->messages[$type][$function['name']] = $content;
	if(empty($function['location'])){
		\GApp::session()->flash($type, $content);
	}else{
		echo '<div class="ui message '.$type.'">'.$content.'</div>';
	}
	