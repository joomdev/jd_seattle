<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-info"><?php el('Information'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="session_cart" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Item Data provider'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][data_provider]">
				</div>
				
				<div class="field">
					<label><?php el('Mode provider'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][mode_provider]">
				</div>
			</div>
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Item id provider'); ?></label>
					<input type="text" value="{data:item_id}" name="Connection[functions][<?php echo $n; ?>][id_data_provider]">
				</div>
				
				<div class="field">
					<label><?php el('Item quantity provider'); ?></label>
					<input type="text" value="{data:quantity}" name="Connection[functions][<?php echo $n; ?>][quantity_data_provider]">
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
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-info">
		
		<h4 class="ui header"><?php el('Function modes'); ?></h4>
		
		<table class="ui very basic selectable table">
			<tbody>
				<tr>
					<td class="collapsing right aligned">
						<h4 class="ui header">default mode</h4>
					</td>
					<td><?php el('Adds the item to the cart if no item with the same id exists, or sums the quantity value if it exists.'); ?></td>
				</tr>
				<tr>
					<td class="collapsing right aligned">
						<h4 class="ui header">remove</h4>
					</td>
					<td><?php el('Remove the item instead of adding it.'); ?></td>
				</tr>
				<tr>
					<td class="collapsing right aligned">
						<h4 class="ui header">replace</h4>
					</td>
					<td><?php el('Replace the item in the cart with the same id.'); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	
</div>