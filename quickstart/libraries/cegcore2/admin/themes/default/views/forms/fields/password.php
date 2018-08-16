<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$input = $this->Fields->tag('input', $attrs);
	
	$output = $label.$input;
?>
<div class="field<?php echo $required.$error; ?>">
	<?php echo $output; ?>
	<?php echo $this->Fields->prompts($prompts); ?>
</div>