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

use Joomla\Registry\Registry;

/** @var  SellaciousViewTransaction $this */

JHtml::_('stylesheet', 'com_sellacious/component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.order.receipt.css', array('version' => S_VERSION_CORE, 'relative' => true));

$app  = JFactory::getApplication();
$item = new Registry($this->item);

if (!$item->get('id'))
{
	echo '<h3>' . JText::_('COM_SELLACIOUS_ORDER_RECEIPT_NOT_FOUND') . '</h3>';

	return;
}
?>
<div class="clearfix"></div>
<div style="min-height: 1000px;">
	<?php if ($app->input->get('tmpl') == 'component'): ?>
	<script>
		jQuery(function($) {
			$(document).ready(function () {
				window.print();
			});
		});
	</script>
	<?php else: ?>
	<div id="receipt-head" class="text-right">
		<?php $print = JRoute::_('index.php?option=com_sellacious&view=order&layout=receipt&tmpl=component&id=' . $item->get('id')); ?>
		<a class="btn btn-sm btn-primary" target="_blank" href="<?php echo $print ?>"><i class="fa fa-print"></i> <?php echo JText::_('COM_SELLACIOUS_ORDER_PRINT'); ?></a>
	</div>
	<?php endif; ?>

	<div class="clearfix hidden-lg"></div>
	<div id="receipt-page">
		<div id="receipt-logo"><img src="<?php echo $this->helper->media->getImage('config.shop_logo', 1) ?>"/></div>
		<div class="title text-center"><?php echo JText::_('COM_SELLACIOUS_ORDER_RECEIPT'); ?></div>
		<div class="title text-center"><?php echo JText::_('COM_SELLACIOUS_ORDER_ORDER'); ?><?php echo $item->get('order_number') ?></div>
		<br>
		<div class="sub-title text-center"><?php echo JText::_('COM_SELLACIOUS_ORDER_FOR'); ?><strong><?php echo $item->get('customer_name'); ?></strong></div>
		<div class="datetime text-center"><?php echo JHtml::_('date', $item->get('created'), 'F d, Y h:i A T') ?></div>
		<br>

		<div class="address text-center">
			<?php
			$shop    = $this->helper->config->get('shop_name');
			$address = $this->helper->config->get('shop_address');
			$country = $this->helper->config->get('shop_country');
			$phone1  = $this->helper->config->get('shop_phone1');
			$phone2  = $this->helper->config->get('shop_phone2');
			$email   = $this->helper->config->get('shop_email');
			$website = $this->helper->config->get('shop_website');

			?><div class="company"><?php echo $shop ?></div><?php

			echo nl2br($address) . ', ' . $this->helper->location->loadResult(array('list.select' => 'a.title', 'id' => $country)); ?><br><?php

			if ($phone1)
			{
				?><i class="fa fa-phone"></i> <?php echo $phone1;
			}

			if ($phone2)
			{
				?>&nbsp;&nbsp;<i class="fa fa-mobile-phone"></i> <?php echo $phone2;
			}

			if ($email)
			{
				?><br><i class="fa fa-envelope-o"></i> <?php echo $email;
			}

			if ($website)
			{
				?><br><i class="fa fa-globe"></i> <?php echo $website;
			}
			?>
		</div>

		<div class="txn-amount text-center">
			<span><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_GRAND_TOTAL') ?></span>:
			<?php echo $this->helper->currency->display($item->get('grand_total'), $item->get('currency'), null); ?>
		</div>
		<div class="sub-title text-center">
			<?php if ($item->get('payment.fee_amount') >= 0.01): ?>
				<span><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_FEE_METHOD', $item->get('payment.method_name')); ?></span>
				<strong><?php echo $this->helper->currency->display($item->get('payment.fee_amount'), $item->get('currency'), null, false) ?></strong>
			<?php endif; ?>
			<br>
		</div>
		<br>

		<div class="txn-amount text-center">
			<span><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_TOTAL_PAYABLE'); ?>:</span>
			<span><?php
				$amount = $item->get('payment.id') ? $item->get('payment.amount_payable') : $item->get('grand_total');
				echo $this->helper->currency->display($amount, $item->get('currency'), null, false) ?>
			</span>
		</div>

		<br>
		<div class="sub-title text-center"><?php
			echo JText::_('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_METHOD') ?>:
			<strong><?php echo $item->get('payment.method_name'); ?></strong>
		</div>
		<br>

		<div class="text-center">
			<?php $status = $this->helper->order->getStatus($item->get('id')); ?>
			<h1 class="red"><?php echo isset($status->s_title) ? $status->s_title : JText::_('COM_SELLACIOUS_ORDER_RECEIPT_UNKNOWN'); ?></h1>
		</div>
		<br>

		<div class="footer"><?php echo JText::_('COM_SELLACIOUS_ORDER_RECEIPT_FOOT_NOTE'); ?>
			<?php echo $app->get('mailfrom'); ?></div>
	</div>
</div>

