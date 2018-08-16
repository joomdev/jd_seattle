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
		<input type="hidden" value="google_nocaptcha" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="field forms_conf">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][enabled]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[functions][<?php echo $n; ?>][enabled]" value="1">
					<label><?php el('Enabled'); ?></label>
				</div>
			</div>
			
			<div class="field required">
				<label><?php el('Secret key'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][secret_key]">
				<small><?php el('This key is required and can be acquired from Google.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Error message'); ?></label>
				<input type="text" value="You didn't pass the NoCaptcha verification." name="Connection[functions][<?php echo $n; ?>][failed_error]">
				<small><?php el('If the check fails, this message will be displayed.'); ?></small>
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