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

JHtml::_('jquery.framework');

/** @var  SellaciousViewTransaction $this */
JHtml::_('stylesheet', 'com_sellacious/component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.transaction.receipt.css', array('version' => S_VERSION_CORE, 'relative' => true));

$app  = JFactory::getApplication();
$item = $this->item;
?>
<div class="clearfix"></div>

<?php if ($app->input->get('tmpl') == 'component'): ?>
<script>
jQuery(document).ready(function () {
	window.print();
});
</script>
<?php else: ?>
<div id="receipt-head" class="text-right">
	<?php $list  = JRoute::_('index.php?option=com_sellacious&view=transactions'); ?>
	<?php $print = JRoute::_('index.php?option=com_sellacious&view=transaction&layout=receipt&tmpl=component&id=' . $item->get('id')); ?>
	<a class="btn btn-sm btn-default" href="<?php echo $list ?>"><i class="fa fa-list"></i> <?php echo JText::_('COM_SELLACIOUS_TRANSACTION_BUTTON_LABEL_VIEW_ALL_TRANSACTION'); ?></a>
	<a class="btn btn-sm btn-primary" target="_blank" href="<?php echo $print ?>"><i class="fa fa-print"></i> <?php echo JText::_('COM_SELLACIOUS_TRANSACTION_BUTTON_LABEL_PRINT'); ?></a>
</div>
<?php endif; ?>

<div class="clearfix hidden-lg"></div>
<div id="receipt-page">
	<div id="receipt-logo"><img src="<?php echo $this->helper->media->getImage('config.shop_logo', 1) ?>"/></div>
	<div class="title text-center"><?php echo JText::_('COM_SELLACIOUS_TRANSACTION_RECEIPT_RECEIPT_LABEL'); ?></div>
	<div class="title text-center"><?php echo JText::_('COM_SELLACIOUS_TRANSACTION_RECEIPT_TRANSACTION_NUMBER_LABEL'); ?>: <?php echo $item->get('txn_number'); ?></div>
	<br>

	<div class="sub-title text-center"><?php echo JText::_('COM_SELLACIOUS_TRANSACTION_RECEIPT_FOR_LABEL'); ?> <strong><?php
		echo $this->helper->transaction->getContext($item->get('context'), $item->get('context_id'));
	?></strong></div>
	<div class="datetime text-center"><?php echo JHtml::_('date', $item->get('txn_date'), 'F d, Y h:i A T') ?></div>
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

	<div class="txn-amount">
		<span><?php echo JText::_('COM_SELLACIOUS_TRANSACTION_RECEIPT_AMOUNT'); ?>: </span>
		<?php echo $this->helper->currency->display($item->get('amount'), $item->get('currency'), null); ?>
	</div>
	<br>

	<div class="sub-title text-center"><?php echo JText::_('COM_SELLACIOUS_TRANSACTION_RECEIPT_VIA_LABEL'); ?>: <strong> <?php
		if ($method_id = $this->item->get('payment_method_id'))
		{
			echo $this->helper->paymentMethod->getFieldValue($method_id, 'title', 'Unknown Payment Mode');
		}
		else
		{
			echo JText::_('COM_SELLACIOUS_TRANSACTION_DIRECT_' . strtoupper($this->item->get('crdr', 'TX')));
		}
	?></strong></div>
	<br>

	<div class="text-center">
		<?php
		// todo: Also add other status icon
		if ($item->get('state') == 1)
		{
			echo JHtml::_('image', 'com_sellacious/paid-stamp.png', 'PAID', null, true);
		}
		else
		{
			$states = array(
				+0 => JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_STATE_X_0'),
				+1 => JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_STATE_X_1'),
				+2 => JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_STATE_X_2'),
				-1 => JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_STATE_X_-1'),
				-2 => JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_STATE_X_-2'),
			);
			$default = JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_STATE_X_');

			echo '<h1 class="red">' . Joomla\Utilities\ArrayHelper::getValue($states, $item->get('state'), $default) . '</h1>';
		}
		?>
	</div>
	<br>

	<div class="footer"><?php echo JText::_('COM_SELLACIOUS_TRANSACTION_RECEIPT_FOOT_NOTE'); ?>
		<?php echo $app->get('mailfrom'); ?></div>
</div>

<div class="clearfix"></div>
