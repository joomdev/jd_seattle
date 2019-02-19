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

/** @var  $this  SellaciousViewOrder */
$item = $this->item;

foreach ($item->get('history') as $history)
{
	$code = $this->helper->product->getCode($item->get('product_id'), $item->get('variant_id'), $item->get('seller_uid'));
	$link = JRoute::_('../index.php?option=com_sellacious&view=order&p=' . $code);
	?>
	<tr class="<?php echo $history->state ? 'strong' : '' ?>">
		<td class="nowrap">
			<?php if ($history->state == 1): ?>
			<?php echo JText::_('COM_SELLACIOUS_HISTORY_ORDER'); ?><a target="_blank" href="<?php echo $link ?>" class="hasTooltip"
			   title="<?php echo $this->escape($item->get('order_number')); ?>"><?php echo $item->get('order_number'); ?>
			</a>
			<?php endif; ?>
		</td>
		<td class="nowrap"></td>
		<td></td>
		<td><?php echo htmlspecialchars($history->s_title); ?></td>
		<td class="nowrap">
			<?php echo JHtml::_('date', $history->created, 'M d, Y'); ?>
			<small><?php echo JHtml::_('date', $history->created, 'H:i A'); ?></small>
		</td>
		<td><?php echo htmlspecialchars($history->notes); ?></td>
		<td><?php echo htmlspecialchars($history->customer_notes); ?></td>
		<td></td>
		<td>
			<?php
			if ($history->created_by == $this->item->get('customer_uid'))
			{
				echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_CUSTOMER');
			}
			elseif (JFactory::getUser($history->created_by)->authorise('config.edit', 'com_sellacious'))
			{
				echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_ADMIN');
			}
			else
			{
				// echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_SELLER');
			}
			?>
		</td>
	</tr>
	<?php
}

