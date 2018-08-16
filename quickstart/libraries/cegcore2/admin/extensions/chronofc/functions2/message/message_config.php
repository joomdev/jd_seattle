<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">
	
	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="message" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="field forms_conf easy_disabled">
			<label><?php el('Designer Label'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][label]">
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Type'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][message_type]" class="ui fluid dropdown">
					<option value="success"><?php el('Confirmation'); ?></option>
					<option value="error"><?php el('Error'); ?></option>
					<option value="info"><?php el('Information'); ?></option>
					<option value="warning"><?php el('Warning'); ?></option>
				</select>
			</div>
			<div class="field">
				<label><?php el('Position'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][location]" class="ui fluid dropdown">
					<option value=""><?php el('System messages bar'); ?></option>
					<option value="body"><?php el('Body'); ?></option>
				</select>
			</div>
		</div>
		
		<div class="field required">
			<label><?php el('Content'); ?></label>
			<textarea rows="5" name="Connection[functions][<?php echo $n; ?>][content]"></textarea>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][owner_id]">
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[functions]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
</div>