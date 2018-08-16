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
		<input type="hidden" value="javascript" name="Connection[views][<?php echo $n; ?>][type]">
		
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
		
		<div class="field forms_conf">
			<label><?php el('Designer label'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][label]">
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][domready]" data-ghost="1" value="0">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][domready]" value="1">
				<label><?php el('Add inside domready event'); ?></label>
				<small><?php el('If enabled then the code will be placed inside a JQuery domready event and will run after the page is loaded.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Content'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][content]" rows="20"></textarea>
			<small><?php el('JavaScript code with OUT script tags'); ?></small>
		</div>
		
		<div class="field">
			<label><?php el('Files list'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][files]" rows="5"></textarea>
			<small><?php el('Multi line list of JS files paths to be loaded'); ?></small>
		</div>
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][owner_id]">
				<small><?php el('The value of the owner id with which the owner permission will be checked.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Toggle switch'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][toggler]">
				<small><?php el('If provided and is an empty value then the view will not be rendered.'); ?></small>
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[views]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
	<button type="button" class="ui button compact red tiny close_config forms_conf"><?php el('Close'); ?></button>
</div>