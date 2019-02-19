jQuery(document).ready(function ($) {
	$(document).on('change', '#jform_seller_hyperlocal_shipping_location_type', function () {
		hyperlocalSelection($(this));
	});

	var hyperlocalSelection = function (element) {
		var by = $(element).find('input[type="radio"]:checked').val();

		if (by == 1) {
			$('#jform_seller_hyperlocal_shipping_distance_m').closest('div.input-row').hide();
			$('#jform_seller_hyperlocal_shipping_distance_m').closest('div.control-group').hide();
		} else {
			$('#jform_seller_hyperlocal_shipping_distance_m').closest('div.input-row').show();
			$('#jform_seller_hyperlocal_shipping_distance_m').closest('div.control-group').show();
		}
	}

	hyperlocalSelection('#jform_seller_hyperlocal_shipping_location_type');
});
