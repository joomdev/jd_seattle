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

/** @var  SellaciousViewCoupons $this */
$usage = $this->helper->coupon->getUsage();
$value = $this->helper->coupon->getRedemption();

$g_currency = $this->helper->currency->getGlobal('code_3');
?>
<ul id="sparks" class="pull-right transaction-summary" style="margin-top: 0">
	<li></li>
	<li class="sparks-info span-0">
		<h5> <?php echo JText::_('COM_SELLACIOUS_COUPONS_TOTAL_USAGE_LABEL'); ?> <span class="txt-color-greenDark">
				<i class="fa fa-arrow-circle-up"></i>&nbsp;<?php
				echo (int) $usage ?>
			</span>
		</h5>
	</li>
	<li class="sparks-info span-0">
		<h5> <?php echo JText::_('COM_SELLACIOUS_COUPONS_TOTAL_REDEEMED_LABEL'); ?> <span class="txt-color-red">
				<i class="fa fa-arrow-circle-down"></i>&nbsp;<?php
				echo $this->helper->currency->display($value, $g_currency, null); ?>
			</span>
		</h5>
	</li>
</ul>

