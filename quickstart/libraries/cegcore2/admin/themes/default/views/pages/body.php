<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php if(!empty($blocks)): ?>
	<?php foreach($blocks as $block): ?>
		<?php if(!empty($block['type']) AND empty($block['parent_id'])): ?>
			<?php echo $this->Parser2->parse($this->view('views.pages.block', ['block' => $block, 'blocks' => $blocks], true)); ?>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>