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
		<input type="hidden" value="multi_page" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Data provider'); ?></label>
					<input type="text" value="{data:}" name="Connection[functions][<?php echo $n; ?>][data_provider]">
					<small><?php el('The source of the data set to be used in the multi page chain.'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Identifier'); ?></label>
					<input type="text" value="multipage" name="Connection[functions][<?php echo $n; ?>][session_id]">
					<small><?php el('Unique identifier for the multi page chain in this form.'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][data_merge]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][data_merge]" value="1">
					<label><?php el('Mix the multi page data with the global data?'); ?></label>
					<small><?php el('Instead of keeping the multi page data set stored in session, mix it with the request data, can then be accessed using {data:param_ame}'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][clear]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][clear]" value="1">
					<label><?php el('Clear multi page data?'); ?></label>
					<small><?php el('Can be enabled at the last page to reset the stored data set.'); ?></small>
				</div>
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