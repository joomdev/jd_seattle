<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$_output = '';
	
	if(!empty($function['sections'])){
		$sections = explode("\n", $function['sections']);
		
		foreach($sections as $section){
			$_output .= $this->Parser->section(trim($section));
		}
		
	}
	
	if(!empty($function['keepalive'])){
		\GApp::document()->__('keepalive');
	}
	
	if($function['display_type'] == 'form'){
		$views_path = \G2\Globals::ext_path('chronofc', 'admin').'views'.DS.'form'.DS.'form_output.php';
		$view_data = $function + ['content' => $_output];
		echo $this->view($views_path, ['view' => $view_data], true);
	}else if($function['display_type'] == 'div'){
		echo '<div class="ui form">';
		echo $_output;
		echo '</div>';
	}else{
		echo $_output;
	}