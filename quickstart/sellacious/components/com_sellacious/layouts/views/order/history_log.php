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

/** @var  stdClass  $tplData */
$log     = $tplData;
$records = $log->items;

/** @var  $this  SellaciousViewOrder */
?>
<!-- Widget ID (each widget will need unique ID)-->
<div class="jarviswidget">
	<header>
		<span class="widget-icon"> <i class="fa fa-check"></i> </span>
		<h2><?php echo $log->title ? $log->title : $this->item->get('order_number'); ?></h2>
	</header>
	<div class="widget-body">
		<div class="col-sm-12">
			<div class="tab-content">
				<div class="tab-pane active">
					<div class="fieldset">
						<?php $order = $this->item; ?>
						<table class="w100p table-bordered table">
							<thead>
							<tr>
								<th style="width:10%;"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DATE'); ?> </th>
								<th style="width:10%;"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_STATUS'); ?></th>
								<th style="width:20%;"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_CUSTOMER_NOTE'); ?></th>
								<th style="width:20%;"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_NOTE'); ?></th>
								<th style="width:30%;"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DETAILS'); ?></th>
								<th style="width:10%;"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_UPDATED_BY'); ?></th>
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
										<table class="table table-bordered table-hover" style="margin: -1px; width: 100%;">
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
										if ($record->created_by == $order->get('customer_uid'))
										{
											echo JText::_('COM_SELLACIOUS_ORDER_USERTYPE_CUSTOMER');
										}
										elseif ($record->created_by == @$item->seller_uid)
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
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- end widget -->
