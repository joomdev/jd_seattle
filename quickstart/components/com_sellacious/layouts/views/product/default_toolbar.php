<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewProduct  $this */
$user = JFactory::getUser();
$code = $this->state->get('product.code');

$page_id = $this->getLayout() == 'modal' ? 'product_modal' : 'product';
?>
<div class="product-toolbar">
	<?php if ($this->helper->config->get('product_compare') && in_array($page_id, (array) $this->helper->config->get('product_compare_display'))): ?>
	<button type="button" class="btn-compare" data-item="<?php echo $code ?>">
		<i class="fa fa-copy"></i><span class="add-compare"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_COMPARE'); ?></span>
		<span class="remove-compare"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_REMOVE_FROM_COMPARE'); ?></span></button>
	<?php endif; ?>

	<?php
	if ($this->helper->config->get('product_wishlist')):
		if ($user->guest):
			$url   = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code, false);
			$login = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url), false); ?>
			<button type="button" class="btn-wishlist" data-guest="true" data-href="<?php echo $this->escape($login) ?>">
				<i class="fa fa-heart-o"></i><span><?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_WISHLIST'); ?></span>
			</button><?php
		elseif ($this->helper->wishlist->check($code, null)):
			$url = JRoute::_('index.php?option=com_sellacious&view=wishlist', false); ?>
			<button type="button" class="btn-wishlist" data-href="<?php echo $this->escape($url) ?>">
				<i class="fa fa-heart"></i><span><?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADDED_TO_WISHLIST'); ?></span>
			</button><?php
		else: ?>
			<button type="button" class="btn-wishlist" data-item="<?php echo $code ?>">
				<i class="fa fa-heart-o"></i><span><?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_WISHLIST'); ?></span>
			</button><?php
		endif;
	endif; ?>
	<?php if ($this->getReviewForm()):
		if ($page_id == 'product_modal'): ?>
			<button type="button" class="btn-review" onclick="location.href='<?php echo JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code . '#reviewBox'); ?>'"
					data-item="<?php echo $code ?>"><i class="fa fa-edit"></i><span><?php echo JText::_('COM_SELLACIOUS_WRITE_A_REVIEW'); ?></span></button>
		<?php else: ?>
			<button type="button" class="btn-review" data-item="<?php echo $code ?>">
				<i class="fa fa-edit"></i><span><?php echo JText::_('COM_SELLACIOUS_WRITE_A_REVIEW'); ?></span></button>
		<?php endif; ?>
	<?php endif; ?>
</div>
<div class="clearfix"></div>
