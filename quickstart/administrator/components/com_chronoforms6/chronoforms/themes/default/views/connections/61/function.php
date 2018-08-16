<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$path = \G2\Globals::ext_path('chronofc', 'admin').'functions2'.DS.$function['type'].DS.$function['type'].'_config.php';
	$ini_path = \G2\Globals::ext_path('chronofc', 'admin').'functions2'.DS.$function['type'].DS.$function['type'].'.ini';
	$info = parse_ini_file($ini_path);
	
	if(!empty($function['events'])){
		$fnevents = array_fill_keys(array_map('trim', explode(',', $function['events'])), array_values($info['events'])[0]);
	}else if(!empty($info['events'])){
		$fnevents = $info['events'];
	}else{
		$fnevents = [];
	}
?>
<div class="ui segment blue element-area" data-id="<?php echo $n; ?>">
	<?php $this->view('views.connections.61.icons'); ?>
	<div class="ui header small" style="color:black;">
		<i class="icon <?php echo $info['icon']; ?>"></i>
		<div class="content">
			<?php echo $info['title']; ?> : <?php echo $function['name']; ?>
		<div class="sub header"><?php echo !empty($function['title']) ? $function['title'] : ''; ?></div>
		</div>
	</div>
	<!--
	<input type="hidden" name="Page[<?php echo $pn; ?>][Actions][<?php echo $n; ?>][page_id]" value="" class="page_id" />
	-->
	<input type="hidden" name="Page[<?php echo $pn; ?>][Actions][<?php echo $n; ?>][parent_id]" value="" class="parent_id" />
	<input type="hidden" name="Page[<?php echo $pn; ?>][Actions][<?php echo $n; ?>][sub_parent_id]" value="" class="sub_parent_id" />
	
	<?php //$this->view('views.components.modal', ['id' => $function['name'], 'header' => $function['name'], 'scrolling' => true, 'size' => 'large', 'content' => $this->view($path, ['function' => $function, 'n' => $n], true)]); ?>
	<?php
		//$this->data['Connection']['
		$output = $this->view($path, ['function' => $function, 'n' => $n], true);
		$output = str_replace('ui segment tab functions-tab', 'ui message transition hidden settings functions-tab', $output);
		$output = str_replace('Connection[functions]', 'Page['.$pn.'][Actions]', $output);
		//$output = str_replace('Action', 'Page['.$pn.'][Actions]', $output);
		echo $output;
	?>
	
	<?php if(!empty($fnevents)): ?>
		<div class="ui divider"></div>
	<?php endif; ?>
	
	<?php foreach($fnevents as $ename => $ecolor): ?>
		<div class="ui <?php echo $ecolor; ?> label">
			<?php echo $ename; ?>
		</div>
		<div class="ui message <?php echo $ecolor; ?> droppable echild-area" data-id="<?php echo $ename; ?>" style="margin-top:2px; min-height:50px;">
			<?php $this->view('views.connections.61.functions', ['pn' => $pn, 'page' => $page, 'parent_id' => $n, 'section' => $ename]); ?>
		</div>
	<?php endforeach; ?>
</div>