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
foreach ($this->item->get('items') as $item)
{
	foreach ($item->history as $history)
	{
		$code = $this->helper->product->getCode($item->product_id, $item->variant_id, $item->seller_uid);
		$link = JRoute::_('../index.php?option=com_sellacious&view=product&p=' . $code);
		$tip  = $item->product_title . ' ' . $item->variant_title;
		?>
		<tr class="<?php echo $history->state ? 'strong' : '' ?>">
			<td class="nowrap">
			<?php if ($history->state == 1): ?>
				<a target="_blank" href="<?php echo $link ?>" class="hasTooltip" title="<?php echo $this->escape($tip); ?>">
					<?php echo substr($item->product_title, 0, 30) . '&hellip;' . $item->variant_title; ?>
				</a>
			<?php endif; ?>
			</td>
			<td class="nowrap">
			<?php
			if ($history->state == 1):
				echo $item->local_sku . '&hellip; ' . $item->variant_sku;
			endif;
			?>
			</td>
			<td>
			<?php if ($history->state == 1): ?>
				<?php echo $this->escape($item->seller_company) ?>
			<?php endif; ?>
			</td>
			<td><?php echo htmlspecialchars($history->s_title); ?></td>
			<td class="nowrap">
				<?php echo JHtml::_('date', $history->created, 'M d, Y'); ?>
				<small><?php echo JHtml::_('date', $history->created, 'H:i A'); ?></small>
			</td>
			<td><?php echo htmlspecialchars($history->notes); ?></td>
			<td><?php echo htmlspecialchars($history->customer_notes); ?></td>
			<td style="padding:0 4px 4px">
				<?php
				if (!empty($history->shipment))
				{
					?>
					<table class="table table-hover" style="width: 100%; margin-top: -1px">
						<?php
						foreach ($history->shipment as $key => $value)
						{
							$label = JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_' . strtoupper($key) . '_LBL');
							?>
							<tr>
								<th style="width:20%;" class="nowrap"><?php echo $label ?>:</th>
								<td><?php echo $value ?></td>
							</tr>
							<?php
						}
						?>
					</table>
					<?php
				}
				?>
			</td>
			<td>
				<?php
				if ($history->created_by == $this->item->get('customer_uid'))
				{
					echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_CUSTOMER');
				}
				elseif ($history->created_by == $item->seller_uid)
				{
					echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_SELLER');
				}
				else
				{
					$user = JFactory::getUser($history->created_by);

					// Todo: Check correct permission here!
					if ($user->authorise('config.edit'))
					{
						echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_ADMIN');
					}
					else
					{
						echo JText::sprintf('COM_SELLACIOUS_ORDER_USERTYPE_UNKNOWN', $user->get('name', 'N/A'));
					}
				}
				?></td>
		</tr>
		<?php
	}
}
