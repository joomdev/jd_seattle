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
		<input type="hidden" value="repeater" name="Connection[views][<?php echo $n; ?>][type]">
		
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
			<div class="field">
				<label><?php el('Data provider'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][data_provider]">
				<small><?php el('The data set used for repeating the content, this should usually be an array.'); ?></small>
			</div>
			
			<div class="field">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][form]" data-ghost="1" value="">
					<input type="checkbox" checked class="hidden" name="Connection[views][<?php echo $n; ?>][form]" value="1">
					<label><?php el('Include form ?'); ?></label>
					<small><?php el('Should the content be placed inside form tags ? this is required for listing features like selectors and toolbar buttons.'); ?></small>
				</div>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Content'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][content]" rows="12"></textarea>
			<small><?php el('The content to be repeated, may contain PHP code.'); ?></small>
		</div>
		
		<div class="field">
			<label><?php el('Header'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][header]" rows="3"></textarea>
			<small><?php el('Content to be displayed before the repeated body content.'); ?></small>
		</div>
		
		<div class="field">
			<label><?php el('Footer'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][footer]" rows="3"></textarea>
			<small><?php el('Content to be displayed after the repeated body content.'); ?></small>
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