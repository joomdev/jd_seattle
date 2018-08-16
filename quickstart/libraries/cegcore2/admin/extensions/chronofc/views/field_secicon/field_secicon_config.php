<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab views-tab active" data-tab="view-<?php echo $n; ?>">
	
	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="view-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-validation"><?php el('Validation'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-advanced"><?php el('Advanced'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="view-<?php echo $n; ?>-general">
		<input type="hidden" value="field_secicon" name="Connection[views][<?php echo $n; ?>][type]">
		
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
				<input type="text" value="Select %s" name="Connection[views][<?php echo $n; ?>][label]">
			</div>
			<div class="four wide field">
				<label><?php el('Layout'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][layout]" class="ui fluid dropdown">
					<option value="grouped"><?php el('Vertical'); ?></option>
					<option value="inline"><?php el('Horizontal'); ?></option>
				</select>
			</div>
		</div>

		<div class="two fields">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="secicon<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][params][name]">
				<small><?php el('This name must be placed in the Check Security image action settings.'); ?></small>
				<small><?php el('No spaces or special characters should be used here.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('ID'); ?></label>
				<input type="text" value="secicon<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][params][id]">
			</div>
		</div>

		<div class="two fields">
			<div class="ten wide field">
				<label><?php el('Options'); ?></label>
				<?php
					$list = [
					'moon:the moon', 
					'flag:the flag', 
					'soccer:the football', 
					'travel:the suitcase', 
					'plane:the plane', 
					'book:the book', 
					'diamond:the diamond', 
					'umbrella:the umbrella', 
					'star:the star', 
					'heart:the heart',
					'cloud:the cloud',
					'write:the pencil',
					'call:the phone',
					];
				?>
				<textarea name="Connection[views][<?php echo $n; ?>][options]" rows="7"><?php echo implode("\n", $list); ?></textarea>
			</div>
			
			<div class="field">
				<label><?php el('Number of options'); ?></label>
				<input type="text" value="5" name="Connection[views][<?php echo $n; ?>][number]">
			</div>
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
			<label><?php el('Error message'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][verror]">
			<small><?php el('The error message to be displayed when the field fails the validtaion test.'); ?></small>
		</div>
		<div class="field easy_disabled">
			<label><?php el('Validation rules'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][validation][rules]" rows="3"></textarea>
		</div>
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-advanced">
		<div class="fields inline">
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

		<div class="field">
			<label><?php el('Extra attributes'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][attrs]" rows="3"></textarea>
		</div>
		
		<div class="field">
			<label><?php el('Description'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][description][text]" rows="3"></textarea>
		</div>

		<div class="two fields">
			<div class="field">
				<label><?php el('Container class'); ?></label>
				<input type="text" value="field" name="Connection[views][<?php echo $n; ?>][container][class]">
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