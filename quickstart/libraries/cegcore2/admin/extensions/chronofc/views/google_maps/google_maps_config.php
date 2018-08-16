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
		<input type="hidden" value="google_maps" name="Connection[views][<?php echo $n; ?>][type]">
		
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
		
		<div class="field required">
			<label><?php el('API key'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][api_key]">
			<small><?php el('Your GMaps API key provided by Google'); ?></small>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Map width'); ?></label>
				<input type="text" value="500px" name="Connection[views][<?php echo $n; ?>][width]">
			</div>
			<div class="field">
				<label><?php el('Map height'); ?></label>
				<input type="text" value="500px" name="Connection[views][<?php echo $n; ?>][height]">
			</div>
		</div>
		
		<div class="three fields">
			<div class="field">
				<label><?php el('Latitude'); ?></label>
				<input type="text" value="51.5" name="Connection[views][<?php echo $n; ?>][lat]">
			</div>
			<div class="field">
				<label><?php el('Longitude'); ?></label>
				<input type="text" value="-0.2" name="Connection[views][<?php echo $n; ?>][lng]">
			</div>
			<div class="field">
				<label><?php el('Zoom'); ?></label>
				<input type="text" value="6" name="Connection[views][<?php echo $n; ?>][zoom]">
				<small><?php el('The starting zoom of the map.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Places provider'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][places]">
			</div>
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
	
	<button type="button" class="ui button compact red tiny close_config forms_conf"><?php el('Close'); ?></button>
</div>