<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="two fields">
	<div class="twelve wide field">
		<label><?php el('Label'); ?></label>
		<input type="text" value="<?php echo \G2\L\Str::camilize($field_type); ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][label]">
	</div>
	<div class="four wide field">
		<label><?php el('Multi file select?'); ?></label>
		<select name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][multiple]" class="ui fluid dropdown">
			<option value="0"><?php el('Single'); ?></option>
			<option value="multiple"><?php el('Multi selection'); ?></option>
		</select>
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

<div class="ui fluid accordion">
	<div class="title ui header small"><i class="dropdown icon"></i><?php el('Advanced settings'); ?></div>
	<div class="content">
		
		<div class="inline fields">
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][ghost][enabled]" data-ghost="1" value="0">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][ghost][enabled]" value="1">
					<label><?php el('Enable ghost'); ?></label>
				</div>
			</div>
			<div class="field">
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][ghost][value]" placeholder="<?php el('Ghost value'); ?>">
			</div>
		</div>

		<div class="two fields">
			<div class="field">
				<label><?php el('Extra attributes'); ?></label>
				<textarea name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][attrs]" rows="3"></textarea>
			</div>
			<div class="field">
				<label><?php el('Validation rules'); ?></label>
				<textarea name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][validation][rules]" rows="3"></textarea>
			</div>
		</div>

		<div class="two fields">
			<div class="field">
				<label><?php el('Container class'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][container][class]">
			</div>
		</div>
		
	</div>
</div>