<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

JHtml::_('script', 'com_sellacious/view.productbuttons.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/plugin/clipboardjs/clipboard.min.js', array('version' => S_VERSION_CORE, 'relative' => true));

JText::script('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_BUY_NOW');
JText::script('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_ADD_TO_CART');

/** @var  SellaciousViewCoupons  $this */
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$me         = JFactory::getUser();
$g_currency = $this->helper->currency->getGlobal('code_3');

foreach ($this->items as $i => $item)
{
	$canDelete = $this->helper->access->check('product.delete', $item->id) ||
		($this->helper->access->check('product.delete.own') && $item->created_by == $me->id);
	$canEdit   = $this->helper->access->check('product.edit.basic') ||
		($this->helper->access->check('product.edit.basic.own') && $item->created_by == $me->id);

	$values = new Registry($item->params);
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canDelete) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>
		<td class="left">
			<?php if ($canEdit) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=productbutton.edit&id=' . $item->id); ?>">
					<?php echo $this->escape($item->title ?: JText::_('JTOOLBAR_EDIT')); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title ?: JText::_('JTOOLBAR_EDIT')); ?>
			<?php endif; ?>
		</td>
		<td>
			<?php echo $values->get('product_sku'); ?>
		</td>
		<td>
			<?php echo $values->get('product_title'); ?>
		</td>
		<td>
			<?php
			try
			{
				echo $this->helper->currency->display($values->get('flat_price'), $values->get('currency', $g_currency), null);
			}
			catch (Exception $e)
			{
				echo '<label class="fa fa-times label label-danger">&nbsp;</label>';
			}
			?>
		</td>
		<td class="center">
			<button type="button" class="btn btn-primary btn-xs btn-copy-buynow hasTooltip" data-id="<?php echo (int) $item->id; ?>"
					title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_BUY_NOW') ?>"> <i class="fa fa-bolt"></i> </button>
			<button type="button" class="btn btn-primary btn-xs btn-copy-addtocart hasTooltip"  data-id="<?php echo (int) $item->id; ?>"
					title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_ADD_TO_CART') ?>"> <i class="fa fa-shopping-cart"></i> </button>
			<script id="button-params-<?php echo (int) $item->id; ?>" type="text/button-params"><?php echo $item->params ?></script>
		</td>
		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
	<?php
}
