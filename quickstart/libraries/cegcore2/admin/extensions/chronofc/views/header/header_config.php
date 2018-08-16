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
		<input type="hidden" value="header" name="Connection[views][<?php echo $n; ?>][type]">
		
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
		
		<div class="two fields">
			<div class="four field easy_disabled">
				<label><?php el('Tag'); ?></label>
				<input type="text" value="h2" name="Connection[views][<?php echo $n; ?>][tag]">
				<small><?php el('The header element tag.'); ?></small>
			</div>
			
			<div class="twelve wide field">
				<label><?php el('Main Text'); ?></label>
				<input type="text" value="Some header text" name="Connection[views][<?php echo $n; ?>][text]">
				<small><?php el('The main header text.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Sub Text'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][subtext]" rows="3"></textarea>
			<small><?php el('The text of the sub header, leave empty if you do not need a sub header.'); ?></small>
		</div>
		
		<div class="field easy_disabled">
			<label><?php el('Class'); ?></label>
			<input type="text" value="ui header dividing" name="Connection[views][<?php echo $n; ?>][class]">
			<small><?php el('The element class used for styling the output.'); ?></small>
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
	
	<button type="button" class="ui button compact red tiny close_config forms_conf forms_conf"><?php el('Close'); ?></button>
</div>