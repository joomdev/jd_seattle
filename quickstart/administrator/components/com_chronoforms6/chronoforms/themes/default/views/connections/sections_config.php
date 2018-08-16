<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui sections-tab main-section main-area area" data-tab="sections-<?php echo $name; ?>">
	
	<div class="ui top attached tabular menu G2-tabs">
		<a class="item active" data-tab="sections-<?php echo $name; ?>-general"><?php echo $name; ?></a>
		<a class="item" data-tab="sections-<?php echo $name; ?>-permissions"><?php el('Permissions'); ?></a>
		<a class="item" data-tab="sections-<?php echo $name; ?>-preview" data-class="preview-tab" data-name="<?php echo $name; ?>"><?php el('Preview'); ?></a>
		<a class="item" data-tab="sections-<?php echo $name; ?>-template"><?php el('Template'); ?></a>
		<div class="item right" data-tab="sections-<?php echo $name; ?>-tools">
			<i class="icon window <?php if(!empty($this->data('Connection.sections.'.$name.'.minimized'))):?>maximize<?php else: ?>minimize<?php endif; ?> teal link minimize_area" data-hint="<?php el('Minimize/Maximize'); ?>" data-named="<?php echo $name; ?>"></i>
			<i class="icon sort yellow link sort_area" data-hint="<?php el('Sort'); ?>"></i>
			<i class="icon delete red link delete_area" data-hint="<?php el('Delete'); ?>"></i>
		</div>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="sections-<?php echo $name; ?>-general" id="<?php echo $name; ?>-general">
		<input type="hidden" value="<?php echo $name; ?>" name="Connection[sections][<?php echo $name; ?>][name]" readonly="true">
		<input type="hidden" value="0" name="Connection[sections][<?php echo $name; ?>][minimized]" data-minimized="<?php echo $name; ?>">
		
		<div class="ui segment active green draggable-receiver <?php if(!empty($this->data('Connection.sections.'.$name.'.minimized'))):?>hidden<?php endif; ?>" style="min-height:200px;" data-name="<?php echo $name; ?>">
			<?php if(!empty($views)): ?>
				<?php foreach($views as $view_n => $view): ?>
					<?php $this->view('views.connections.views_config', ['section_name' => $name, 'name' => $view['name'], 'type' => $view['type'], 'count' => $view_n, 'view' => $view, 'views' => $views]); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<div class="ui label teal fluid center aligned minimized-shadow <?php if(empty($this->data('Connection.sections.'.$name.'.minimized'))):?>hidden<?php endif; ?>" data-name="<?php echo $name; ?>"><?php el('Minimized'); ?></div>
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="sections-<?php echo $name; ?>-permissions">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[sections][<?php echo $name; ?>][owner_id]">
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[sections]['.$name.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="sections-<?php echo $name; ?>-preview" id="<?php echo $name; ?>-preview">
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="sections-<?php echo $name; ?>-template">
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[sections][<?php echo $name; ?>][auto]" data-ghost="1" value="0">
				<input type="checkbox" checked="checked" class="hidden" name="Connection[sections][<?php echo $name; ?>][auto]" value="1">
				<label><?php el('Auto generate on form save'); ?></label>
			</div>
		</div>
		<div class="field">
			<label><?php el('Template'); ?></label>
			<textarea name="Connection[sections][<?php echo $name; ?>][template]" rows="10"></textarea>
		</div>
	</div>
	
</div>