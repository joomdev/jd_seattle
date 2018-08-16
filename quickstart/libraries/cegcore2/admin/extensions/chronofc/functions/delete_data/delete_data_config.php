<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$db_options = \G2\Globals::get('custom_db_options', []);
	if(!empty($function['db']['enabled'])){
		/*
		$db_options = $function['db'];
		$dbo = \G2\L\Database::getInstance($db_options, (\G2\L\System::pdo() ? 'pdo' : null));
		if(!empty($dbo->connected)){
			$db_tables = $dbo->getTablesList();
		}else{
			$db_tables = [rl('Database connection failed.')];
		}
		*/
		$dbo = \G2\L\Database::getInstance($function['db']);
		if(!empty($dbo->connected)){
			$db_tables = $dbo->getTablesList();
		}else{
			$db_tables = [rl('Database connection failed.')];
		}
	}else{
		$db_tables = \G2\L\Database::getInstance()->getTablesList();
	}
?>
<script>
	function delete_data_delete_model(){
		jQuery('.delete_data_delete_model').off('click').on('click', function(){
			var model_id = jQuery(this).closest('.segment.tab').attr('data-tab');
			jQuery('*[data-tab="'+model_id+'"]').prev().addClass('active');
			jQuery('*[data-tab="'+model_id+'"]').remove();
		});
	}
	
	function delete_data_add_model(add_link, n){
		var model_name = jQuery(add_link).parent().find('.model-name').val();
		var model_number = parseInt(jQuery(add_link).closest('.segment').find('.models-count').val()) + 1;
		
		var extra_data = [];
		extra_data['Connection[functions]['+n+'][models]['+model_number+'][model_name]'] = model_name;
		
		if(model_name.length > 0){
			var model_id = 'function-'+n+'-models-'+model_number;
			jQuery(add_link).closest('.segment').find('.models-menu').append(
				jQuery('<a class="item"></a>')
				.html(model_name)
				.attr('data-tab', model_id)
			);
			
			jQuery(add_link).closest('.segment').find('.tab.segment').addClass('loading');
				
			jQuery.ajax({
				url: "<?php echo r2('index.php?ext='.GApp::instance()->extension.'&cont=connections&act=function_config&tvout=view'); ?>",
				data: jQuery.extend({'type' : 'delete_data', 'id' : 'new_model', 'count' : n, 'params[]' : 'model_number', 'model_number' : model_number}, extra_data),
				success: function(result){
					jQuery(add_link).closest('.segment').find('.tab.segment').last().after(result);
					
					jQuery(add_link).closest('.segment').find('.tab.segment').removeClass('loading');
					
					jQuery('.ui.menu.G2-tabs .item').tab();
					jQuery('.ui.dropdown').dropdown({'forceSelection' : false});
					jQuery('.ui.checkbox').checkbox();
					delete_data_delete_model();
					
					jQuery('*[data-tab="'+model_id+'"]').parent().find('*[data-tab]').removeClass('active');
					jQuery('*[data-tab="'+model_id+'"]').addClass('active');
				}
			});
			
			jQuery(add_link).parent().find('.model-name').val('');
			jQuery(add_link).closest('.segment').find('.models-count').val(model_number);
		}
	}
	
	jQuery(document).ready(function($){
		delete_data_delete_model();
	});
</script>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-external"><?php el('External database'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="delete_data" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="field forms_conf">
			<label><?php el('Designer Label'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][label]">
		</div>
		
		<div class="ui action input fluid">
			<input type="text" placeholder="<?php el('New model name...'); ?>" class="model-name">
			<button type="button" class="ui button green compact" onclick="delete_data_add_model(this, <?php echo $n; ?>);"><?php el('Add new model'); ?></button>
		</div>
		
		<div class="ui pointing menu G2-tabs inverted models-menu">
			<a class="item active" data-tab="function-<?php echo $n; ?>-models-0"><?php echo !empty($function['model_name']) ? $function['model_name'] : rl('Primary'); ?></a>
			<?php if(!empty($function['models'])): ?>
				<?php foreach($function['models'] as $model_number => $model): ?>
					<a class="item" data-tab="function-<?php echo $n; ?>-models-<?php echo $model_number; ?>"><?php echo $model['model_name']; ?></a>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
		<input type="hidden" value="<?php echo empty($function['models']) ? 0 : max(array_keys($function['models'])); ?>" class="models-count">
		
		<div class="ui tab segment active" data-tab="function-<?php echo $n; ?>-models-0">
			<div class="two fields">
				<div class="field required">
					<label><?php el('Model name'); ?></label>
					<input type="text" value="Data<?php echo $n; ?>" name="Connection[functions][<?php echo $n; ?>][model_name]">
				</div>
				<div class="field required">
					<label><?php el('Database table'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][db_table]" class="ui fluid search selection dropdown">
						<option value=""><?php el('------Select table------'); ?></option>
						<?php foreach($db_tables as $table): ?>
						<option value="<?php echo $table; ?>"><?php echo $table; ?></option>
						<?php endforeach; ?>
					</select>
					<small><?php el('Select the table to delete the data from.'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Delete conditions'); ?></label>
				<textarea placeholder="<?php el('Multiline list or PHP code to return an array'); ?>" name="Connection[functions][<?php echo $n; ?>][where]" rows="8"></textarea>
			</div>
			
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][delete_protection]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[functions][<?php echo $n; ?>][delete_protection]" value="1">
					<label><?php el('Protect against full deletion.'); ?></label>
					<small><?php el('If disabled and empty conditions are passed, your whole table may be emptied.'); ?></small>
				</div>
			</div>
			
		</div>
		
		<?php if(!empty($function['models'])): ?>
			<?php foreach($function['models'] as $model_number => $model): ?>
				<?php $this->view(dirname(__FILE__).DS.'new_model.php', ['model_number' => $model_number, 'n' => $n, 'db_tables' => $db_tables]); ?>
			<?php endforeach; ?>
		<?php endif; ?>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-external">
		
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[functions][<?php echo $n; ?>][db][enabled]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][db][enabled]" value="1">
				<label><?php el('Enabled'); ?></label>
			</div>
		</div>
		
		<div class="ui message info"><?php el('The connection must be saved before the updated tables list is loaded. '); ?></div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('DB user name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][db][user]">
			</div>
			<div class="field">
				<label><?php el('DB user pass'); ?></label>
				<input type="password" value="" name="Connection[functions][<?php echo $n; ?>][db][pass]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('DB name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][db][name]">
			</div>
			<div class="field">
				<label><?php el('DB type'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][db][type]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('DB host'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][db][host]">
			</div>
			<div class="field">
				<label><?php el('DB prefix'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][db][prefix]">
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