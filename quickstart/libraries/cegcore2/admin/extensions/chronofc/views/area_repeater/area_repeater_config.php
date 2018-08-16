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
		<input type="hidden" value="area_repeater" name="Connection[views][<?php echo $n; ?>][type]">
		
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
		
		<div class="field">
			<label><?php el('Designer Label'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][label]">
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Data provider'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][data_provider]">
				<small><?php el('The source of the data set used to repeat the content, this should be an array or an integer, you should use {var:area_repeater%s.key} in your fields names.', [$n]); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Keys provider (Optional)'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][keys_provider]">
				<small><?php el('Optional list of valid keys to limit the repetitions.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Repeater class'); ?></label>
			<input type="text" value="ui segment basic" name="Connection[views][<?php echo $n; ?>][class]">
		</div>
		
		<div class="ui header dividing"><?php el('Multiplier settings'); ?></div>
		
		<div class="field">
			<label><?php el('Enable content multiplier ?'); ?></label>
			<select name="Connection[views][<?php echo $n; ?>][multiplier]" class="ui fluid dropdown">
				<option value="0"><?php el('No'); ?></option>
				<option value="1"><?php el('Yes'); ?></option>
			</select>
			<small><?php el('Enable multiplying the content by clicking an add button defined by the selector class below.'); ?></small>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Multiply button selector'); ?></label>
				<input type="text" value=".multiply" readonly name="Connection[views][<?php echo $n; ?>][multiply_selector]">
			</div>
			<div class="field">
				<label><?php el('Remove button selector'); ?></label>
				<input type="text" value=".remove" readonly name="Connection[views][<?php echo $n; ?>][remove_selector]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Max multiplies'); ?></label>
				<input type="text" value="100" name="Connection[views][<?php echo $n; ?>][max_clones]">
				<small><?php el('The maximum number of multiplies to have at any time.'); ?></small>
			</div>
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