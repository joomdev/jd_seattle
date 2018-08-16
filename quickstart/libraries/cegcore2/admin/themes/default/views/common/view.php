<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php $fdata = !empty($fdata) ? $fdata : []; ?>
<?php if(!empty($title)): ?>
	<h3 class="ui header"><?php echo $title; ?></h3>
<?php endif; ?>
<table class="ui definition table">
	<tbody>
		<?php foreach($columns as $column): ?>
		<?php list($text, $path, $output, $class) = $column; ?>
		<tr>
			<td class="collapsing"><?php echo $text; ?></td>
			<?php if(is_callable($output)): ?>
				<td class="<?php echo $class; ?>"><?php echo call_user_func_array($output, array_merge([$data], $fdata)); ?></td>
			<?php else: ?>
				<td class="<?php echo $class; ?>"><?php echo \G2\L\Arr::getVal($data, $path); ?></td>
			<?php endif; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>