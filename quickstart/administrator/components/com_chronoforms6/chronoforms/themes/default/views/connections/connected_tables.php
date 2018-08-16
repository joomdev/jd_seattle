<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$tables = [];
	if(!empty($connection['Connection']['functions'])){
		foreach($connection['Connection']['functions'] as $function){
			if($function['type'] == 'save_data'){
				$tables[] = $function['db_table'];
			}
		}
	}
	$tables = array_filter($tables);
	$tables = array_unique($tables);
?>
<?php if(!empty($tables)): ?>
<div class="ui top right pointing dropdown icon">
	<input type="hidden" name="status">
	<i class="database icon"></i>
	<i class="dropdown icon"></i>
	<span class="text"></span>
	<div class="menu drop">
		<?php foreach($tables as $table): ?>
		<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=tables'.rp('name', $table)); ?>"><?php echo $table; ?></a>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>