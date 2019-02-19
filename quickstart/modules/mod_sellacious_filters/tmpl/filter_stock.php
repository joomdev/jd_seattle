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
<?php if (!$helper->config->get('hide_stock_filter') && !$helper->config->get('hide_out_of_stock')): ?>
	<div class="filter-snap-in">
		<div class="filter-title filter-product-stock"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_STOCK'); ?></div>
		<div class="filter-stock">
			<?php $checked = $state->get('filter.hide_out_of_stock', '') ? 'checked="checked"' : ''; ?>
			<label>
				<input type="checkbox" <?php echo $checked; ?>  name="filter[hide_out_of_stock]" id="filter_hide_out_of_stock" value="1" onclick="this.form.submit();">
				<?php echo JText::_('MOD_SELLACIOUS_FILTERS_HIDE_OUT_STOCK_LABEL'); ?>
			</label>
		</div>
	</div>
<?php endif; ?>

