<?php
/**
 * @version     1.6.1
 * @package     Sellacious Payment - PayPal Standard
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access.
defined('_JEXEC') or die;

/** @var stdClass $displayData */
?>
<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="utf-8">
	<script type="text/javascript">
		window.onload = function () {
			var btn = document.getElementById('pay-btn');

			window.setTimeout(btn.click(), 10000);
		};
	</script>
	<style>
		.paypalSForm-form-layout {
			text-align : center;
		}

		.paypalSForm-form-detail {
			width       : 50%;
			margin-left : 25%;
		}

		label {
			display    : inline-block;
			text-align : right;
			width      : 137px;
		}

		span {
			display     : inline-block;
			margin-left : 14px;
			text-align  : left;
			width       : 200px;
		}

		#pay-btn {
			height : 1px;
			width  : 1px;
			border : 0;
		}
	</style>
</head>

<body>
<form id="paypalStandardForm" class="paypalSForm-form-layout" action="<?php echo $displayData['url'] ?>" method="post">
	<h2><?php echo JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_LAYOUT_TRANSACTION_DETAILS_LABEL'); ?></h2>
	<div class="paypalSForm-form-detail">
		<div>
			<label><?php echo JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_LAYOUT_AMOUNT_LABEL'); ?></label>
			<span><?php echo $displayData['amount'] . ' ' . $displayData['currency_code'] ?></span>
		</div>
		<?php if (isset($displayData['user_detail']->order_number))
		{ ?>
			<div>
				<label><?php echo JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_LAYOUT_ORDER_NUMBER_LABEL'); ?></label>
				<span><?php echo $displayData['user_detail']->order_number ?></span>
			</div>
		<?php } ?>
		<div>
			<label><?php echo JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_LAYOUT_NAME_LABEL'); ?></label>
			<span><?php echo isset($displayData['user_detail']->firstname) ? $displayData['user_detail']->firstname : '-' ?></span>
		</div>
		<div>
			<label><?php echo JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_LAYOUT_EMAIL_LABEL'); ?></label>
			<span><?php echo isset($displayData['user_detail']->email) ? $displayData['user_detail']->email : '-' ?></span>
		</div>
		<div>
			<label><?php echo JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_LAYOUT_MOBILE_LABEL'); ?></label>
			<span><?php echo isset($displayData['user_detail']->phone) ? $displayData['user_detail']->phone : '-' ?></span>
		</div>
	</div>
	<br><br>

	<img src="<?php echo JUri::root() . 'plugins/sellaciouspayment/paypalstandard/images/loader.gif' ?>">

	<div><?php echo JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_LAYOUT_NO_REFRESH_NOTE'); ?></div>
	<br>
	<?php foreach ($displayData as $key => $value)
	{
		if ($key != 'url' && $key != 'user_detail')
		{ ?>
			<input type="hidden" name="<?php echo $key ?>" value="<?php echo $value; ?>" />
		<?php }
	} ?>

	<input type="submit" name="pay-btn" id="pay-btn">
</form>
</body>
</html>
