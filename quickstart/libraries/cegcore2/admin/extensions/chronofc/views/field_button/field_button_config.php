<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab views-tab active" data-tab="view-<?php echo $n; ?>">
	
	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="view-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-advanced"><?php el('Advanced'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-events"><?php el('Events'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="view-<?php echo $n; ?>-general">
		<input type="hidden" value="field_button" name="Connection[views][<?php echo $n; ?>][type]">
		
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
		
		<div class="two fields">
			<div class="four wide field">
				<label><?php el('Type'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][params][type]" class="ui fluid dropdown">
					<option value="submit"><?php el('Submit'); ?></option>
					<option value="reset"><?php el('Reset'); ?></option>
					<option value="button"><?php el('Button'); ?></option>
				</select>
			</div>
			<div class="twelve wide field">
				<label><?php el('Content'); ?></label>
				<input type="text" value="Send" name="Connection[views][<?php echo $n; ?>][content]">
			</div>
		</div>

		<div class="two fields">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="button<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][params][name]">
				<small><?php el('No spaces or special characters should be used here.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('ID'); ?></label>
				<input type="text" value="button<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][params][id]">
			</div>
		</div>

		<div class="two fields">
			<div class="field easy_disabled">
				<label><?php el('Value'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][params][value]">
			</div>
			<div class="field">
				<label><?php el('Color'); ?></label>
				<div class="ui fluid selection dropdown">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][color]" value="green" />
					<i class="dropdown icon"></i>
					<div class="default text"><?php el('Default'); ?></div>
					<div class="menu">
						<div class="item" data-value=""><div class="ui empty fluid label"></div></div>
						<div class="item" data-value="red"><div class="ui red empty fluid label"></div></div>
						<div class="item" data-value="orange"><div class="ui orange empty fluid label"></div></div>
						<div class="item" data-value="yellow"><div class="ui yellow empty fluid label"></div></div>
						<div class="item" data-value="olive"><div class="ui olive empty fluid label"></div></div>
						<div class="item" data-value="green"><div class="ui green empty fluid label"></div></div>
						<div class="item" data-value="teal"><div class="ui teal empty fluid label"></div></div>
						<div class="item" data-value="blue"><div class="ui blue empty fluid label"></div></div>
						<div class="item" data-value="violet"><div class="ui violet empty fluid label"></div></div>
						<div class="item" data-value="purple"><div class="ui purple empty fluid label"></div></div>
						<div class="item" data-value="pink"><div class="ui pink empty fluid label"></div></div>
						<div class="item" data-value="brown"><div class="ui brown empty fluid label"></div></div>
						<div class="item" data-value="grey"><div class="ui grey empty fluid label"></div></div>
						<div class="item" data-value="black"><div class="ui black empty fluid label"></div></div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Class'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][class]">
			</div>
			<div class="field">
				<label><?php el('Full width'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][fluid]" class="ui fluid dropdown">
					<option value="0"><?php el('No'); ?></option>
					<option value="1"><?php el('Yes'); ?></option>
				</select>
			</div>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-advanced">

		<!--
		<div class="field">
			<label><?php el('On Click'); ?></label>
			<select name="Connection[views][<?php echo $n; ?>][onclick]" class="ui fluid dropdown">
				<option value=""><?php el('Do nothing'); ?></option>
				<option value="fields_duplicate_parent(this);"><?php el('Duplicate'); ?></option>
				<option value="fields_remove_parent(this);"><?php el('Remove'); ?></option>
			</select>
		</div>
		-->
		<div class="field">
			<label><?php el('Extra attributes'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][attrs]" rows="3"></textarea>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Container class'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][container][class]">
			</div>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment small fields_events_list" data-tab="view-<?php echo $n; ?>-events">
		<?php $this->view(dirname(dirname(__FILE__)).DS.'field_events'.DS.'field_events_config.php', ['view' => $view, 'n' => $n, 'events_values' => false, 'events_events' => ['click' => rl('Click')]]); ?>
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