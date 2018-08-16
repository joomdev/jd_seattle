<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="two fields">
	<div class="field">
		<label><?php el('Label'); ?></label>
		<input type="text" value="<?php echo \G2\L\Str::camilize($field_type); ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][label]">
	</div>
	<div class="field">
		<label><?php el('Placeholder'); ?></label>
		<input type="text" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][placeholder]">
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
</div>

<div class="three fields">
	
	<div class="field">
		<label><?php el('Start mode'); ?></label>
		<select name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][calendar][startmode]" class="ui fluid dropdown">
			<option value="day"><?php el('Day'); ?></option>
			<option value="month"><?php el('Month'); ?></option>
			<option value="year"><?php el('Year'); ?></option>
			<option value="hour"><?php el('Hour'); ?></option>
			<option value="minute"><?php el('Minute'); ?></option>
		</select>
	</div>
	
	<div class="field">
		<label><?php el('Type'); ?></label>
		<select name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][calendar][type]" class="ui fluid dropdown">
			<option value="date"><?php el('Date'); ?></option>
			<option value="time"><?php el('Time'); ?></option>
			<option value="datetime"><?php el('DateTime'); ?></option>
			<option value="month"><?php el('Month'); ?></option>
			<option value="year"><?php el('Year'); ?></option>
		</select>
	</div>
	
	<div class="field">
		<label><?php el('Format'); ?></label>
		<input type="text" value="y-m-d" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][calendar][format]">
	</div>
</div>

<div class="ui fluid accordion">
	<div class="title ui header small"><i class="dropdown icon"></i><?php el('Advanced settings'); ?></div>
	<div class="content">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Earliest date/time'); ?></label>
				<input type="text" placeholder="y-m-d" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][calendar][mindate]">
			</div>
			<div class="field">
				<label><?php el('Latest date/time'); ?></label>
				<input type="text" placeholder="y-m-d" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][calendar][maxdate]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Start calendar'); ?></label>
				<input type="text" placeholder="<?php echo '#calendar_id'; ?>" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][calendar][startcalendar]">
			</div>
			<div class="field">
				<label><?php el('End calendar'); ?></label>
				<input type="text" placeholder="<?php echo '#calendar_id'; ?>" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][calendar][endcalendar]">
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