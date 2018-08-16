<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>

<form action="<?php echo r2('index.php?ext='.$this->extension.'&cont=connections'); ?>" method="post" name="admin_form" id="admin_form" class="ui form" style="overflow:auto;">
	
	<h2 class="ui header"><?php echo $this->data('name'); ?></h2>
	
	<a class="compact ui button icon labeled toolbar-button" href="<?php echo r2('index.php?ext='.$this->extension.'&cont=connections'); ?>">
		<i class="left arrow icon"></i><?php el('Cancel'); ?>
	</a>
	
	<a class="compact ui button blue icon labeled right floating" href="<?php echo r2('index.php?ext='.$this->extension.'&cont=tables&act=build&table_name='.$this->data('name')); ?>">
		<i class="write icon"></i><?php el('Modify table'); ?>
	</a>
	
	<button type="button" class="compact ui button red icon labeled toolbar-button" data-url="<?php echo r2('index.php?ext='.$this->extension.'&cont=tables&act=delete&table_name='.$this->data('name')); ?>">
		<i class="trash icon"></i><?php el('Delete'); ?>
	</button>
	
	<button type="button" class="compact ui button teal icon labeled toolbar-button" data-hint="<?php el('If no records are selected then the whole table will be exported.'); ?>" data-url="<?php echo r2('index.php?ext='.$this->extension.'&cont=tables&act=fullcsv&table_name='.$this->data('name')); ?>">
		<i class="download icon"></i><?php el('Export data'); ?>
	</button>
	
	<div class="ui clearing divider"></div>
	
	<?php echo $this->Paginator->navigation('Table'); ?>
	<?php echo $this->Paginator->limiter('Table'); ?>
	
	<table class="ui selectable table">
		<thead>
			<tr>
				<th class="collapsing">
					<div class="ui select_all checkbox">
						<input type="checkbox">
						<label></label>
					</div>
				</th>
				<th class="collapsing"><?php el('View'); ?></th>
				<?php foreach($fields as $field): ?>
				<th class="collapsing">
				<?php echo $this->Sorter->link($field, $field); ?>
				<a href="<?php echo r2('index.php?ext='.$this->extension.'&cont=tables&act=chart&table_name='.$this->data('name').rp('field', $field)); ?>" data-hint="<?php el('Display statistics'); ?>"><i class="icon bar chart green"></i></a>
				</th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach($rows as $i => $row): ?>
			<tr>
				<td class="collapsing">
					<div class="ui checkbox selector">
						<input type="checkbox" class="hidden" name="gcb[]" value="<?php echo $row['Table'][$pkey]; ?>">
						<label></label>
					</div>
				</td>
				<td class="collapsing"><a href="<?php echo r2('index.php?ext='.$this->extension.'&cont=tables&act=view&name='.$this->data('name').'&id='.$row['Table'][$pkey]); ?>"><?php el('View'); ?></a></td>
				<?php foreach($fields as $field): ?>
				<td class="collapsing"><?php echo isset($row['Table'][$field]) ? $row['Table'][$field] : 'NULL'; ?></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
</form>
