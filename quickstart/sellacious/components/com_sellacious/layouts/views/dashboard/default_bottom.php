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

/** @var $this SellaciousViewDashboard */
?>
<ul id="sparks" class="row sellacious-stats">
	<?php
	$overall = reset($this->orderStats);
	$daily   = array_slice($this->orderStats, 1);
	$dailyC  = ArrayHelper::getColumn($daily, 'count');
	$dailyA  = ArrayHelper::getColumn($daily, 'value');
	?>
	<li class="sparks-info stats-block visible col-lg-2 col-md-4 col-sm-6 col-xs-12" style="background: #088ce2;">
		<label><?php echo JText::_('COM_SELLACIOUS_DASHBOARD_SITE_ORDERS_LABEL'); ?></label>
		<div class="stat-value"><?php echo $overall->count ?></div>
		<div class="stat-icon"><i class="fa fa-gavel"></i></div>
	</li>

	<li class="sparks-info stats-block visible col-lg-2 col-md-4 col-sm-6 col-xs-12" style="background: #07a207;">
		<label><?php echo JText::_('COM_SELLACIOUS_DASHBOARD_ORDERS_VALUE_LABEL'); ?></label>
		<div class="stat-value"><?php echo $overall->amount ?></div>
		<div class="stat-icon"><i class="fa fa-cart-arrow-down"></i></div>
	</li>

	<?php $stat_count  = isset($daily[0]) ? $daily[0]->count : 0; ?>
	<?php $stat_amount = isset($daily[0]) ? $daily[0]->amount : 0; ?>

	<li class="sparks-info stats-block visible col-lg-2 col-md-4 col-sm-6 col-xs-12" style="background: #00aff0;">
		<label><?php echo JText::_('COM_SELLACIOUS_DASHBOARD_ORDERS_TODAY_LABEL'); ?></label>
		<div class="stat-value">&nbsp;<?php echo $stat_count ?></div>
		<div class="stat-icon"><i class="fa fa-money"></i></div>
	</li>

	<li class="sparks-info stats-block visible col-lg-2 col-md-4 col-sm-6 col-xs-12" style="background: #f19c19;">
		<label><?php echo JText::_('COM_SELLACIOUS_DASHBOARD_REVENUE_TODAY_LABEL'); ?></label>
		<div class="stat-value"><?php echo $stat_amount; ?></div>
		<div class="stat-icon"><i class="fa fa-bank"></i></div>
	</li>

	<?php $counts = $this->helper->report->getProductCount(); ?>

	<li class="sparks-info stats-block visible col-lg-2 col-md-4 col-sm-6 col-xs-12" style="background: #1688d2;">
		<label><?php echo JText::_('COM_SELLACIOUS_DASHBOARD_PRODUCTS_LABEL'); ?></label>
		<div class="stat-value"><?php echo $counts->product ?> / <small> <?php echo $counts->product + $counts->variant ?></small></div>
		<div class="stat-icon"><i class="fa fa-cubes"></i></div>
	</li>

	<?php $userCount = $this->helper->access->check('user.list') ? $this->helper->user->count(array('list.from' => '#__users', 'block' => '0')) : '0'; ?>

	<li class="sparks-info stats-block visible col-lg-2 col-md-4 col-sm-6 col-xs-12" style="background: #f4402c">
		<label><?php echo JText::_('COM_SELLACIOUS_DASHBOARD_USERS_LABEL'); ?></label>
		<div class="stat-value"><?php echo $userCount; ?></div>
		<div class="stat-icon"><i class="fa fa-users"></i></div>
	</li>
</ul>
<div class="clearfix"></div>

