<?php
/**
 * @version     1.6.1
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousHelper  $helper */

/** @var  JObject  $state */

$app  = JFactory::getApplication();
$view = $app->input->get('view');

if ($view == 'store')
{
	return;
}
?>
<?php if (!$helper->config->get('hide_store_availability_filter') && JPluginHelper::isEnabled('system', 'sellacioushyperlocal')): ?>
	<div class="filter-snap-in">
		<div class="filter-title filter-store-availability"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_STORE_AVAILABILITY'); ?></div>
		<div class="filter-store">
			<?php $open = $state->get('filter.show_open_stores', '') ? 'checked="checked"' : ''; ?>
			<label>
				<input type="checkbox" <?php echo $open; ?>  name="filter[show_open_stores]" id="filter_show_open_stores" value="1" onclick="this.form.submit();">
				<?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOW_OPEN_STORES_LABEL'); ?>
			</label>
			<?php $delivery = $state->get('filter.delivery_available', '') ? 'checked="checked"' : ''; ?>
			<label>
				<input type="checkbox" <?php echo $delivery; ?>  name="filter[delivery_available]" id="filter_delivery_available" value="1" onclick="this.form.submit();">
				<?php echo JText::_('MOD_SELLACIOUS_FILTERS_STORE_DELIVERY_AVAILABLE_LABEL'); ?>
			</label>
			<?php $pickup = $state->get('filter.pickup_available', '') ? 'checked="checked"' : ''; ?>
			<label>
				<input type="checkbox" <?php echo $pickup; ?>  name="filter[pickup_available]" id="filter_pickup_available" value="1" onclick="this.form.submit();">
				<?php echo JText::_('MOD_SELLACIOUS_FILTERS_STORE_PICKUP_AVAILABLE_LABEL'); ?>
			</label>
		</div>
	</div>
<?php endif; ?>

