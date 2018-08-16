<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$attrs['type'] = !empty($field['params']['type']) ? $field['params']['type'] : 'submit';
	$attrs['value'] = !empty($field['params']['value']) ? $field['params']['value'] : $field['title'];
	
	$attrs['class'] = !empty($attrs['class']) ? $attrs['class'] : '';
	$attrs['class'] = 'ui button '.$field['params']['color'].' '.$attrs['class'];
	
	$attrs['class'] = $attrs['class'].(!empty($field['params']['fluid']) ? ' fluid' : '');
	
	$label = !empty($field['params']['text']) ? $field['params']['text'] : $field['title'];
	
	$input = $this->Fields->tag('button', $attrs, $label);
	
	$output = $input;
?>
<div class="field">
	<?php echo $output; ?>
</div>