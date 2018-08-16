<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php $fdata = !empty($fdata) ? $fdata : []; ?>
<?php if(!empty($title)): ?>
	<h3 class="ui header"><?php echo $title; ?></h3>
<?php endif; ?>
<table class="ui table">
	<thead>
		<tr>
			<?php foreach($columns as $column): ?>
				<?php list($text, $path, $output, $class) = $column; ?>
				<th class="<?php echo $class; ?>"><?php echo $text; ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($data as $k => $row): ?>
		<tr>
			<?php foreach($columns as $column): ?>
				<?php list($text, $path, $output, $class) = $column; ?>
				<?php if(is_callable($output)): ?>
					<td class="<?php echo $class; ?>"><?php echo call_user_func_array($output, array_merge([$row], $fdata)); ?></td>
				<?php else: ?>
					<td class="<?php echo $class; ?>"><?php echo \G2\L\Arr::getVal($row, $path); ?></td>
				<?php endif; ?>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>