<?php
/**
 * @version     1.6.1
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousHelper  $helper */

/** @var  JObject  $state */

$app  = JFactory::getApplication();
$view = $app->input->get('view');

if ($view == 'stores')
{
	return;
}
?>
<?php if (!$helper->config->get('hide_price_filter')): ?>
<div class="filter-snap-in">
	<div class="filter-title filter-price"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_PRICE'); ?></div>
		<div class="filter-price-area">
				<input type="number" name="filter[price_from]" id="min_price_input" value ="<?php
				echo $state->get('filter.price_from'); ?>" placeholder="Min"/>
				<input type="number" name="filter[price_to]" id="max_price_input" value ="<?php
				echo $state->get('filter.price_to'); ?>" placeholder="Max"/>
			</div>
		<button class="btn-filter-price btn btn-default btn-block" onclick="this.form.submit();">Go</button>
	</div>
<?php endif; ?>
