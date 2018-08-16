<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php if(empty($view['all'])): ?>
	<div class="ui checkbox selector">
		<input type="checkbox" class="hidden" name="<?php echo $this->Parser->parse($view['input_name'], true); ?>" value="<?php $this->Parser->parse($view['value']); ?>">
		<label></label>
	</div>
<?php else: ?>
	<div class="ui select_all checkbox">
		<input type="checkbox">
		<label></label>
	</div>
<?php endif; ?>