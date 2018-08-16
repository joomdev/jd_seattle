<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab views-tab active" data-tab="view-<?php echo $n; ?>">
	
	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="view-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-validation"><?php el('Validation'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-info"><?php el('Info'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-advanced"><?php el('Advanced'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="view-<?php echo $n; ?>-general">
		<input type="hidden" value="field_file" name="Connection[views][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][name]">
			</div>
			<div class="field">
				<label><?php el('Category'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][category]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="twelve wide field">
				<label><?php el('Label'); ?></label>
				<input type="text" value="File" name="Connection[views][<?php echo $n; ?>][label]">
			</div>
			<div class="four wide field">
				<label><?php el('Multi file select?'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][multiple]" class="ui fluid dropdown">
					<option value="0"><?php el('No'); ?></option>
					<option value="multiple"><?php el('Yes'); ?></option>
				</select>
			</div>
		</div>

		<div class="two fields">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="file<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][params][name]">
				<small><?php el('No spaces or special characters should be used here.'); ?></small>
				<small><?php el('If multi selection is enabled then the name must end with two square tags []'); ?></small>
			</div>
			<div class="field">
				<label><?php el('ID'); ?></label>
				<input type="text" value="file<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][params][id]">
			</div>
		</div>
		
		<div class="ui header dividing small"><?php el('Data settings'); ?></div>
		<div class="two fields">
			<div class="field">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][dynamics][email][enabled]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[views][<?php echo $n; ?>][dynamics][email][enabled]" value="1">
					<label><?php el('Include value in email'); ?></label>
					<small><?php el('The auto add fields setting must be enabled in the email function.'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][dynamics][save][enabled]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[views][<?php echo $n; ?>][dynamics][save][enabled]" value="1">
					<label><?php el('Save to database'); ?></label>
					<small><?php el('The auto save fields setting must be enabled in the save data function.'); ?></small>
				</div>
			</div>
		</div>
		
		<div class="ui header dividing small"><?php el('Upload and attachment settings'); ?></div>
		<div class="two fields">
			<div class="field">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][dynamics][upload][enabled]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[views][<?php echo $n; ?>][dynamics][upload][enabled]" value="1">
					<label><?php el('Upload to server'); ?></label>
					<small><?php el('The auto upload setting must be enabled in the upload function.'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][dynamics][attach][enabled]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[views][<?php echo $n; ?>][dynamics][attach][enabled]" value="1">
					<label><?php el('Attach to email'); ?></label>
					<small><?php el('The auto attach setting must be enabled in the email function.'); ?></small>
				</div>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Extensions'); ?></label>
			<input type="text" value="jpg,jpeg,png,gif,pdf,doc,docx,txt,zip" name="Connection[views][<?php echo $n; ?>][extensions]">
			<small><?php el('Comma separated list of extensions; If left empty, the global list will be used.'); ?></small>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-validation">
		<div class="field">
			<div class="ui checkbox toggle red">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][validation][required]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][validation][required]" value="true">
				<label><?php el('Required ?'); ?></label>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox toggle red">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][validation][optional]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][validation][optional]" value="true">
				<label><?php el('Optional'); ?></label>
				<small><?php el('If enabled then the validation will run if the field is NOT empty.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox toggle red">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][validation][disabled]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][validation][disabled]" value="true">
				<label><?php el('Disabled'); ?></label>
				<small><?php el('Keep the validation disabled, it can be enabled later using a field event.'); ?></small>
			</div>
		</div>
		<div class="field">
			<label><?php el('Error message'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][verror]">
			<small><?php el('The error message to be displayed when the field fails the validtaion test.'); ?></small>
		</div>
		<div class="field easy_disabled">
			<label><?php el('Validation rules'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][validation][rules]" rows="3"></textarea>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-info">
		<div class="field">
			<label><?php el('Description'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][description][text]" rows="3"></textarea>
		</div>
		
		<div class="field">
			<label><?php el('Tooltip text'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][tooltip][text]" rows="3"></textarea>
		</div>
		
		<div class="field easy_disabled">
			<label><?php el('Tooltip icon class'); ?></label>
			<input type="text" value="icon info circular blue inverted small" name="Connection[views][<?php echo $n; ?>][tooltip][class]">
		</div>
		
		<div class="ui header dividing small easy_disabled"><?php el('Load states'); ?></div>
		<div class="two fields easy_disabled">
			<div class="field">
				<label><?php el('Hidden'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][states][hidden]">
				<small><?php el('If not empty then the field will be hidden when the form is loaded.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Disabled'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][states][disabled]">
				<small><?php el('If not empty then the field will be disabled when the form is loaded.'); ?></small>
			</div>
			
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-advanced">
		<div class="inline fields">
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][ghost][enabled]" data-ghost="1" value="0">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[views][<?php echo $n; ?>][ghost][enabled]" value="1">
					<label><?php el('Enable ghost'); ?></label>
				</div>
			</div>
			<div class="field">
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][ghost][value]" placeholder="<?php el('Ghost value'); ?>">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Reload event'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][reload][event]">
				<small><?php el('The form event name used to reload this field when another field is set to reload it.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Extra attributes'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][attrs]" rows="3"></textarea>
		</div>

		<div class="two fields">
			<div class="field">
				<label><?php el('Container class'); ?></label>
				<input type="text" value="field" name="Connection[views][<?php echo $n; ?>][container][class]">
			</div>
			
			<div class="field">
				<label><?php el('Width'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][container][width]" class="ui fluid dropdown">
					<option value=""><?php el('Fluid'); ?></option>
					<option value="three wide">20%</option>
					<option value="four wide">25%</option>
					<option value="six wide">38%</option>
					<option value="eight wide">50%</option>
					<option value="twelve wide">75%</option>
				</select>
			</div>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][owner_id]">
				<small><?php el('The value of the owner id with which the owner permission will be checked.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Toggle switch'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][toggler]">
				<small><?php el('If provided and is an empty value then the view will not be rendered.'); ?></small>
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[views]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
	<button type="button" class="ui button compact red tiny close_config forms_conf"><?php el('Close'); ?></button>
</div>