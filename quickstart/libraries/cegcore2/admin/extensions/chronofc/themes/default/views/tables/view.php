<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>

<form action="<?php echo r2('index.php?ext='.$this->extension.'&cont=connections'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">
	
	<a class="ui header" href="<?php echo r2('index.php?ext='.$this->extension.'&cont=tables&name='.$this->data('name')); ?>"><i class="icon left chevron"></i> <?php echo $this->data('name'); ?></a>
	
	<div class="ui clearing divider"></div>
	
	<table class="ui very basic collapsing celled table">
		<thead>
			<tr>
				<th class="right aligned"><?php el('Name'); ?></th>
				<th><?php el('Value'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($fields as $i => $field): ?>
			<tr>
				<td class="right aligned"><?php echo $field; ?></td>
				<td><?php echo isset($row['Table'][$field]) ? $row['Table'][$field] : 'NULL'; ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
</form>
