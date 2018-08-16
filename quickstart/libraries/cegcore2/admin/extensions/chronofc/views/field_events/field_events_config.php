<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<input type="hidden" class="fields_events_counter" value="<?php echo !empty($view['events']) ? max(array_keys($view['events'])) : 0; ?>">
<?php
	if(empty($view['events'])){
		$view['events'] = [1];
	}
	if(empty($events_events)){
		$events_events = ['=' => rl('equals'), '!=' => rl('not equals'), 'change' => rl('Change')];
	}
?>
<?php foreach($view['events'] as $ke => $field_event): ?>
<div class="field field_event">
	<?php if(!empty($events_group)): ?>
	<input type="hidden" value="1" name="Connection[views][<?php echo $n; ?>][events][<?php echo $ke; ?>][group]">
	<?php endif; ?>
	<div class="fields">
		<div class="seven wide field">
			<label><?php el('On'); ?></label>
			<select name="Connection[views][<?php echo $n; ?>][events][<?php echo $ke; ?>][sign]" class="ui fluid dropdown">
				<?php foreach($events_events as $sign => $events_event): ?>
				<option value="<?php echo $sign; ?>"><?php echo $events_event; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="seven wide field">
			<label><?php el('Actions'); ?></label>
			<select name="Connection[views][<?php echo $n; ?>][events][<?php echo $ke; ?>][action][]" class="ui fluid dropdown" multiple>
				<option value="enable"><?php el('Enable'); ?></option>
				<option value="disable"><?php el('Disable'); ?></option>
				<option value="show"><?php el('Show'); ?></option>
				<option value="hide"><?php el('Hide'); ?></option>
				<option value="disable_validation"><?php el('Disable validation'); ?></option>
				<option value="enable_validation"><?php el('Enable validation'); ?></option>
				<option value="reload"><?php el('Reload'); ?></option>
				<option value="function"><?php el('Function'); ?></option>
				<option value="add"><?php el('Add to'); ?></option>
				<option value="sub"><?php el('Subtract from'); ?></option>
				<option value="multiply"><?php el('Multiply with'); ?></option>
			</select>
		</div>
		<div class="two wide field">
			<label>&nbsp;</label>
			<button type="button" class="ui button icon compact green tiny add_field_event"><i class="plus icon"></i></button>
			<button type="button" class="ui button icon compact red tiny <?php if($ke == 0): ?>hidden<?php endif; ?> delete_field_event"><i class="cancel icon"></i></button>
		</div>
	</div>
	<div class="fields">
		<?php if(!isset($events_values)): ?>
		<div class="seven wide field">
			<label><?php el('Value(s)'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][events][<?php echo $ke; ?>][value]" rows="1" data-autoresize="1"></textarea>
		</div>
		<?php endif; ?>
		<div class="seven wide field">
			<label><?php el('Element(s) identifier'); ?>
			<i class="icon info circular blue inverted small" data-hint="<?php el('Enter a field name, or an element id preceded by #, or an element class preceded by .'); ?>"></i>
			</label>
			<textarea name="Connection[views][<?php echo $n; ?>][events][<?php echo $ke; ?>][identifier]" rows="1" data-autoresize="1"></textarea>
		</div>
	</div>
	<div class="ui divider fitted"></div>
</div>
<?php endforeach; ?>