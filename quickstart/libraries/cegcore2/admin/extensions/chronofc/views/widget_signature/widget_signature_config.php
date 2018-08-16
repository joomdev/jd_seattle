<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab views-tab active" data-tab="view-<?php echo $n; ?>">
	
	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="view-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="view-<?php echo $n; ?>-general">
		<input type="hidden" value="widget_signature" name="Connection[views][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][name]">
			</div>
			<div class="field">
				<label><?php el('Category'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][category]">
			</div>
		</div>
		
		<div class="two fields">
			
			<div class="field">
				<label><?php el('Field name'); ?></label>
				<input type="text" value="signature<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][params][name]">
				<small><?php el('The name of the hidden field used to store the calculated value.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Field ID'); ?></label>
				<input type="text" value="signature<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][params][id]">
				<small><?php el('The id of the hidden field used to store the calculated value.'); ?></small>
			</div>
			
		</div>
		
		<div class="field">
			<label><?php el('Label'); ?></label>
			<input type="text" value="Please sign" name="Connection[views][<?php echo $n; ?>][label]">
			<small><?php el('A label for the widget.'); ?></small>
		</div>
		
		
		<div class="three fields">
			<div class="field">
				<label><?php el('Width'); ?></label>
				<input type="text" value="400" name="Connection[views][<?php echo $n; ?>][width]">
				<small><?php el('The default signature pad width.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Height'); ?></label>
				<input type="text" value="150" name="Connection[views][<?php echo $n; ?>][height]">
				<small><?php el('The defualt signature pad height.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Clear text'); ?></label>
				<input type="text" value="Clear" name="Connection[views][<?php echo $n; ?>][clear][content]">
				<small><?php el('The text on the clear button.'); ?></small>
			</div>
			
		</div>
		
		<div class="field">
			<label><?php el('Style'); ?></label>
			<input type="text" value="border: 1px solid;" name="Connection[views][<?php echo $n; ?>][style]">
			<small><?php el('Default styling for the signature area, you can force static width or height here.'); ?></small>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][owner_id]">
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[views]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
	<button type="button" class="ui button compact red tiny close_config forms_conf"><?php el('Close'); ?></button>
</div>