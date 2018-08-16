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
		<input type="hidden" value="toolbar_button" name="Connection[views][<?php echo $n; ?>][type]">
		
		<div class="two fields">
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
			<div class="six wide field">
				<label><?php el('Class'); ?></label>
				<input type="text" value="ui button compact blue" name="Connection[views][<?php echo $n; ?>][class]">
				<small><?php el('The styling class for the button'); ?></small>
			</div>
			<div class="ten wide field">
				<label><?php el('Content'); ?></label>
				<input type="text" value="Button" name="Connection[views][<?php echo $n; ?>][content]">
				<small><?php el('The content of the button.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="six wide field">
				<label><?php el('Event'); ?></label>
				<input type="text" value="view" name="Connection[views][<?php echo $n; ?>][event]">
				<small><?php el('The event to which the data is sent.'); ?></small>
			</div>
			<div class="ten wide field">
				<label><?php el('URL and/or URL parameters'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][parameters]">
				<small><?php el('A full qualified url to send the data to or a list of key=value pairs to include in the request.'); ?></small>
			</div>
		</div>
		
		<div class="two grouped fields">
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][submit_data]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][submit_data]" value="1">
					<label><?php el('Submit button?'); ?></label>
					<small><?php el('Should this button submit the view form ?'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('List view name'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][form_name]">
				<small><?php el('The name of the view which the button is going to submit its data, this is usually a table view.'); ?></small>
			</div>
		</div>
		
		<div class="two grouped fields">
			
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][selections][required]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][selections][required]" value="1">
					<label><?php el('Selections required?'); ?></label>
					<small><?php el('Does this button requires selections to be made in the view ?'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Selections missing message'); ?></label>
				<input type="text" value="<?php el('Please make selections from the list.'); ?>" name="Connection[views][<?php echo $n; ?>][selections][message]">
				<small><?php el('The error message displayed when the button is clicked but no selections are made.'); ?></small>
			</div>
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
	
</div>