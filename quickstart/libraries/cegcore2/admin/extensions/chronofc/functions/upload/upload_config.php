<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="upload" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="field forms_conf">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][enabled]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[functions][<?php echo $n; ?>][enabled]" value="1">
					<label><?php el('Enabled'); ?></label>
				</div>
			</div>
			
			<div class="field private_config easy_disabled">
				<label><?php el('Upload directory path'); ?></label>
				<input type="text" value="{path:front}<?php echo DS.'uploads'.DS; ?>" name="Connection[functions][<?php echo $n; ?>][path]">
			</div>
			
			<div class="field">
				<label><?php el('Default Allowed extensions list'); ?></label>
				<input type="text" value="jpg,jpeg,png,gif,pdf,doc,docx,txt,zip" name="Connection[functions][<?php echo $n; ?>][extensions]">
				<small><?php el('This list will be used whenever an extensions list is missing from a file config.'); ?></small>
			</div>
			
			<div class="field forms_conf">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][autofields]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][autofields]" value="1" <?php if(is_null($this->data('Connection.functions.'.$n.'.type'))): ?>checked<?php endif; ?>>
					<label><?php el('Auto Upload file fields'); ?></label>
					<small><?php el('Auto upload enabled file fields ?'); ?></small>
				</div>
			</div>
			
			<div class="field easy_disabled">
				<label><?php el('Custom Files config'); ?></label>
				<textarea placeholder="<?php el('Multiline list, field_name:ext1,ext2'); ?>" name="Connection[functions][<?php echo $n; ?>][config]" rows="5"></textarea>
				<small><?php el('Multiline list, field_name:ext1,ext2'); ?></small>
			</div>
			
			<div class="field easy_disabled private_config">
				<label><?php el('File name provider'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][filename_provider]">
				<small><?php el('If not empty then the resulting value will used as the file name, you can use {var:FN.file.name} and {var:FN.file.extension} to get the file name and extension.'); ?></small>
			</div>
			
			<div class="two fields private_config">
				
				<div class="field">
					<label><?php el('Max size in KB'); ?></label>
					<input type="text" value="1000" name="Connection[functions][<?php echo $n; ?>][max_size]">
				</div>
				
				<div class="field easy_disabled">
					<label><?php el('Min size in KB'); ?></label>
					<input type="text" value="0" name="Connection[functions][<?php echo $n; ?>][min_size]">
				</div>
				
			</div>
			
			<div class="ui header dividing"><?php el('Errors'); ?></div>
				
			<div class="field">
				<label><?php el('Max size exceeded error'); ?></label>
				<input type="text" value="The file has exceeded the maximum size limit." name="Connection[functions][<?php echo $n; ?>][max_size_error]">
			</div>
			
			<div class="field easy_disabled">
				<label><?php el('Min size limit error'); ?></label>
				<input type="text" value="The file is below the minimum size limit." name="Connection[functions][<?php echo $n; ?>][min_size_error]">
			</div>
			
			<div class="field">
				<label><?php el('File extension error'); ?></label>
				<input type="text" value="The file extension is not permitted." name="Connection[functions][<?php echo $n; ?>][file_extension_error]">
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
	
</div>