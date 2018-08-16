<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(empty($event_name) OR $event_name == $function['_event']):
	$functions_path = \G2\Globals::ext_path('chronofc', 'admin').'functions'.DS.$type.DS.$type.'_config.php';
	$ini_path = \G2\Globals::ext_path('chronofc', 'admin').'functions'.DS.$type.DS.$type.'.ini';
	$info = parse_ini_file($ini_path);
?>
<div class="ui segment blue dragged" style="margin-bottom:5px;">
	<div class="ui label function_title"><i class="icon puzzle"></i><?php echo $info['title']; ?></div>
	<div class="ui label black"><?php el('Name:'); ?><div class="detail"><?php echo !empty($function) ? $name : $name.$count; ?></div></div>
	<?php if(!empty($function['label'])): ?>
	<div class="ui label blue basic"><?php echo $function['label']; ?></div>
	<?php endif; ?>
	<i class="icon setting blue link edit_dragged" data-hint="<?php el('Edit'); ?>"></i>
	<i class="icon sort orange link sort_dragged" data-hint="<?php el('Sort'); ?>"></i>
	<i class="icon delete red link delete_dragged" data-hint="<?php el('Delete'); ?>"></i>
	<i class="icon copy green link copy_dragged" data-hint="<?php el('Copy'); ?>" data-block="function" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=copy_element&tvout=view'); ?>"></i>
	<i class="icon save black link save_link G2-static" data-task="popup:#save-function-<?php echo $count; ?>" data-hint="<?php el('Save'); ?>"></i>
	<div class="ui popup top left transition hidden G2-static-popup" id="save-function-<?php echo $count; ?>" style="min-width:300px;">
		<div class="ui form">
			<div class="field required">
				<label><?php el('Block title'); ?></label>
				<input type="text" name="title" value="<?php echo isset($block_title) ? $block_title : (!empty($function) ? $name : $name.$count); ?>">
			</div>
			<div class="field">
				<label><?php el('Block ID (Optional)'); ?> <i class="icon info circular orange inverted small" data-hint="<?php el('If the ID matches another block id then the existing block will be updated.'); ?>"></i></label>
				<input type="text" name="block_id" value="<?php echo isset($block_id) ? $block_id : ''; ?>">
			</div>
			<div class="field">
				<div class="ui button black compact icon fluid G2-dynamic save_block" data-dtask="send/closest:.dragged" data-result="after/closest:.dragged" data-complete-message="<?php el("Block saved successfully."); ?>" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=save_block&tvout=view&type=functions'); ?>"><?php el('Save block'); ?></div>
			</div>
		</div>
	</div>
	<!--<i class="icon move purple link drag_link" data-hint="<?php el('Move'); ?>"></i>-->
	<div class="config_area transition hidden">
		<input type="hidden" value="" name="Connection[functions][<?php echo $count; ?>][_event]" class="dragged_parent">
		<?php
			
			if(empty($this->data['Connection']['functions'][$count])){
				$this->data['Connection']['functions'][$count] = ['name' => $name.$count];
			}
			
			$this->view($functions_path, ['n' => $count, 'function' => !empty($function) ? $function : []]);
		?>
	</div>
	<?php $func_name = !empty($function) ? $function['name'] : $type.$count; ?>
	<?php
		if(!empty($function['events'])){
			$fnevents = array_fill_keys(array_map('trim', explode(',', $function['events'])), array_values($info['events'])[0]);
			
		}else if(!empty($info['events'])){
			$fnevents = $info['events'];
		}
	?>
	<?php if(!empty($fnevents)): ?>
		<?php foreach($fnevents as $ename => $ecolor): ?>
			<div class="ui segment top attached secondary inverted draggable-receiver-title <?php echo $ecolor; ?>" style="padding:0.2em 1em;" data-name="<?php echo $func_name; ?>/<?php echo $ename; ?>">
				<i class="icon lightning"></i><?php echo $ename; ?>
				<i class="icon window <?php if(!empty($this->data('Connection.functions.'.$count.'.'.$ename.'.minimized'))):?>maximize<?php else: ?>minimize<?php endif; ?> white link minimize_area" data-hint="<?php el('Minimize/Maximize'); ?>" data-named="<?php echo $func_name; ?>/<?php echo $ename; ?>"></i>
			</div>
			<input type="hidden" value="0" name="Connection[functions][<?php echo $count; ?>][<?php echo $ename; ?>][minimized]" data-minimized="<?php echo $func_name; ?>/<?php echo $ename; ?>">
			<div class="ui segment bottom attached function_event draggable-receiver <?php echo $ecolor; ?> <?php if(!empty($this->data('Connection.functions.'.$count.'.'.$ename.'.minimized'))):?>hidden<?php endif; ?>" style="min-height:50px; margin-bottom:2px; border-top:0px !important;" data-name="<?php echo $func_name; ?>/<?php echo $ename; ?>">
				<?php if(!empty($functions)): ?>
					<?php foreach($functions as $function_n => $function): ?>
						<?php $this->view('views.connections.functions_config', ['event_name' => $func_name.'/'.$ename, 'name' => $function['name'], 'type' => $function['type'], 'count' => $function_n, 'function' => $function, 'functions' => $functions]); ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="ui bottom attached segment minimized-shadow <?php if(empty($this->data('Connection.functions.'.$count.'.'.$ename.'.minimized'))): ?>hidden<?php endif; ?>" data-name="<?php echo $func_name; ?>/<?php echo $ename; ?>">
				<div class="ui label teal fluid center aligned"><?php el('Minimized'); ?></div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
<?php endif; ?>