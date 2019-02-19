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
<?php if (!$helper->config->get('hide_listing_type_filter')): ?>
	<div class="filter-snap-in">
		<div class="filter-title filter-listing_type"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_LISTING_TYPE'); ?></div>
		<div class="filter-con-list">
			<?php
			$listing_type = array(
				JHtml::_('select.option', '0', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_LISTING_TYPE_ALL')),
				JHtml::_('select.option', '1', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_LISTING_TYPE_NEW')),
				JHtml::_('select.option', '2', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_LISTING_TYPE_USED')),
				JHtml::_('select.option', '3', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_LISTING_TYPE_REFURBISHED'))
			);

			echo JHtml::_('select.radiolist', $listing_type, 'filter[listing_type]', array('onchange' => 'this.form.submit()'), 'value', 'text', $state->get('filter.listing_type', 0)); ?>
		</div>
	</div>
<?php endif; ?>
