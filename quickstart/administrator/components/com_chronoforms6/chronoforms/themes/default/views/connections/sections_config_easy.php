<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segmenthh sections-tab main-section" data-tab="sections-<?php echo $name; ?>">
	
	<div class="ui top attached tabular menu G2-tabs">
		<a class="item active" data-tab="sections-<?php echo $name; ?>-general"><?php echo $name; ?></a>
		<a class="item" data-tab="sections-<?php echo $name; ?>-preview" data-class="preview-tab" data-name="<?php echo $name; ?>">
			<?php el('Preview'); ?>&nbsp;&nbsp;<i class="icon tv large blue"></i>
		</a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="sections-<?php echo $name; ?>-general" id="<?php echo $name; ?>-general">
		<input type="hidden" value="<?php echo $name; ?>" name="Connection[sections][<?php echo $name; ?>][name]" readonly="true">
		
		<div class="ui segment active green draggable-receiver" style="min-height:200px;" data-name="<?php echo $name; ?>">
			<?php if(!empty($views)): ?>
				<?php foreach($views as $view_n => $view): ?>
					<?php $this->view('views.connections.views_config', ['section_name' => $name, 'name' => $view['name'], 'type' => $view['type'], 'count' => $view_n, 'view' => $view, 'views' => $views]); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="sections-<?php echo $name; ?>-preview" id="<?php echo $name; ?>-preview">
		
	</div>
</div>