<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php foreach($items as $item_n => $item): ?>
	<?php if(!isset($item['category']) OR $item['category'] == $category): ?>
	<a class="blue item" data-tab="<?php echo $list_name; ?>-<?php echo $item_n; ?>">
		<i class="icon delete fitted red delete_block" data-hint="<?php echo rl('Delete'); ?>"></i>
		<div class="ui <?php echo $info[$item['type']]['color']; ?> label small"><?php echo $info[$item['type']]['title']; ?></div>
		<?php echo $item['name']; ?>
		<i class="icon <?php echo !empty($info[$item['type']]['icon']) ? $info[$item['type']]['icon'] : 'display'; ?> fitted"></i>
	</a>
	<?php endif; ?>
<?php endforeach; ?>