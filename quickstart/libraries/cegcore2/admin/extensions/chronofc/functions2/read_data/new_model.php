<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(empty($db_tables)){
		$db_options = \G2\Globals::get('custom_db_options', []);
		$dbo = \G2\L\Database::getInstance($db_options);
		$db_tables = $dbo->getTablesList();
	}
?>
<div class="ui tab segment" data-tab="function-<?php echo $n; ?>-models-<?php echo $model_number; ?>">
	<div class="two fields">
		<div class="six wide field">
			<label><?php el('Model name'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][model_name]">
			<small><?php el('The other table model name'); ?></small>
		</div>
		<div class="ten wide field">
			<label><?php el('Database table'); ?></label>
			<select name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][db_table]" class="ui fluid dropdown">
				<option value=""><?php el('------Select table------'); ?></option>
				<?php foreach($db_tables as $table): ?>
				<option value="<?php echo $table; ?>"><?php echo $table; ?></option>
				<?php endforeach; ?>
			</select>
			<small><?php el('The table name used for the association.'); ?></small>
		</div>
	</div>
	
	<div class="two fields">
		<div class="six wide field">
			<label><?php el('Related to'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][related_to]">
			<small><?php el('The model to which this model will be related, this may be the main model or any other model in the relation.'); ?></small>
		</div>
		<div class="ten wide field">
			<label><?php el('Relation'); ?></label>
			<select name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][relation]" class="ui fluid dropdown">
				<option value=""><?php el('------Select relation------'); ?></option>
				<option value="hasOne"><?php el('One matching record, foreign key in this table.'); ?></option>
				<option value="belongsTo"><?php el('One matching record, foreign key at the related table.'); ?></option>
				<option value="hasMany"><?php el('Multiple matching records, foreign key in this table.'); ?></option>
				<option value="subqueryJoin"><?php el('SubQuery Join, One matching record.'); ?></option>
			</select>
			<small><?php el('The relation between the two models/tables.'); ?></small>
		</div>
	</div>
	
	<div class="two fields">
		<div class="six wide field">
			<label><?php el('Foreign key'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][foreign_key]">
			<small><?php el('The foreign key field name.'); ?></small>
		</div>
		<div class="ten wide field">
			<label><?php el('Relation conditions'); ?></label>
			<textarea placeholder="<?php el('Multiline list or PHP code to return an array'); ?>" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][relation_conditions]" rows="4"></textarea>
			<small><?php el('The relation conditions in case it can not be setup by a single foreign key field, example:Model1.field:Model2.field'); ?></small>
		</div>
	</div>
	
	<div class="ui red button read_data_delete_model"><?php el('Delete'); ?></div>
	
	<div class="ui header dividing"><?php el('Advanced Multiple results settings'); ?></div>
	
	<div class="field">
		<label><?php el('Fields to retrieve'); ?></label>
		<textarea placeholder="<?php el('Multiline list or PHP code to return an array'); ?>" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][fields][list]" rows="4"></textarea>
		<small><?php el('Multi line list of fields to retrieve from this table.'); ?></small>
	</div>
	
	<div class="field">
		<div class="ui checkbox">
			<input type="hidden" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][fields][extra]" data-ghost="1" value="">
			<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][fields][extra]" value="1">
			<label><?php el('This is an extra fields list'); ?></label>
		</div>
	</div>

	<div class="field">
		<label><?php el('Order fields'); ?></label>
		<textarea placeholder="<?php el('Multiline list or PHP code to return an array'); ?>" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][order]" rows="3"></textarea>
		<small><?php el('example: field_name/asc or field_name/desc'); ?></small>
	</div>
	
	<div class="field">
		<label><?php el('Group conditions'); ?></label>
		<textarea placeholder="<?php el('Multiline list or PHP code to return an array'); ?>" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][group]" rows="2"></textarea>
		<small><?php el('Multi line list of fields to be used for grouping results, this is only used if this model returns multiple results.'); ?></small>
	</div>
	
</div>