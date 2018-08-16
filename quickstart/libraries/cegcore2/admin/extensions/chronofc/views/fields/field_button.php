<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="two fields">
	<div class="four wide field">
		<label><?php el('Type'); ?></label>
		<select name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][type]" class="ui fluid dropdown">
			<option value="submit"><?php el('Submit'); ?></option>
			<option value="reset"><?php el('Reset'); ?></option>
			<option value="button"><?php el('Button'); ?></option>
		</select>
	</div>
	<div class="twelve wide field">
		<label><?php el('Content'); ?></label>
		<input type="text" value="<?php echo \G2\L\Str::camilize($field_type); ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][content]">
	</div>
</div>

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
	<div class="field">
		<label><?php el('Color'); ?></label>
		<input type="text" value="green" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][color]">
	</div>
</div>

<div class="ui fluid accordion">
	<div class="title ui header small"><i class="dropdown icon"></i><?php el('Advanced settings'); ?></div>
	<div class="content">
		<!--
		<div class="field">
			<label><?php el('On Click'); ?></label>
			<select name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][onclick]" class="ui fluid dropdown">
				<option value=""><?php el('Do nothing'); ?></option>
				<option value="fields_duplicate_parent(this);"><?php el('Duplicate'); ?></option>
				<option value="fields_remove_parent(this);"><?php el('Remove'); ?></option>
			</select>
		</div>
		-->
		<div class="two fields">
			<div class="field">
				<label><?php el('Extra attributes'); ?></label>
				<textarea name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][attrs]" rows="3"></textarea>
			</div>
		</div>

	</div>
</div>