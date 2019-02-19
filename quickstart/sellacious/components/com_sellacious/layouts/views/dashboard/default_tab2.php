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

/** @var  SellaciousViewDashboard  $this */

/** @var  JDate  $start */
/** @var  JDate  $end */
$now   = JFactory::getDate()->format('Y-m-d H:i:s');
$start = $this->helper->core->fixDate($now, 'UTC', null)->setTime(0, 0, 0)->modify('-1 month');
$end   = $this->helper->core->fixDate($now, 'UTC', null)->setTime(0, 0, 0)->modify('+1 day');

$chart = $this->helper->report->getDailyPageViews($start, $end);
?>
<script>
jQuery(function ($) {
	$(document).ready(function () {
		var dashboard = new SellaciousDashboard;
		dashboard.init({
				min: <?php echo $start->toUnix() + $start->getOffsetFromGmt(); ?>,
				max: <?php echo $end->toUnix() + $end->getOffsetFromGmt(); ?>,
				range: 'm'
			},
			'#stats-chart2',
			'#range-slider2',
			<?php echo json_encode($chart); ?>
		);
	});
});
</script>
<div class="tab-pane fade in no-padding-bottom" id="s2">
	<div class="chart-area w100p">
		<input id="range-slider2" type="hidden" title="">
		<div id="stats-chart2" class="chart-large txt-color-blue"><!-- Graph / chart would be here --></div>
		<div id="axis-label-y2" class="axis-label-y"><?php echo JText::_('COM_SELLACIOUS_DASHBOARD_PAGE_VIEWS'); ?></div>
	</div>
</div>
