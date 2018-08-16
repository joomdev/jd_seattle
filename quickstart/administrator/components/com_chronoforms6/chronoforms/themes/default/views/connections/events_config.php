<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui events-tab main-event main-area area" data-tab="events-<?php echo $name; ?>">
	
	<div class="ui top attached tabular menu G2-tabs">
		<a class="item active" data-tab="events-<?php echo $name; ?>-general"><?php echo $name; ?></a>
		<a class="item" data-tab="events-<?php echo $name; ?>-permissions"><?php el('Permissions'); ?></a>
		<div class="item right" data-tab="events-<?php echo $name; ?>-tools">
			<i class="icon window <?php if(!empty($this->data('Connection.events.'.$name.'.minimized'))):?>maximize<?php else: ?>minimize<?php endif; ?> teal link minimize_area" data-hint="<?php el('Minimize/Maximize'); ?>" data-named="<?php echo $name; ?>"></i>
			<i class="icon sort yellow link sort_area" data-hint="<?php el('Sort'); ?>"></i>
			<i class="icon delete red link delete_area" data-hint="<?php el('Delete'); ?>"></i>
		</div>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="events-<?php echo $name; ?>-general">
		<input type="hidden" value="<?php echo $name; ?>" name="Connection[events][<?php echo $name; ?>][name]" readonly="true">
		<input type="hidden" value="0" name="Connection[events][<?php echo $name; ?>][minimized]" data-minimized="<?php echo $name; ?>">
		
		<div class="ui segment active green draggable-receiver <?php if(!empty($this->data('Connection.events.'.$name.'.minimized'))):?>hidden<?php endif; ?>" style="min-height:200px;" data-name="<?php echo $name; ?>">
			<?php if(!empty($functions)): ?>
				<?php foreach($functions as $function_n => $function): ?>
					<?php $this->view('views.connections.functions_config', ['event_name' => $name, 'name' => $function['name'], 'type' => $function['type'], 'count' => $function_n, 'function' => $function, 'functions' => $functions]); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<div class="ui label teal fluid center aligned minimized-shadow <?php if(empty($this->data('Connection.events.'.$name.'.minimized'))):?>hidden<?php endif; ?>" data-name="<?php echo $name; ?>"><?php el('Minimized'); ?></div>
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="events-<?php echo $name; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('On access denied'); ?></label>
				<input type="text" value="" name="Connection[events][<?php echo $name; ?>][access_denied]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[events][<?php echo $name; ?>][owner_id]">
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[events]['.$name.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
</div>