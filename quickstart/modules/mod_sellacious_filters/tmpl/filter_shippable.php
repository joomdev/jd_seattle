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
?>
<?php if (!$helper->config->get('hide_shippable_filter')): ?>
	<?php
	JHtml::_('stylesheet', 'mod_sellacious_filters/jquery.autocomplete.ui.css', null, true);
	JHtml::_('script', 'mod_sellacious_filters/jquery.autocomplete.ui.js', false, true);
	JHtml::_('script', 'mod_sellacious_filters/locations.js', false, true);

	$itemId = JFactory::getApplication()->input->getInt("Itemid", 0);

	$types = $helper->config->get('shippable_location_search_in', array('country'));
	$args   = array(
		'id'    => 'filter_shippable_text',
		'hid'   => 'filter_shippable',
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
	<div class="filter-snap-in shippablesearch">
		<div class="filter-title filter-shippable"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_SHIPPABLE_LOCATION'); ?></div>
		<div class="search-filter">
			<input type="hidden" name="filter[shippable]" id="filter_shippable" value="<?php
			echo $state->get('filter.shippable'); ?>">
			<input type="text" name="filter[shippable_text]" id="filter_shippable_text" value="<?php
			echo $state->get('filter.shippable_text'); ?>" placeholder="<?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHIPPABLE_LOCATION_PLACEHOLDER'); ?>" autocomplete="off"/>
			<button class="btn-filter_shippable btn btn-default" onclick="this.form.submit();"><i class="fa fa-search"></i></button>
		</div>
	</div>
<?php endif; ?>
