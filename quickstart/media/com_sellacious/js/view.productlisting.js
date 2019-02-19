/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


jQuery(function ($) {
	$(document).ready(function () {
		var Ajax = function (url, data, callback, fallback) {
			$.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				success: function (response) {
					if (typeof callback == 'function') callback(response);
				},
				error: function (jqXHR) {
					console.log(jqXHR.responseText);
					if (typeof fallback == 'function') fallback();
				}
			});
		};

		var reTotal = function () {
			var $els = $('#jform_listing_days_cost,#jform_special_categories_total');
			var gTotal = 0;

			$els.each(function (i, element) {
				var val = $(element).val();
				gTotal += isNaN(val) ? 0 : parseFloat(val);
			});

			// Since we have multiple products, some of which may have been deselected/deleted
			gTotal = gTotal * $('.product-row').not('.deleted').length;

			var $gTotal = $('#product-listingfee-total');
			var formats = $gTotal.data('format') || {pos: [], neg: []};
			var format = gTotal < 0 ? formats.neg : formats.pos;

			var text = format[0] || '{NUM}';
			text = text.replace('{NUM}', number_format(gTotal, format[1], format[2], format[3]));

			$gTotal.find('span').text(text).data('amount', gTotal);
		};

		$(document).on('click', '.variant-info-toggle', function () {
			if ($(this).is(':disabled')) return false;

			var id = $(this).closest('tr').data('id');
			$(this).find('i').toggleClass('fa-plus-square-o').toggleClass('fa-minus-square-o');
			$('.variant-info-pid-' + id).toggleClass('hidden');
			return false;
		});

		$(document).on('change', '#jform_listing_days_cost,#jform_special_categories_total', reTotal);

		$('#itemList').on('click', '.del-product', function () {
			var $target = $(this).closest('tr');
			var id = $target.data('id');
			var $btnAction = $target.find('.del-product').closest('td');
			var $variantRow = $('#itemList').find('.variant-info-pid-' + id);

			if ($target.data('deleted')) {
				$target.removeClass('deleted').data('deleted', false);
				$target.find('td').fadeTo('slow', 1.0).find(':input').removeAttr('disabled');
				$variantRow.fadeTo('slow', 1.0).find(':input').removeAttr('disabled');
				$target.find('.del-product').addClass('btn-danger').removeClass('btn-success')
					.html('<i class="fa fa-times"></i> Remove');
			}
			else {
				$target.addClass('deleted').data('deleted', true);
				$target.find('td').not($btnAction).fadeTo('slow', 0.25).find(':input').attr('disabled', 'disabled');
				$variantRow.fadeTo('slow', 0.25).find(':input').attr('disabled', 'disabled');
				$target.find('.del-product').removeClass('btn-danger').addClass('btn-success')
					.html('<i class="fa fa-check"></i> Restore');
			}

			// Recalculate
			reTotal();
		});
	});
});
