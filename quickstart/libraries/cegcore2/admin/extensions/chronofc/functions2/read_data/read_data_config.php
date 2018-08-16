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
	function read_data_delete_model(){
		jQuery('.read_data_delete_model').off('click').on('click', function(){
			var model_id = jQuery(this).closest('.segment.tab').attr('data-tab');
			jQuery('*[data-tab="'+model_id+'"]').prev().addClass('active');
			jQuery('*[data-tab="'+model_id+'"]').remove();
		});
	}
	
	function read_data_add_model(add_link, n){
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
				data: jQuery.extend({'type' : 'read_data', 'id' : 'new_model', 'count' : n, 'params[]' : 'model_number', 'model_number' : model_number}, extra_data),
				success: function(result){
					jQuery(add_link).closest('.segment').find('.tab.segment').last().after(result);
					
					jQuery(add_link).closest('.segment').find('.tab.segment').removeClass('loading');
					
					jQuery('.ui.menu.G2-tabs .item').tab();
					jQuery('.ui.dropdown').dropdown({'forceSelection' : false});
					jQuery('.ui.checkbox').checkbox();
					read_data_delete_model();
					
					jQuery('*[data-tab="'+model_id+'"]').parent().find('*[data-tab]').removeClass('active');
					jQuery('*[data-tab="'+model_id+'"]').addClass('active');
				}
			});
			
			jQuery(add_link).parent().find('.model-name').val('');
			jQuery(add_link).closest('.segment').find('.models-count').val(model_number);
		}
	}
	
	jQuery(document).ready(function($){
		read_data_delete_model();
	});
</script>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-events"><?php el('Events'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-external"><?php el('External database'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="read_data" name="Connection[functions][<?php echo $n; ?>][type]">
		
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
			<button type="button" class="ui button green compact" onclick="read_data_add_model(this, <?php echo $n; ?>);"><?php el('Add new model'); ?></button>
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
					<small><?php el('Which database table should be used to read the data ?'); ?></small>
				</div>
			</div>
			
			<div class="ui header dividing"><?php el('Filtering settings'); ?></div>
			
			<div class="field">
				<label><?php el('Where conditions'); ?></label>
				<textarea placeholder="<?php el('Multiline list or PHP code to return an array'); ?>" name="Connection[functions][<?php echo $n; ?>][where]" rows="8"></textarea>
				<small><?php el('Multi line list of key:value pairs, the value can be a data call like {data:field_name} to capture a request variable.'); ?></small>
			</div>
			
			<div class="ui header dividing advanced_conf"><?php el('Sorting settings'); ?></div>
			
			<div class="field advanced_conf">
				<label><?php el('Sortable fields'); ?></label>
				<textarea placeholder="<?php el('Multi line list of fields'); ?>" name="Connection[functions][<?php echo $n; ?>][sort][fields]" rows="4"></textarea>
			</div>
			
			<div class="field">
				<label><?php el('Order fields'); ?></label>
				<textarea placeholder="<?php el('Multiline list or PHP code to return an array'); ?>" name="Connection[functions][<?php echo $n; ?>][order]" rows="4"></textarea>
				<small><?php el('example: field_name/asc or field_name/desc'); ?></small>
			</div>
			
			<div class="ui header dividing"><?php el('Data settings'); ?></div>
			
			<div class="field">
				<label><?php el('Select type'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][select_type]" class="ui fluid dropdown">
					<option value="all"><?php el('All matching records.'); ?></option>
					<option value="first"><?php el('First matching record.'); ?></option>
					<option value="count"><?php el('Return the count of records matching the filtering conditions.'); ?></option>
					<option value="list"><?php el('Return an array of key/value pairs, two fields must be provided.'); ?></option>
					<option value="indexed"><?php el('Index the results list by one or more fields values.'); ?></option>
				</select>
				<small><?php el('Return multiple or single records, choose the key/value pairs option for dynamic dropdown options sets.'); ?></small>
			</div>
			<div class="three fields">
				<div class="field">
					<label><?php el('Paging for multiple results'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][paging]" class="ui fluid dropdown">
						<option value="1"><?php el('Enabled'); ?></option>
						<option selected="selected" value="0"><?php el('Disabled'); ?></option>
					</select>
					<small><?php el('Disable paging if the whole query data should be returned.'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Page limit'); ?></label>
					<input type="text" value="100" name="Connection[functions][<?php echo $n; ?>][limit]">
					<small><?php el('How many records to read ?'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Offset'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][offset]">
					<small><?php el('The offset used for reading records'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Fields to retrieve'); ?></label>
				<textarea name="Connection[functions][<?php echo $n; ?>][fields][list]" rows="7"></textarea>
				<small><?php el('Multi line list of fields to be retrieved, this may be default data table fields or functions, example: Model.field1 or COUNT(Model.id):Model.count_alias'); ?></small>
			</div>
			
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][fields][extra]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][fields][extra]" value="1">
					<label><?php el('This is an extra fields list'); ?></label>
					<small><?php el('If enabled then the list above will be considered an extra list of fields beside the full list of table fields.'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Group fields'); ?></label>
				<textarea name="Connection[functions][<?php echo $n; ?>][group]" rows="4"></textarea>
				<small><?php el('Multi line list of fields to be used for grouping the results.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Special fields'); ?></label>
				<textarea placeholder="<?php el('Multiline list'); ?>" name="Connection[functions][<?php echo $n; ?>][fields][special]" rows="3"></textarea>
			</div>
			
			<div class="ui header dividing advanced_conf"><?php el('Search settings'); ?></div>
			
			<div class="two fields advanced_conf">
				<div class="field">
					<label><?php el('Search parameter name'); ?></label>
					<input type="text" value="keywords" name="Connection[functions][<?php echo $n; ?>][search][param_name]">
					<small><?php el('The request parameter name used for searching the table.'); ?></small>
				</div>
			</div>
			<div class="field advanced_conf">
				<label><?php el('Searchable fields'); ?></label>
				<textarea name="Connection[functions][<?php echo $n; ?>][search][fields]" rows="4"></textarea>
				<small><?php el('Multi line list of fields to be searched when the search parameter value is passed in the request.'); ?></small>
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
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-events">
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" <?php if(!empty($this->data('Connection.functions.'.$n.'._event'))): ?>checked="checked"<?php endif; ?> class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="found" data-event_switcher="1">
				<label><?php el('Enable the record found event'); ?></label>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" checked="checked" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="notfound" data-event_switcher="1">
				<label><?php el('Enable the record not found event'); ?></label>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" <?php if(!empty($this->data('Connection.functions.'.$n.'._event'))): ?>checked="checked"<?php endif; ?> class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="fail" data-event_switcher="1">
				<label><?php el('Enable the read failed event'); ?></label>
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