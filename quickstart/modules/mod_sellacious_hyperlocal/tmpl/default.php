<?php
/**
 * @version     1.6.1
 * @package     Sellacious Hyperlocal Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('jquery.ui');

JHtml::_('stylesheet', 'mod_sellacious_hyperlocal/jquery.autocomplete.ui.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_hyperlocal/jquery-ui.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_hyperlocal/default.css', null, true);

JText::script('MOD_SELLACIOUS_HYPERLOCAL_GEOCODE_NO_RESULTS_FOUND');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_GEOCODE_FAILED');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_FETCH_ADDRESS_FAILED');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_GET_CURRENT_LOCATION');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_DETECTING_LOCATION');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_SET_ADDRESS_FAILED');

// TODO: Get google api key from config
JHtml::_('script', 'https://maps.googleapis.com/maps/api/js?key=' . $params->get('google_api_key') . '&libraries=places', false, false);
JHtml::_('script', 'mod_sellacious_hyperlocal/jquery.autocomplete.ui.js', false, true);
JHtml::_('script', 'mod_sellacious_hyperlocal/jquery-ui.min.js', false, true);
JHtml::_('script', 'mod_sellacious_hyperlocal/default.js', false, true);

$args   = array(
	'location_field'   => 'hyperlocation',
	'location_value'   => 'hyperlocation_id',
	'geo_finder_btn'   => 'detect-location',
	'min_distance'     => $minDistance,
	'max_distance'     => $maxDistance,
	'product_distance' => $productDistance,
	'unit_rate'        => $rate,
	'params'           => $params->toArray(),
);
$args   = json_encode($args);
$script = <<<JS
		jQuery(document).ready(function($) {
			var o = new ModSellaciousHyperLocal;
			o.setup({$args});
			o.init();
			o.geolocate({$hyperlocal});
			
			var unitRate = {$rate};
			
			$('.btn-filter_shippable').prop('onclick',null).off('click');
			$('.btn-filter_shippable').on('click', function (e) {
				var value = $('#filter_shippable_text').val();
				var btn   = $(this);
				
				o.setShippableFilter(value, function(){
					btn.closest('form').submit();
				});
				
				return false;
			});
			
			$('.btn-filter_shop_location').prop('onclick',null).off('click');
			$('.btn-filter_shop_location').on('click', function (e) {
				var value = $('#filter_store_location_custom_text').val();
				var btn   = $(this);
				
				o.setLocationFilter(value, function(){
					btn.closest('form').submit();
				});
				
				return false;
			});
			
			$('#reset-location').on('click', function(e) {
			  e.preventDefault();
			  
			  $('#hyperlocation').val('');
			  $('#hyperlocation_id').val('');
			  
			  o.resetAddress(function() {
			    window.location.reload();
			  });
			});
			
			$('#update-radius').on('click', function(e){
				e.preventDefault();
				
				var lat  = $('#hyperlocation_lat').val();
				var long = $('#hyperlocation_long').val();
				var minMetres  = parseInt($('#hyperlocation_min_distance').val());
				var maxMetres  = parseInt($('#hyperlocation_max_distance').val());
				var min = $( "#distance_filter_{$module->id}" ).slider( "values", 0 );
				var max = $( "#distance_filter_{$module->id}" ).slider( "values", 1 );
				
				o.setRadiusRange(lat, long, min, max, minMetres, maxMetres);
			});
			
			$( "#distance_filter_{$module->id}" ).slider({
			  range: true,
			  min: {$distance_min},
			  max: {$distance_max},
			  values: [ parseInt({$min_radius}), parseInt({$max_radius}) ],
			  slide: function( event, ui ) {
				$('#distance_min_display_{$module->id}').text(ui.values[0]);
				$('#distance_max_display_{$module->id}').text(ui.values[1]);
				$('#hyperlocation_min_distance').val(ui.values[0] * unitRate);
				$('#hyperlocation_max_distance').val(ui.values[1] * unitRate);
			  }
			});
			
			$('#distance_min_display_{$module->id}').text($( "#distance_filter_{$module->id}" ).slider( "values", 0 ));
			$('#distance_max_display_{$module->id}').text($( "#distance_filter_{$module->id}" ).slider( "values", 1 ));
		});
JS;
JFactory::getDocument()->addScriptDeclaration($script);
?>
<div class="mod_sellacious_hyperlocation">
	<input id="hyperlocation" name="hyperlocation" placeholder="Enter your address" type="text" data-autofill-components="<?php echo implode(',', $components);?>" value="<?php echo isset($location['address']) ? $location['address'] : '';?>">
	<input type="hidden" id="hyperlocation_id" name="hyperlocation_id" value="<?php echo isset($location['id']) ? $location['id'] : '';?>">
	<input type="hidden" id="hyperlocation_lat" name="hyperlocation_lat" value="<?php echo $location->get('lat', 0);?>">
	<input type="hidden" id="hyperlocation_long" name="hyperlocation_long" value="<?php echo $location->get('long', 0);?>">
	<input type="hidden" id="hyperlocation_min_distance" name="hyperlocation_min_distance" value="<?php echo $minDistance;?>">
	<input type="hidden" id="hyperlocation_max_distance" name="hyperlocation_max_distance" value="<?php echo $maxDistance;?>">

	<?php if ($distance_filter): ?>
	<div class="distance_filter">
		<div>
			<b><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_RADIUS');?>:</b>
			<span id="distance_min_display_<?php echo $module->id; ?>"></span>
			<span class="dist_div"> - </span>
			<span id="distance_max_display_<?php echo $module->id; ?>"></span>
			<span class="distance_unit"><?php echo $distance_unit_value; ?></span>
		</div>
		<div id="distance_filter_<?php echo $module->id; ?>"></div>
		<div class="distance_ranges">
			<span class="pull-left"><?php echo $distance_min . ' ' . $distance_unit_value; ?></span>
			<span class="pull-right"><?php echo $distance_max . ' ' . $distance_unit_value; ?></span>
		</div>
		<div class="clearfix"></div>
		<button type="button" class="btn btn-primary" id="update-radius"><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_UPDATE_RADIUS');?></button>
	</div>
	<?php endif; ?>

	<?php if ($browser_detect): ?>
	<button type="button" class="btn btn-primary" id="detect-location"><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_GET_CURRENT_LOCATION');?></button>
	<?php else: ?>
		<button type="button" class="btn btn-primary" id="reset-location"><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_RESET_LOCATION');?></button>
	<?php endif; ?>
</div>
