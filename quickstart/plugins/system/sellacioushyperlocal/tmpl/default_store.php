<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die('Restricted access');

/** @var  array  $displayData */
extract($displayData['sellerTimings']);

$params       = $displayData['params'];
$sellerParams = $displayData['sellerParams'];

$show_store_timings = $sellerParams->get('show_store_timings', $params->get('show_store_timings', 1));
$show_delivery_timings = $sellerParams->get('show_delivery_timings', $params->get('show_delivery_timings', 1));
$show_pickup_timings = $sellerParams->get('show_pickup_timings', $params->get('show_pickup_timings', 1));

$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

?>
	<div class="clearfix"></div>
<?php if (!empty($displayData['availability'])): ?>
	<div class="store_availiability">
		<?php echo implode(' | ', $displayData['availability']);?>
	</div>
<?php endif; ?>
<?php
if (!empty($timings) && $show_store_timings)
{
	?>
	<div class="clearfix"></div>
	<div class="seller-timings">
		<h4><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_TIMINGS');?></h4>
		<ul>
			<?php foreach ($timings as $timing):?>
			<li><?php echo $days[$timing['week_day']] . ': ' . ($timing['full_day'] ? JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_FULL_DAY') : JFactory::getDate($timing['from_time'])->format('h:i A') . ' - ' . JFactory::getDate($timing['to_time'])->format('h:i A'));?></li>
			<?php endforeach;?>
		</ul>
	</div>
	<?php
}

if (!empty($delivery_hours) && $show_delivery_timings)
{
	?>
	<div class="clearfix"></div>
	<div class="seller-timings">
		<h4><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_DELIVERY_HOURS');?></h4>
		<ul>
			<?php foreach ($delivery_hours as $delivery):?>
			<li><?php echo $days[$delivery['week_day']] . ': ' . ($delivery['full_day'] ? JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_FULL_DAY') : JFactory::getDate($delivery['from_time'])->format('h:i A') . ' - ' . JFactory::getDate($delivery['to_time'])->format('h:i A'));?></li>
			<?php endforeach;?>
		</ul>
	</div>
	<?php
}

if (!empty($pickup_hours) && $show_pickup_timings)
{
	?>
	<div class="clearfix"></div>
	<div class="seller-timings">
		<h4><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PICKUP_HOURS');?></h4>
		<ul>
			<?php foreach ($pickup_hours as $pickup):?>
			<li><?php echo $days[$pickup['week_day']] . ': ' . ($pickup['full_day'] ? JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_FULL_DAY') : JFactory::getDate($pickup['from_time'])->format('h:i A') . ' - ' . JFactory::getDate($pickup['to_time'])->format('h:i A'));?></li>
			<?php endforeach;?>
		</ul>
	</div>
	<?php
}
