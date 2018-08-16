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
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-external"><?php el('External database'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="save_data" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="field forms_conf easy_disabled">
			<label><?php el('Designer Label'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][label]">
		</div>
		
		<div class="ui tab segment active" data-tab="function-<?php echo $n; ?>-models-0">
			<div class="field forms_conf">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][enabled]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[functions][<?php echo $n; ?>][enabled]" value="1">
					<label><?php el('Enabled'); ?></label>
				</div>
			</div>
			
			<div class="field forms_conf">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][autotable]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][autotable]" value="1" <?php if($this->extension == 'chronoforms' && is_null($this->data('Connection.functions.'.$n.'.type'))): ?>checked<?php endif; ?>>
					<label><?php el('Auto manage the data table'); ?></label>
					<small><?php el('Synchronize the data table with the form fields automtaically, if no table is selected then a new one will be created.'); ?></small>
				</div>
			</div>
			
			<div class="two fields">
				<div class="field required easy_disabled">
					<label><?php el('Model name'); ?></label>
					<input type="text" value="Data<?php echo $n; ?>" name="Connection[functions][<?php echo $n; ?>][model_name]">
					<small><?php el('Fill in the model name if its empty.'); ?></small>
				</div>
				<div class="field required">
					<label><?php el('Database table'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][db_table]" class="ui fluid search selection dropdown">
						<option value=""><?php el('------Select table------'); ?></option>
						<?php foreach($db_tables as $table): ?>
						<option value="<?php echo $table; ?>"><?php echo $table; ?></option>
						<?php endforeach; ?>
					</select>
					<small><?php el('The database table where the data will be stored, tables can be created and updated in the forms manager.'); ?></small>
				</div>
			</div>
			
			<div class="two fields easy_disabled">
				<div class="field">
					<label><?php el('Data provider'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][data_provider]">
					<small><?php el('The data set to be used when saving, use {data:} for the full request data.'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Write action'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][action]" class="ui fluid dropdown">
						<option value="save"><?php el('Auto detect'); ?></option>
						<option value="insert"><?php el('Insert'); ?></option>
						<option value="update"><?php el('Update'); ?></option>
						<option value="insert:update"><?php el('Insert - duplicate key update'); ?></option>
						<option value="insert:ignore"><?php el('Insert - duplicate key ignore'); ?></option>
					</select>
					<small><?php el('Select the whether to insert or update or let it be decided based on the primary key value passed and the update conditions.'); ?></small>
				</div>
			</div>
			
			<div class="two fields easy_disabled">
				<div class="field">
					<label><?php el('Data override on Insert'); ?></label>
					<textarea name="Connection[functions][<?php echo $n; ?>][insert_data_override]" rows="5"><?php echo "created:{date:Y-m-d H:i:s}\nuser_id:{user:id}"; ?></textarea>
					<small><?php el('Multi line list of field:value to be added into the data set before an insert operation.'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Data override on Update'); ?></label>
					<textarea placeholder="<?php el('Multiline list of array fields'); ?>" name="Connection[functions][<?php echo $n; ?>][update_data_override]" rows="5"><?php echo "modified:{date:Y-m-d H:i:s}"; ?></textarea>
					<small><?php el('Multi line list of field:value to be added into the data set before an update operation.'); ?></small>
				</div>
			</div>
			
			<div class="field forms_conf">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][autofields]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][autofields]" value="1" <?php if($this->extension == 'chronoforms' && is_null($this->data('Connection.functions.'.$n.'.type'))): ?>checked<?php endif; ?>>
					<label><?php el('Auto save fields'); ?></label>
					<small><?php el('Auto include save enabled form fields in the data set to be saved.'); ?></small>
				</div>
			</div>
			
			<div class="field easy_disabled">
				<label><?php el('Update conditions'); ?></label>
				<textarea name="Connection[functions][<?php echo $n; ?>][where]" rows="5"></textarea>
				<small><?php el('Multi line list of field:value to be used as conditions for an update operation, example: field:1 to update rows with field = 1'); ?></small>
			</div>
			
			<div class="field easy_disabled">
				<label><?php el('Special fields'); ?></label>
				<textarea name="Connection[functions][<?php echo $n; ?>][fields][special]" rows="3"></textarea>
				<small><?php el('Multi line list of special fields, example: field/increment:1 to increment the value of some field, other features are decrement and json.'); ?></small>
			</div>
			
			<?php
				if(!empty($this->data('Connection.views'))){
					$fields = [];
					foreach($this->data('Connection.views') as $view){
						if(!empty($view['params']['name'])){
							$fname = rtrim(str_replace(['[]', '[', ']', '(N)'], ['(N)', '.', '', '.[n]'], $view['params']['name']), '.');
							$lname = explode('.', str_replace('.[n]', '', $fname));
							$fields[$view['name']] = array_pop($lname);
						}
					}
				}
			?>
			<input type="hidden" name="Connection[functions][<?php echo $n; ?>][_save]" value="1">
			<textarea name="Connection[functions][<?php echo $n; ?>][viewfields]" rows="3" data-ghost="1" class="hidden"><?php echo json_encode($fields); ?></textarea>
			
		</div>
		
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