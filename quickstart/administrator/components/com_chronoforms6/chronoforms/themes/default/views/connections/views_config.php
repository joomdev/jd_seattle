<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(empty($section_name) OR $section_name == $view['_section']):
	$views_path = \G2\Globals::ext_path('chronofc', 'admin').'views'.DS.$type.DS.$type.'_config.php';
	$ini_path = \G2\Globals::ext_path('chronofc', 'admin').'views'.DS.$type.DS.$type.'.ini';
	$info = parse_ini_file($ini_path);
?>
<div class="ui segment blue dragged">
	<div class="ui label view_title"><?php echo $info['title']; ?></div>
	<div class="ui label black"><?php echo !empty($view) ? $name : $name.$count; ?></div>
	<?php if(!empty($view['label'])): ?>
	<div class="ui label blue basic" data-hint="<?php echo $view['label']; ?>"><?php echo substr($view['label'], 0, 40).(strlen($view['label']) > 30 ? '...' : ''); ?></div>
	<?php endif; ?>
	<?php if(!empty($view['params']['name'])): ?>
	<div class="ui label green"><?php echo $view['params']['name']; ?></div>
	<?php endif; ?>
	<i class="icon setting blue link edit_dragged" data-hint="<?php el('Edit'); ?>"></i>
	<i class="icon sort orange link sort_dragged" data-hint="<?php el('Sort'); ?>"></i>
	<i class="icon delete red link delete_dragged" data-hint="<?php el('Delete'); ?>"></i>
	<i class="icon copy green link copy_dragged" data-hint="<?php el('Copy'); ?>" data-block="view" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=copy_element&tvout=view'); ?>"></i>
	<i class="icon save black link save_link G2-static" data-task="popup:#save-view-<?php echo $count; ?>" data-hint="<?php el('Save'); ?>"></i>
	<div class="ui popup top left transition hidden G2-static-popup" id="save-view-<?php echo $count; ?>" style="min-width:300px;">
		<div class="ui form">
			<div class="field required">
				<label><?php el('Block title'); ?></label>
				<input type="text" name="title" value="<?php echo isset($block_title) ? $block_title : (!empty($view) ? $name : $name.$count); ?>">
			</div>
			<div class="field">
				<label><?php el('Block ID (Optional)'); ?> <i class="icon info circular orange inverted small" data-hint="<?php el('If the ID matches another block id then the existing block will be updated.'); ?>"></i></label>
				<input type="text" name="block_id" value="<?php echo isset($block_id) ? $block_id : ''; ?>">
			</div>
			<div class="field">
				<div class="ui button black compact icon fluid G2-dynamic save_block" data-dtask="send/closest:.dragged" data-result="after/closest:.dragged" data-complete-message="<?php el("Block saved successfully."); ?>" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=save_block&tvout=view&type=views'); ?>"><?php el('Save block'); ?></div>
			</div>
		</div>
	</div>
	<!--<i class="icon move purple link drag_link" data-hint="<?php el('Move'); ?>"></i>-->
	<div class="config_area transition hidden">
		<input type="hidden" value="" name="Connection[views][<?php echo $count; ?>][_section]" class="dragged_parent">
		<?php
			
			if(empty($this->data['Connection']['views'][$count])){
				$this->data['Connection']['views'][$count] = ['name' => $name.$count];
			}
			
			$this->view($views_path, ['n' => $count, 'view' => !empty($view) ? $view : []]);
		?>
	</div>
	<?php /*if(!empty($info['preview'])): ?>
		<div class="preview_area">
			<?php
				$preview_path = \G2\Globals::ext_path('chronofc', 'admin').'views'.DS.$type.DS.$type.'_output.php';
				$this->view($preview_path, ['n' => $count, 'view' => !empty($view) ? $view : ['params' => []]]);
			?>
		</div>
	<?php endif;*/ ?>
	<?php
		if(!empty($view['sections'])){
			$vwsections = array_fill_keys(array_map('trim', explode("\n", $view['sections'])), array_values($info['sections'])[0]);
			
		}else if(!empty($info['sections'])){
			$vwsections = $info['sections'];
		}
		if(!empty($info['sections2'])){
			$vwsections = array_merge($vwsections, $info['sections2']);
		}
	?>
	<?php if(!empty($vwsections)): ?>
		<?php foreach($vwsections as $ename => $ecolor): ?>
			<?php $ename = explode(':', $ename)[0]; ?>
			<div class="ui segment top attached secondary inverted <?php echo $ecolor; ?>" style="padding:0.2em 1em;">
				<?php echo $ename; ?>
				<i class="icon window <?php if(!empty($this->data('Connection.views.'.$count.'.'.$ename.'.minimized'))):?>maximize<?php else: ?>minimize<?php endif; ?> white link minimize_area" data-hint="<?php el('Minimize/Maximize'); ?>" data-named="<?php echo $type.$count; ?>/<?php echo $ename; ?>"></i>
			</div>
			<input type="hidden" value="0" name="Connection[views][<?php echo $count; ?>][<?php echo $ename; ?>][minimized]" data-minimized="<?php echo $type.$count; ?>/<?php echo $ename; ?>">
			<div class="ui segment view_section bottom attached draggable-receiver <?php if(!empty($this->data('Connection.views.'.$count.'.'.$ename.'.minimized'))):?>hidden<?php endif; ?>" style="min-height:50px;" data-name="<?php echo $type.$count; ?>/<?php echo $ename; ?>" data-title="<?php echo $ename; ?>">
				<?php if(!empty($views)): ?>
					<?php foreach($views as $view_n => $view): ?>
						<?php $this->view('views.connections.views_config', ['section_name' => $type.$count.'/'.$ename, 'name' => $view['name'], 'type' => $view['type'], 'count' => $view_n, 'view' => $view, 'views' => $views]); ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="ui bottom attached segment minimized-shadow <?php if(empty($this->data('Connection.views.'.$count.'.'.$ename.'.minimized'))):?>hidden<?php endif; ?>" data-name="<?php echo $type.$count; ?>/<?php echo $ename; ?>">
				<div class="ui label teal fluid center aligned"><?php el('Minimized'); ?></div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
<?php endif; ?>