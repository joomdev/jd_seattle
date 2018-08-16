<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="two fields">
	<div class="four wide field">
		<label><?php el('Tag'); ?></label>
		<input type="text" value="div" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][tag]">
	</div>
	<div class="twelve wide field">
		<label><?php el('Class'); ?></label>
		<input type="text" value="field" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][class]">
	</div>
</div>

<div class="field">
	<label><?php el('Content'); ?></label>
	<textarea name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][content]" rows="3"><?php echo \G2\L\Str::camilize($field_type); ?></textarea>
</div>