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

$listingType = $state->get('filter.listing_type', 0)
?>
<?php if (!$helper->config->get('hide_item_condition_filter') && in_array($listingType, array(2, 3))): ?>
	<div class="filter-snap-in">
		<div class="filter-item-condition"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_ITEM_CONDITION'); ?></div>
		<div class="filter-con-list">
			<?php
			$item_conditions = array(
				JHtml::_('select.option', '0', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_ITEM_CONDITION_ALL')),
				JHtml::_('select.option', '1', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_ITEM_CONDITION_LIKE_NEW')),
				JHtml::_('select.option', '2', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_ITEM_CONDITION_GOOD')),
				JHtml::_('select.option', '3', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_ITEM_CONDITION_AVERAGE')),
				JHtml::_('select.option', '4', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_ITEM_CONDITION_POOR'))
			);

			echo JHtml::_('select.radiolist', $item_conditions, 'filter[item_condition]', array('onchange' => 'this.form.submit()'), 'value', 'text', $state->get('filter.item_condition', 0)); ?>
		</div>
	</div>
<?php endif; ?>
