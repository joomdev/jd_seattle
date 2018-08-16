<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php if(!empty($element['params']['values']) AND !empty($element['params']['provider'])): ?>
	<?php $subs = \G2\L\Arr::searchVal($elements, ['[n]', 'parent_id'], [$element['id']]); ?>
	<?php foreach($element['params']['values'] as $i => $value): ?>
		<?php $provider = $this->Parser2->parse($element['params']['provider']); ?>
		<?php if($provider == $value): ?>
			<?php foreach($subs as $sub): ?>
				<?php if(($sub['parent_id'] == $element['id']) AND ($sub['parent_sub_id'] == $i)): ?>
					<?php $this->view('views.pages.element', ['element' => $sub, 'elements' => $elements]); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>