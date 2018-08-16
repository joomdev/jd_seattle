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
		<input type="hidden" value="csv" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="two fields">
			
				<div class="field required">
					<label><?php el('Data provider'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][data_provider]">
					<small><?php el('The source of the data to be saved, should be an array, if not provided below, array keys will be used as titles.'); ?></small>
				</div>
				
				<div class="field">
					<label><?php el('Action'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][action]" class="ui fluid dropdown">
						<option value="download"><?php el('Download'); ?></option>
						<option value="store"><?php el('Store'); ?></option>
						<option value="store_download"><?php el('Store and download'); ?></option>
					</select>
				</div>
				
			</div>
			
			<div class="two fields">
			
				<div class="five wide field">
					<label><?php el('Delimiter'); ?></label>
					<input type="text" value="," name="Connection[functions][<?php echo $n; ?>][delimiter]">
				</div>
				
				<div class="eleven wide field">
					<label><?php el('Titles'); ?></label>
					<textarea placeholder="<?php el('Multiline list'); ?>" name="Connection[functions][<?php echo $n; ?>][titles]" rows="6"></textarea>
					<small><?php el('name:Title, where "name" is a key name in the data set.'); ?></small>
				</div>
				
			</div>
			
			<div class="field">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][disable_titles]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][disable_titles]" value="1">
					<label><?php el('Disable header titles'); ?></label>
					<small><?php el('If enabled then header titles will not be included in the generated files.'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('File name'); ?></label>
				<input type="text" value="csv<?php echo $n; ?>.csv" name="Connection[functions][<?php echo $n; ?>][file_name]">
				<small><?php el('The download file name.'); ?></small>
			</div>
			
			<div class="field private_config">
				<label><?php el('Storage path'); ?></label>
				<input type="text" value="{path:front}<?php echo DS.'csv'.DS.'csv'.$n.'.csv'; ?>" name="Connection[functions][<?php echo $n; ?>][file_path]">
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