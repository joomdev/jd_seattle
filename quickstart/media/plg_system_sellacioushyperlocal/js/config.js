jQuery(document).ready(function ($) {
	$('#jform_plg_system_sellacioushyperlocal_hyperlocal_type').change(function () {
		hyperlocalSelection($(this));
	});

	var hyperlocalSelection = function (element) {
		var by = $(element).find('input[type="radio"]:checked').val();

		if (by == 1) {
			$('#jform_plg_system_sellacioushyperlocal_product_radius_m').closest('div.input-row').show();
			$('.shippable_location_note').closest('div.row').hide();
		} else {
			$('#jform_plg_system_sellacioushyperlocal_product_radius_m').closest('div.input-row').hide();
			$('.shippable_location_note').closest('div.row').show();
		}
	}

	hyperlocalSelection('#jform_plg_system_sellacioushyperlocal_hyperlocal_type');

	$('.btn-purge-distance-cache').on('click', function (e) {
		e.preventDefault();

		var btn = $(this);

		var paths = Joomla.getOptions('system.paths', {});
		var base = paths.root || '';

		var data = {
			option: 'com_ajax',
			plugin: 'sellacioushyperlocal',
			group : 'system',
			format: 'json'
		};

		$.ajax({
			url: base + '/index.php',
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: data,
			beforeSend: function () {
				btn.attr("disabled", true);
				btn.text(Joomla.JText._('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PURGING'));
			},
			complete: function () {
				btn.attr("disabled", false);
				btn.text(Joomla.JText._('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PURGE_CACHE'));
			}
		}).done(function (response) {
			if (response.success == true) {
				Joomla.renderMessages({success: [response.message]});
			} else {
				Joomla.renderMessages({warning: [response.message]});
			}
		}).fail(function (jqXHR) {
			Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
			console.log(jqXHR.responseText);
		});
	});
});
