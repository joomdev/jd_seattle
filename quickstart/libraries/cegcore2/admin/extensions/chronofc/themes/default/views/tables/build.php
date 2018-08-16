<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	ob_start();
?>
<script>
	jQuery(document).ready(function($){
		$.each($('.enabled_chk'), function(i, chk){
			config_chk($(chk));
		});
		
		$('.enabled_chk').on('change', function(){
			config_chk($(this));
		});
		
		function config_chk(chk){
			if(chk.prop('checked')){
				chk.closest('tr').addClass('warning');
				chk.closest('tr').removeClass('error');
			}else{
				chk.closest('tr').addClass('error');
				chk.closest('tr').removeClass('warning');
			}
		}
		
		$('#table_name').on('change keyup', function(){
			$('#save_button').prop('disabled', true);
		});
	});
</script>
<?php
	$jscode = ob_get_clean();
	\GApp::document()->addHeaderTag($jscode);
?>
<form action="<?php echo r2('index.php?ext='.$this->extension.'&cont=tables'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">
	
	<h2 class="ui header"><?php el('Build table'); ?></h2>
	<div class="ui">
		<a class="compact ui button red icon labeled toolbar-button" href="<?php echo r2('index.php?ext='.$this->extension.'&cont=connections'); ?>">
			<i class="left arrow icon"></i><?php el('Cancel'); ?>
		</a>
		
		<button type="button" class="compact ui button green icon labeled toolbar-button" data-url="<?php echo r2('index.php?ext='.$this->extension.'&cont=tables&act=build'); ?>">
			<i class="refresh icon"></i><?php el('Refresh'); ?>
		</button>
		
		<button type="button" class="compact ui button black icon labeled toolbar-button" data-url="<?php echo r2('index.php?ext='.$this->extension.'&cont=tables&act=build&save=1'); ?>" id="save_button">
			<i class="save icon"></i><?php el('Save table'); ?>
		</button>
	</div>
	
	<div class="ui clearing divider"></div>
	
	<input type="hidden" name="gcb[0]" value="">
	
	<div class="ui form">
		<div class="field">
			<label><?php el('Table name'); ?></label>
			<input type="text" value="<?php echo $this->data('name'); ?>" name="table_name" id="table_name">
		</div>
	</div>
	
	<?php if(!empty($fields)): ?>
	<div class="ui message blue large">
		<?php el('This table already exists and will be modified.'); ?>
	</div>
	<?php endif; ?>
	
	<table class="ui selectable table">
		<thead>
			<tr>
				<th class="collapsing"><?php el('Add/Remove'); ?></th>
				<th class="collapsing"><?php el('Status'); ?></th>
				<th class="four wide"><?php el('Title'); ?></th>
				<th class="three wide"><?php el('Type[ (length) (sign)]'); ?></th>
				
				<th class="two wide"><?php el('Default'); ?></th>
				<th class="two wide"><?php el('Extra'); ?></th>
				<th class="two wide"><?php el('Index'); ?></th>
				<th class="collapsing"><?php el('Null'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $counter = 0; ?>
			
			<?php foreach($fields as $i => $field): ?>
			<tr>
				<td>
					<div class="ui checkbox selector">
						<input type="hidden" name="all[<?php echo $counter; ?>]" value="1">
						<input type="hidden" name="tbl[<?php echo $counter; ?>]" value="1">
						<input type="checkbox" checked class="hidden enabled_chk" name="fld[<?php echo $counter; ?>]" value="1">
						<label></label>
					</div>
				</td>
				<td><div class="ui label green small"><?php el('Table field'); ?></div></td>
				<td><input type="text" value="<?php echo $field['title']; ?>" name="title[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="<?php echo $field['type']; ?>" name="type[<?php echo $counter; ?>]"></td>
				
				<td><input type="text" value="<?php echo @$field['default']; ?>" name="default[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="<?php echo @$field['extra']; ?>" name="extra[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="<?php echo @$field['index']; ?>" name="index[<?php echo $counter; ?>]"></td>
				<td>
					<div class="ui checkbox">
						<input type="checkbox" <?php if(!empty($field['null'])): ?>checked<?php endif; ?> class="hidden" name="null[<?php echo $counter; ?>]" value="1">
						<label></label>
					</div>
				</td>
			</tr>
			<?php $counter++; ?>
			<?php endforeach; ?>
			
			<?php foreach($basics as $i => $basic): ?>
			<tr>
				<td>
					<div class="ui checkbox selector">
						<input type="hidden" name="all[<?php echo $counter; ?>]" value="1">
						<input type="checkbox" checked class="hidden enabled_chk" name="fld[<?php echo $counter; ?>]" value="1">
						<label></label>
					</div>
				</td>
				<td><div class="ui label orange small"><?php el('Suggested'); ?></div></td>
				<td><input type="text" value="<?php echo $basic['title']; ?>" name="title[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="<?php echo $basic['type']; ?>" name="type[<?php echo $counter; ?>]"></td>
				
				<td><input type="text" value="<?php echo @$basic['default']; ?>" name="default[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="<?php echo @$basic['extra']; ?>" name="extra[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="<?php echo @$basic['index']; ?>" name="index[<?php echo $counter; ?>]"></td>
				<td>
					<div class="ui checkbox">
						<input type="checkbox" <?php if(!empty($basic['null'])): ?>checked<?php endif; ?> class="hidden" name="null[<?php echo $counter; ?>]" value="1">
						<label></label>
					</div>
				</td>
			</tr>
			<?php $counter++; ?>
			<?php endforeach; ?>
			
			<?php foreach($views as $i => $view): ?>
			<tr>
				<td>
					<div class="ui checkbox selector">
						<input type="hidden" name="all[<?php echo $counter; ?>]" value="1">
						<input type="checkbox" checked class="hidden enabled_chk" name="fld[<?php echo $counter; ?>]" value="1">
						<label></label>
					</div>
				</td>
				<td><div class="ui label blue small"><?php el('Form field'); ?></div></td>
				<td><input type="text" value="<?php echo $view['title']; ?>" name="title[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="<?php echo $view['type']; ?>" name="type[<?php echo $counter; ?>]"></td>
				
				<td><input type="text" value="<?php echo @$view['default']; ?>" name="default[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="<?php echo @$view['extra']; ?>" name="extra[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="<?php echo @$view['index']; ?>" name="index[<?php echo $counter; ?>]"></td>
				<td>
					<div class="ui checkbox">
						<input type="checkbox" <?php if(!empty($view['null'])): ?>checked<?php endif; ?> class="hidden" name="null[<?php echo $counter; ?>]" value="1">
						<label></label>
					</div>
				</td>
			</tr>
			<?php $counter++; ?>
			<?php endforeach; ?>
			
			<?php for($i = 100; $i <= 130; $i++): ?>
			<tr>
				<td>
					<div class="ui checkbox selector">
						<input type="checkbox" class="hidden enabled_chk" name="fld[<?php echo $counter; ?>]" value="1" class="enabled_chk">
						<label></label>
					</div>
				</td>
				<td><div class="ui label black small"><?php el('Extra field'); ?></div></td>
				<td><input type="text" value="" name="title[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="VARCHAR(255)" name="type[<?php echo $counter; ?>]"></td>
				
				<td><input type="text" value="" name="default[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="" name="extra[<?php echo $counter; ?>]"></td>
				<td><input type="text" value="" name="index[<?php echo $counter; ?>]"></td>
				<td>
					<div class="ui checkbox">
						<input type="checkbox" class="hidden" name="null[<?php echo $counter; ?>]" value="1">
						<label></label>
					</div>
				</td>
			</tr>
			<?php $counter++; ?>
			<?php endfor; ?>
		</tbody>
	</table>
	
</form>
