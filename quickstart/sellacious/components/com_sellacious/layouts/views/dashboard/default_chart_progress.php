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

/** @var $this SellaciousViewDashboard */
$now   = $this->helper->core->fixDate('now', null, null);
$today = $now->format('Y-m-d 00:00:00', true);

$this_day   = $this->helper->core->fixDate($today, null, null);
$this_week  = $this->helper->core->fixDate($today, null, null)->modify('+1 day')->modify('last sunday');
$this_month = $this->helper->core->fixDate($now->format('Y-m-01 00:00:00', true), null, null);
$this_year  = $this->helper->core->fixDate($now->format('Y-01-01 00:00:00', true), null, null);

$ratio_today = $this->helper->report->getConversionRatio($this_day->toSql(), $now->toSql());
$ratio_week  = $this->helper->report->getConversionRatio($this_week->toSql(), $now->toSql());
$ratio_month = $this->helper->report->getConversionRatio($this_month->toSql(), $now->toSql());
$ratio_year  = $this->helper->report->getConversionRatio($this_year->toSql(), $now->toSql());
?>
<section id="widget-grid">
	<div class="row">
		<article class="col-sm-12">
			<div class="jarviswidget chart_progress">
				<header>
					<span class="widget-icon"> <i class="fa fa-pie-chart txt-color-darken"></i> </span>
					<h2><?php echo JText::_('COM_SELLACIOUS_CONVERSION_STATISTICS_HEADING'); ?></h2>
				</header>
				<div class="widget-body">
					<div id="myTabContent" class="tab-content">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 show-stats">
							<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"><span class="text"> <?php echo JText::_('COM_SELLACIOUS_DASHBOARD_VISITOR_TODAY'); ?> <span
											class="pull-right"><?php echo JText::sprintf('COM_SELLACIOUS_DASHBOARD_STATS_ORDERS_VISITORS', $ratio_today->orders, $ratio_today->visits); ?></span> </span>
									<div class="progress bg-color-yellow">
										<div class="progress-bar bg-color-blueDark" style="width: <?php echo round($ratio_today->percentage, 1) ?>%;"></div>
									</div>
								</div>
								<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"><span class="text"> <?php echo JText::_('COM_SELLACIOUS_DASHBOARD_VISITOR_THIS_WEEK'); ?><span
											class="pull-right"><?php echo JText::sprintf('COM_SELLACIOUS_DASHBOARD_STATS_ORDERS_VISITORS', $ratio_week->orders, $ratio_week->visits); ?></span> </span>
									<div class="progress bg-color-yellow">
										<div class="progress-bar bg-color-blue" style="width: <?php echo round($ratio_week->percentage, 1) ?>%;"></div>
									</div>
								</div>
								<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"><span class="text"> <?php echo JText::_('COM_SELLACIOUS_DASHBOARD_VISITOR_THIS_MONTH'); ?><span
											class="pull-right"><?php echo JText::sprintf('COM_SELLACIOUS_DASHBOARD_STATS_ORDERS_VISITORS', $ratio_month->orders, $ratio_month->visits); ?></span> </span>
									<div class="progress bg-color-yellow">
										<div class="progress-bar bg-color-blue" style="width: <?php echo round($ratio_month->percentage, 1) ?>%;"></div>
									</div>
								</div>
								<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"><span class="text"> <?php echo JText::_('COM_SELLACIOUS_DASHBOARD_VISITOR_THIS_YEAR'); ?><span
											class="pull-right"><?php echo JText::sprintf('COM_SELLACIOUS_DASHBOARD_STATS_ORDERS_VISITORS', $ratio_year->orders, $ratio_year->visits); ?></span> </span>
									<div class="progress bg-color-yellow">
										<div class="progress-bar bg-color-greenLight" style="width: <?php echo round($ratio_year->percentage, 1) ?>%;"></div>
									</div>
								</div>
							</div>
							<div class="banner-box-right">
								<?php if ($this->show_banners): ?>
								<div id="sellacious-banner-right-a"></div>
								<!--<div id="sellacious-banner-right-b"></div>-->
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</article>
	</div>
</section>
