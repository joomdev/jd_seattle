<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="two fields">
	<div class="field">
		<label><?php el('Name'); ?></label>
		<input type="text" value="<?php echo $field_type; ?><?php echo $field_number; ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][name]">
	</div>
	<div class="field">
		<label><?php el('ID'); ?></label>
		<input type="text" value="<?php echo $field_type; ?><?php echo $field_number; ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][id]">
	</div>
</div>

<div class="two fields">
	<div class="field">
		<label><?php el('Value'); ?></label>
		<input type="text" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][value]">
	</div>
</div>