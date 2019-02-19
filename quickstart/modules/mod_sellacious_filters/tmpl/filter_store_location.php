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

JHtml::_('behavior.framework');

/** @var  SellaciousHelper  $helper */

/** @var  JObject  $state */

$app  = JFactory::getApplication();
$view = $app->input->get('view');

if ($view == 'store')
{
	return;
}
?>
<?php if (!$helper->config->get('hide_store_location_filter')): ?>
	<?php
	JHtml::_('stylesheet', 'mod_sellacious_filters/jquery.autocomplete.ui.css', null, true);
	JHtml::_('script', 'mod_sellacious_filters/jquery.autocomplete.ui.js', false, true);
	JHtml::_('script', 'mod_sellacious_filters/locations.js', false, true);

	$itemId = $app->input->getInt("Itemid", 0);

	$types = $helper->config->get('store_location_custom_search_in', array('country'));
	$args   = array(
		'id'    => 'filter_store_location_custom_text',
		'hid'   => 'filter_store_location_custom',
		'name'  => '',
		'types' => $types,
		'ItemId'=> $itemId,
	);
	$args   = json_encode($args);
	$script = "
		jQuery(document).ready(function($) {
			var o = new ModSellaciousFiltersLocation;
			o.setup({$args});
		});
	";
	JFactory::getDocument()->addScriptDeclaration($script);
	?>
	<div class="filter-snap-in">
		<div class="filter-title filter-shop-location"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_STORE_LOCATION'); ?></div>
		<div class="filter-shop-locations">
			<?php
			$locations = array(
				JHtml::_('select.option', '0', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_STORE_LOCATION_ANYWHERE'))
			);

			if (!empty($countryName = $helper->location->ipToCountryName()))
			{
				$locations[] = JHtml::_('select.option', '1', JText::_($countryName));
			}

			$locations[] = JHtml::_('select.option', '2', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_STORE_LOCATION_CUSTOM'));

			echo JHtml::_('select.radiolist', $locations, 'filter[store_location]', array('class' => 'store-location-options'), 'value', 'text', $state->get('filter.store_location', 0)); ?>

			<?php $hidden = $state->get('filter.store_location') == 2 ? '' : 'hidden'; ?>
			<div class="<?php echo $hidden; ?> s-l-custom-block">
				<input type="hidden" name="filter[store_location_custom]" id="filter_store_location_custom" value="<?php echo $state->get('filter.store_location_custom', '') ?>">
				<input type="text" class="w80p s-l-custom-text" name="filter[store_location_custom_text]" id="filter_store_location_custom_text" value="<?php echo $state->get('filter.store_location_custom_text', '') ?>">
				<button class="btn-filter_shop_location btn btn-default" onclick="this.form.submit();"><i class="fa fa-search"></i></button>
			</div>
		</div>
	</div>
<?php endif; ?>

