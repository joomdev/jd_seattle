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
		<input type="hidden" value="link" name="Connection[views][<?php echo $n; ?>][type]">
		
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
				<label><?php el('Event'); ?></label>
				<input type="text" value="view" name="Connection[views][<?php echo $n; ?>][event]">
				<small><?php el('The event to which the link should point.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Class'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][class]">
				<small><?php el('The link class attribute'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Content'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][content]">
			<small><?php el('The link text or html content'); ?></small>
		</div>
		
		<div class="field">
			<label><?php el('URL and/or URL parameters'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][parameters]">
			<small><?php el('A fully qualified url or key=val pairs to include in the link url.'); ?></small>
		</div>
		
		<div class="field">
			<label><?php el('Target'); ?></label>
			<select name="Connection[views][<?php echo $n; ?>][target]" class="ui fluid dropdown">
				<option value=""><?php el('Parent'); ?></option>
				<option value="_blank"><?php el('New page'); ?></option>
			</select>
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