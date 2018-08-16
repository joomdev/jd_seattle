<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$form_id = \G2\L\Str::slug($view['name']);
?>
<form action="<?php echo r2($this->Parser->url('_self')); ?>" method="post" name="<?php echo $form_id; ?>" id="<?php echo $form_id; ?>" data-id="<?php echo $form_id; ?>" class="ui form">
	<div class="ui action input fluid">
		<input type="text" placeholder="<?php $this->Parser->parse($view['placeholder']); ?>" name="keywords">
		<button class="ui button"><?php $this->Parser->parse($view['button']); ?></button>
	</div>
</form>
	