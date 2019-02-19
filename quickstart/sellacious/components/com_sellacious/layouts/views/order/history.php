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
JHtml::_('stylesheet', 'com_sellacious/view.order.history.css', array('version' => S_VERSION_CORE, 'relative' => true));

$order = $this->item;
$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));
?>
<div class="jarviswidget">
	<header>
		<span class="widget-icon"> <i class="fa fa-check"></i> </span>
		<h2><?php echo $this->item->get('order_number'); ?></h2>
	</header>
	<div class="widget-body">
		<div class="col-sm-12">
			<div class="fieldset">

				<div class="pull-right">
					<h1 class="font-300"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_ORDER_LOG'); ?></h1>
				</div>
				<div class="clearfix"></div>
				<br>
				<div class="row">
					<div class="col-sm-4">
						<div id="address-viewer">
							<h5><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_BT'); ?></h5>
							<h4 class="semi-bold"><?php echo $order->get('bt_name') ?></h4>
							<address id="address-billing-text">
								<span class="address_address"><?php echo $order->get('bt_address') ?></span>,
								<span class="address_landmark"><?php echo $order->get('bt_landmark') ?></span><br/>
								<span class="address_district"><?php echo $order->get('bt_district') ?></span>,
								<span class="address_state_loc"><?php echo $order->get('bt_state') ?></span>,
								<span class="address_zip"><?php echo $order->get('bt_zip') ?></span> -
								<span class="address_country"><?php echo $order->get('bt_country') ?></span><br/>
									<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
										<?php echo $order->get('bt_mobile') ?></span><br/>
							</address>
						</div>
					</div>
					<div class="col-sm-1">&nbsp;</div>
					<?php if ($hasShippingAddress) : ?>
					<div class="col-sm-4">
						<div id="address-viewer">
							<h5><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_ST'); ?></h5>
							<h4 class="semi-bold"><?php echo $order->get('st_name') ?></h4>
							<address id="address-shipping-text">
								<span class="address_address"><?php echo $order->get('st_address') ?></span>,
								<span class="address_landmark"><?php echo $order->get('st_landmark') ?></span><br/>
								<span class="address_district"><?php echo $order->get('st_district') ?></span>,
								<span class="address_state_loc"><?php echo $order->get('st_state') ?></span>,
								<span class="address_zip"><?php echo $order->get('st_zip') ?></span> -
								<span class="address_country"><?php echo $order->get('st_country') ?></span><br/>
									<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
										<?php echo $order->get('st_mobile') ?></span><br/>
							</address>
						</div>
					</div>
					<?php endif; ?>
					<ul class="padding-10 pull-right" style="list-style: none">
						<li class="font-md nowrap">
							<strong><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_NUMBER'); ?></strong>
							<span class="pull-right"><strong><?php echo $order->get('order_number') ?></strong></span>
						</li>
						<li class="font-md nowrap">
							<strong><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DATE'); ?></strong>
							<span class="pull-right"><?php echo JHtml::_('date', $order->get('created'), 'D, F d, Y'); ?></span>
						</li>
					</ul>
				</div>
				<?php $order = $this->item; ?>
				<div class="table-responsive">
				<table class="w100p table-bordered table">
					<thead>
					<tr>
						<th><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_PRODUCT_NAME'); ?></th>
						<th><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_PRODUCT_SKU'); ?></th>
						<th><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_SELLER'); ?></th>
						<th style="width:180px;"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_STATUS'); ?></th>
						<th style="width:140px;"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DATE'); ?> </th>
						<th><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_INTERNAL_NOTE'); ?></th>
						<th><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_CUSTOMER_NOTE'); ?></th>
						<th><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DETAILS'); ?></th>
						<th style="width:180px;"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_UPDATED_BY'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php echo $this->loadTemplate('order'); ?>
					<?php echo $this->loadTemplate('items'); ?>
					</tbody>
				</table>
				</div>
			</div>
		</div>
	</div>
</div>


