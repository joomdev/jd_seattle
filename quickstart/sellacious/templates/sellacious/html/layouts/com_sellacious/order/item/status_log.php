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

use Joomla\Utilities\ArrayHelper;

/** @var  stdClass[] $displayData */
$data = $displayData;

$records = ArrayHelper::getValue($displayData, 'log');
$order   = (object) ArrayHelper::getValue($displayData, 'order');
$item    = (object) ArrayHelper::getValue($displayData, 'item');
?>
<table class="w100p table-bordered table">
	<thead>
	<tr>
		<th style="width:10%;">Date        	 </th>
		<th style="width:10%;">Status      	 </th>
		<th style="width:20%;">Customer Note </th>
		<th style="width:20%;">Note          </th>
		<th style="width:30%;">Details       </th>
		<th style="width:10%;">Updated By    </th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($records as $record)
	{
		?>
		<tr>
			<td>
				<?php echo JHtml::_('date', $record->created, 'F d, Y'); ?><br>
				<?php echo JHtml::_('date', $record->created, 'H:i A'); ?>
			</td>
			<td><?php echo htmlspecialchars($record->s_title); ?></td>
			<td><?php echo htmlspecialchars($record->customer_notes); ?></td>
			<td><?php echo htmlspecialchars($record->notes); ?></td>
			<td style="padding: 0; border: 0;">
				<table class="table table-bordered table-hover" style="margin: -1px; width: calc(100%+2px);">
				<?php
				if (!empty($record->shipment))
				{
					$info = array();

					foreach ($record->shipment as $key => $value)
					{
						$label  = JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_' . strtoupper($key) . '_LBL');
						$info[] = sprintf('<tr><th style="width:20%%;" class="nowrap">%s:</th><td>%s</td></tr>', $label, htmlspecialchars($value));
					}

					echo implode($info);
				}
				?>
				</table>
			</td>
			<td>
			<?php
			if ($record->created_by == $order->customer_uid)
			{
				echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_CUSTOMER');
			}
			elseif ($record->created_by == $item->seller_uid)
			{
				echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_SELLER');
			}
			else
			{
				$user = JFactory::getUser($record->created_by);

				// Todo: Check correct permission here!
				if ($user->authorise('config.edit'))
				{
					echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_ADMIN');

					echo ' <small class="red debug">(core.admin?)</small>';
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
	?>
	</tbody>
</table>
