<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$classes = [];
	if(!empty($size)){
		$classes[] = $size;
	}
	if(!empty($dynamic)){
		$classes[] = 'dynamic';
	}
	if(!empty($class)){
		$classes[] = $class;
	}else{
		$classes[] = 'segment';
	}
?>
<div class="ui notifier transition hidden <?php echo implode(' ', $classes); ?>" <?php if(!empty($id)): ?>id="<?php echo $id; ?>"<?php endif; ?>>
	<?php if(!empty($content)): ?><?php echo $content; ?><?php endif; ?>
</div>