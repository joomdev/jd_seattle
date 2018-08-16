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
		<input type="hidden" value="switch" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="field forms_conf">
			<label><?php el('Designer Label'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][label]">
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="two fields">
				<div class="field required">
					<label><?php el('Data provider'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][data_provider]">
					<small><?php el('The source data to use for switching, can be a data command for example, {data:field_name}'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][return]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][return]" value="1">
					<label><?php el('Return the result as var?'); ?></label>
					<small><?php el('Should the result be retuned inside a var {var:NAME}'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][array]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][array]" value="1">
					<label><?php el('Handle data arrays'); ?></label>
					<small><?php el('If enabled and an array is provided then all values will be checked in the source array.'); ?></small>
				</div>
			</div>
			
			<div class="field required">
				<label><?php el('Values setup'); ?></label>
				<textarea placeholder="<?php el('Multiline list'); ?>" name="Connection[functions][<?php echo $n; ?>][values]" rows="10"></textarea>
				<small><?php el('Multi line list of possible value and returned result.'); ?></small>
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