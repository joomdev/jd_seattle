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
		<input type="hidden" value="form" name="Connection[views][<?php echo $n; ?>][type]">
		
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
				<label><?php el('Event'); ?></label>
				<input type="text" value="save" name="Connection[views][<?php echo $n; ?>][event]">
				<small><?php el('To which event this form should submit the data ?'); ?></small>
			</div>
			<div class="ten wide field">
				<label><?php el('Action URL and/or parameters'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][parameters]">
				<small><?php el('Enter a full qualified action URL or a string of parameters to be included, example: par1=val1&par2=val2'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Data provider'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][data_provider]">
				<small><?php el('The data set used to provide the fields data of this form, the request data is also used.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Validation messages'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][validation][type]" class="ui fluid dropdown">
					<option value="inline"><?php el('Inline tooltips'); ?></option>
					<option value="message"><?php el('Errors list below form'); ?></option>
				</select>
				<small><?php el('How to display the validation messages.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][dynamic]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][dynamic]" value="1">
				<label><?php el('AJAX form?'); ?></label>
				<small><?php el('Use AJAX to submit this form without page reload ?'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Content'); ?></label>
			<textarea placeholder="<?php el('HTML or PHP Code with tags'); ?>" name="Connection[views][<?php echo $n; ?>][content]" rows="10"></textarea>
			<small><?php el('Your form contents, usually contains a call of one of more fields views.'); ?></small>
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