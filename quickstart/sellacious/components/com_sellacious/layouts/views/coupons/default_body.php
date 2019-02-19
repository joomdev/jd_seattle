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
defined('_JEXEC') or die;

/** @var  SellaciousViewCoupons  $this */
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
$me        = JFactory::getUser();

foreach ($this->items as $i => $item)
{
	$s_currency = $this->helper->currency->forSeller($item->seller_uid, 'code_3');
	$canChange  = $this->helper->access->check('coupon.edit.state', $item->id);
	$canDelete  = $this->helper->access->check('coupon.delete', $item->id) ||
		($this->helper->access->check('coupon.delete.own') && $item->seller_uid == $me->id);
	$canEdit    = $this->helper->access->check('coupon.edit') ||
		($this->helper->access->check('coupon.edit.own') && $item->seller_uid == $me->id);
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange || $canDelete) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>
		<td class="nowrap center">
			<span class="btn-round"><?php
				echo JHtml::_('jgrid.published', $item->state, $i, 'coupons.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?></span>
		</td>
		<td class="left">
			<?php if ($canEdit) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=coupon.edit&id=' . $item->id); ?>">
					<?php echo $this->escape($item->title); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
			<span class="small" title="<?php echo $this->escape($item->alias); ?>">
			<?php if (empty($item->note)) : ?>
				<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
			<?php else : ?>
				<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
			<?php endif; ?>
			</span>
		</td>
		<td class="left">
			<?php echo $item->coupon_code; ?>
		</td>
		<td class="left">
			<?php echo number_format($item->per_user_limit); ?>
		</td>
		<td class="text-right">
			<?php echo number_format($item->total_limit); ?>
		</td>
		<td class="text-right">
			<?php
			if (substr($item->discount_amount, -1) == '%')
			{
				echo $item->discount_amount;
			}
			else
			{
				$amt_original  = $this->helper->currency->display($item->discount_amount, $s_currency, null);
				$amt_converted = $this->helper->currency->display($item->discount_amount, $s_currency, '');
				echo sprintf('%s<br/><small>%s</small>', $amt_original, $amt_converted);
			}
			?>
		</td>
		<td class=" text-right">
			<?php echo $this->helper->currency->display($item->min_purchase, $s_currency, null); ?><br />
			<small><?php echo $this->helper->currency->display($item->min_purchase, $s_currency, ''); ?></small>
		</td>
		<td class="text-right">
			<?php echo $this->helper->currency->display($item->max_discount, $s_currency, null); ?><br />
			<small><?php echo $this->helper->currency->display($item->max_discount, $s_currency, ''); ?></small>
		</td>
		<td class="text-right">
			<?php echo $this->helper->currency->display($item->max_discount_total, $s_currency, null); ?><br />
			<small><?php echo $this->helper->currency->display($item->max_discount_total, $s_currency, ''); ?></small>
		</td>
		<td class="center nowrap">
			<?php echo $item->publish_up == '0000-00-00 00:00:00' ? JText::_('JNONE') : JHtml::_('date', $item->publish_up, 'M d, Y') ?>
		</td>
		<td class="center nowrap">
			<?php echo $item->publish_down == '0000-00-00 00:00:00' ? JText::_('JNONE') : JHtml::_('date', $item->publish_down, 'M d, Y') ?>
		</td>
		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
	<?php
}
