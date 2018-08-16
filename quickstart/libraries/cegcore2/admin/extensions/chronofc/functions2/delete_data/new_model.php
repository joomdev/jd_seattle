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
		</div>
		<div class="ten wide field">
			<label><?php el('Database table'); ?></label>
			<select name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][db_table]" class="ui fluid dropdown">
				<option value=""><?php el('------Select table------'); ?></option>
				<?php foreach($db_tables as $table): ?>
				<option value="<?php echo $table; ?>"><?php echo $table; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	
	<div class="two fields">
		<div class="six wide field">
			<label><?php el('Related to'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][related_to]">
		</div>
	</div>
	
	<div class="two fields">
		<div class="six wide field">
			<label><?php el('Foreign key'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][foreign_key]">
		</div>
		<div class="ten wide field">
			<label><?php el('Relation conditions'); ?></label>
			<textarea placeholder="<?php el('Multiline list or PHP code to return an array'); ?>" name="Connection[functions][<?php echo $n; ?>][models][<?php echo $model_number; ?>][relation_conditions]" rows="4"></textarea>
		</div>
	</div>
	
	<div class="ui red button delete_data_delete_model"><?php el('Delete'); ?></div>
	
</div>