<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>

<form action="<?php echo r2('index.php?ext='.$this->extension.'&cont=connections'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">
	
	<a class="ui header" href="<?php echo r2('index.php?ext='.$this->extension.'&cont=tables&name='.$this->data('table_name')); ?>"><i class="icon left chevron"></i> <?php echo $this->data('table_name'); ?></a>
	
	<div class="ui clearing divider"></div>
	
	<?php
		$Model = new \G2\L\Model(['name' => 'Model', 'table' => $this->data('table_name')]);
		$prods = $Model->group([$this->data('field')])->fields(['Model.'.$this->data('field'), 'COUNT(*)' => $this->data('field').'_count'])->select();
		$prods = \G2\L\Arr::getVal($prods, '[n].Model', []);
		echo \G2\H\Chart::render($prods, ['x_field' => $this->data('field'), 'y_field' => $this->data('field').'_count', 'x_field_title' => rl('%s Values', [$this->data('field')]), 'y_field_title' => rl('Number of records')]);
	?>
	
</form>
