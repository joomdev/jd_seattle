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
		<input type="hidden" value="joomla_user" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			<div class="two fields">
				<div class="field required">
					<label><?php el('Name field provider'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name_provider]">
				</div>
				<div class="field required">
					<label><?php el('Username field provider'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][username_provider]">
				</div>
			</div>
			
			<div class="two fields">
				<div class="field required">
					<label><?php el('Password field provider'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][password_provider]">
				</div>
				<div class="field required">
					<label><?php el('Email field provider'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][email_provider]">
				</div>
			</div>
			
			<div class="two fields">
				<div class="field required">
					<label><?php el('Blocked status provider'); ?></label>
					<input type="text" value="1" name="Connection[functions][<?php echo $n; ?>][block_provider]">
				</div>
				<div class="field required">
					<label><?php el('Activation code provider'); ?></label>
					<input type="text" value="{uuid:}" name="Connection[functions][<?php echo $n; ?>][activation_provider]">
				</div>
			</div>
			
			<div class="two fields">
				<div class="field required">
					<label><?php el('Groups ids provider'); ?></label>
					<input type="text" value="{value:[2]}" name="Connection[functions][<?php echo $n; ?>][groups_provider]">
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Data override'); ?></label>
				<textarea placeholder="<?php el('Multiline list of array fields'); ?>" name="Connection[functions][<?php echo $n; ?>][data_override]" rows="8"></textarea>
			</div>
			
			<div class="field required">
				<label><?php el('User exists error'); ?></label>
				<input type="text" value="<?php el('A user with the same username or email already exists.'); ?>" name="Connection[functions][<?php echo $n; ?>][userexists_error]">
				<small><?php el('Error message displayed when a user with the same username or email address already exists.'); ?></small>
			</div>
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