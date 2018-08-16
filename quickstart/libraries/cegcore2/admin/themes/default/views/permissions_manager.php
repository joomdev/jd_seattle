<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if($this->get('permissions_deactivated', false)){
		return;
	}
	ob_start();
?>
<script>
	jQuery(document).ready(function($) {
		function updatePermissions(dropdown, value){
			if(value == ''){
				//not set
				dropdown.removeClass('grey red green').addClass('');
			}else if(value == 0){
				//inherited
				dropdown.removeClass('grey red green').addClass('');
				dropdown.find('i').removeClass('ban check question').addClass('question');
				//find parent
				var current_depth = dropdown.closest('.field').find('i.depth').length;
				$.each(dropdown.closest('.field').prevAll('.field'), function(i, obj){
					var field_depth = $(obj).closest('.field').find('i.depth').length;
					var existing_value = $(obj).find('input').first().val();
					
					if(field_depth == current_depth - 1){
						$(obj).find('.ui.dropdown.permission').trigger('change');
					}
				});
			}else if(value == -1 || value == -2){
				//denied
				dropdown.removeClass('grey red green').addClass('red');
				dropdown.find('i').removeClass('ban check question').addClass('ban');
				updateChildren(dropdown, value, [0, -1], true);
			}else if(value == 1){
				//allowed
				dropdown.removeClass('grey red green').addClass('green');
				dropdown.find('i').removeClass('ban check question').addClass('check');
				updateChildren(dropdown, value, [0, 1, -1], false);
			}
		}
		
		function updateChildren(dropdown, value, update, disable){
			var current_depth = dropdown.closest('.field').find('i.depth').length;
			$.each(dropdown.closest('.field').nextAll('.field'), function(i, obj){
				var field_depth = $(obj).closest('.field').find('i.depth').length;
				var existing_value = $(obj).find('input').first().val();
				
				if(field_depth == current_depth + 1 && ($.inArray(parseInt(existing_value), update) > -1 || existing_value == '')){
					$(obj).find('input').first().val(value);
					$(obj).find('.ui.dropdown.permission').dropdown('set selected', value);
					$(obj).find('.ui.dropdown.permission').trigger('change');
					/*
					if(disable){
						$(obj).find('.ui.dropdown.permission').addClass('disabled');
					}else{
						$(obj).find('.ui.dropdown.permission').removeClass('disabled');
					}
					*/
					//$(obj).find('.ui.dropdown.permission').dropdown('refresh');
				}else{
					//return false;
					if(field_depth == current_depth){
						return false;
					}
				}
			});
		}
		$('.ui.dropdown.permission').on('change', function(){
			var value = $(this).find('input').first().val();
			updatePermissions($(this), value);
			
		});
		$('.ui.dropdown.permission').trigger('change');
	});
</script>
<?php
	$jscode = ob_get_clean();
	\GApp::document()->addHeaderTag($jscode);
?>
<?php $perm_selections = array(0 => rl('Inherited'), /*'' => rl('Not set'),*/ 1 => rl('Allowed'), -1 => rl('Denied'), -2 => rl('Banned')); ?>
<?php
	if(\G2\Globals::get('app') == 'wordpress'){
		$groups = array_merge($groups, \GApp::get_gcore_wp_usergroups());
	}
?>
<div class="ui grid">
	<div class="four wide column">
		<div class="ui vertical pointing menu fluid G2-tabs">
			<?php $counter = 0; ?>
			<?php foreach($perms as $action => $label): ?>
			<a class="red item<?php echo ($counter == 0) ? ' active':''; ?>" data-tab="perm-<?php echo $action; ?>"><?php echo $label; ?></a>
			<?php $counter++; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="twelve wide stretched column">
		<?php $counter = 0; ?>
		<?php foreach($perms as $action => $label): ?>
			<div class="ui segment tab<?php echo ($counter == 0) ? ' active':''; ?>" data-tab="perm-<?php echo $action; ?>">
				<?php foreach($groups as $k => $group): ?>
					<?php //echo $this->Html->formLine('Category[rules]['.$action.']['.$g_id.']', array('type' => 'dropdown', 'label' => $g_name, 'class' => 'A', 'options' => array(0 => rl('INHERITED'), '' => rl('NOT_SET'), 1 => rl('ALLOWED'), -1 => rl('DENIED')))); ?>
					<div class="field">
						<label><?php echo $group['Group']['title']; ?></label>
						<?php echo str_repeat('<i class="chevron right icon depth"></i>', $group['Group']['_depth']); ?>
						<div class="ui dropdown labeled icon buttond label permission">
							<i class="question icon"></i>
							<input type="hidden" name="<?php echo $model; ?>[rules][<?php echo $action; ?>][<?php echo $group['Group']['id']; ?>]" value="" />
							<div class="default text">------</div>
							<div class="menu">
								<?php foreach($perm_selections as $id => $title): ?>
								<div class="item" data-value="<?php echo $id; ?>"><?php echo $title; ?></div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php $counter++; ?>
		<?php endforeach; ?>
	</div>
</div>