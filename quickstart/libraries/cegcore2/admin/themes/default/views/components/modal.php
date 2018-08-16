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
	if(!empty($source)){
		$classes[] = 'source';
	}
?>
<div class="ui modal <?php echo implode(' ', $classes); ?>" <?php if(!empty($id)): ?>id="<?php echo $id; ?>"<?php endif; ?>>
	<i class="close icon"></i>
	<?php if(!empty($header)): ?>
	<div class="ui header centered">
		<?php echo $header; ?>
	</div>
	<?php endif; ?>
	<div class="<?php if(!empty($scrolling)): ?>scrolling <?php endif; ?>content" <?php if(!empty($content_id)): ?>id="<?php echo $content_id; ?>"<?php endif; ?>>
		<?php if(!empty($content)): ?><?php echo $content; ?><?php endif; ?>
	</div>
	
	<?php if(!empty($source)): ?>
	<div class="source" style="display:none;">
		<?php if(!empty($content)): ?><?php echo $content; ?><?php endif; ?>
	</div>
	<?php endif; ?>
</div>