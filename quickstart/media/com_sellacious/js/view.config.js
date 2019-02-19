/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($) {
	$(document).ready(function () {
		// B2B
		var $allowAuthUsers = $('#jform_com_sellacious_allow_client_authorised_users');
		var $allowCredit = $('#jform_com_sellacious_allow_credit_limit2').closest('label.btn-default');
		$($allowAuthUsers).change(function () {
			var authUsers = $allowAuthUsers.find('input[type="radio"]').filter(':checked').val();
			if (authUsers == 0) {
				$allowCredit.hide();
			} else {
				$allowCredit.show();
			}
		}).triggerHandler('change');

		// Review and Rating
		var $nonBuyerRating = $('#jform_com_sellacious_allow_non_buyer_ratings');
		var $ratingStatus = $('#jform_com_sellacious_allowed_status').closest('div.input-row');
		$($nonBuyerRating).change(function () {
			var rating = $nonBuyerRating.find('input[type="radio"]').filter(':checked').val();
			if (rating == 0) {
				$ratingStatus.show();
			} else {
				$ratingStatus.hide();
			}
		}).triggerHandler('change');

		// Shipped By
		var $shipBy = $('#jform_com_sellacious_shipped_by');
		var $shipLocationBySeller = $('#jform_com_sellacious_shippable_location_by_seller').closest('div.input-row');
		var $flatShipping = $('#jform_com_sellacious_flat_shipping');
		var $flatFee = $('#jform_com_sellacious_shipping_flat_fee').closest('div.input-row');
		var $sellerShipMethods = $('#jform_com_sellacious_seller_shippable_methods').closest('div.row');
		var $shipOriginGroup = $('#jform_com_sellacious_ship_origin_group,' +
			'#jform_com_sellacious_shipping_address_line1,' +
			'#jform_com_sellacious_shipping_address_line2,' +
			'#jform_com_sellacious_shipping_address_line3,' +
			'#jform_com_sellacious_shipping_country,' +
			'#jform_com_sellacious_shipping_state,' +
			'#jform_com_sellacious_shipping_district,' +
			'#jform_com_sellacious_shipping_zip').closest('div.row');

		$('#jform_com_sellacious_shipped_by,#jform_com_sellacious_flat_shipping').change(function () {
			var by = $shipBy.find('input[type="radio"]').filter(':checked').val();
			if (by == 'seller') {
				$shipLocationBySeller.show();
				$sellerShipMethods.show();
				$flatShipping.closest('div.input-row').hide();
				$shipOriginGroup.hide();
				$flatFee.hide();
			} else {
				$shipLocationBySeller.hide();
				$sellerShipMethods.hide();
				$flatShipping.closest('div.input-row').show();
				$shipOriginGroup.show();
				var flat = $flatShipping.find('input[type="radio"]').filter(':checked').val();
				flat == 1 ? $flatFee.show() : $flatFee.hide();
			}
		}).triggerHandler('change');

		var $itemisedShipping = $('#jform_com_sellacious_itemised_shipping');
		var $shippingCalcBatchRow = $('#jform_com_sellacious_shipping_calculation_batch').closest('div.row');

		$itemisedShipping.change(function () {
			var itemised = $itemisedShipping.find('input[type="radio"]').filter(':checked').val();
			if (itemised == '1') {
				$shippingCalcBatchRow.hide();
			} else {
				$shippingCalcBatchRow.show();
			}
		}).triggerHandler('change');

		// Show/Hide Image Zoom Options
		var $zoomType = $('#jform_com_sellacious_image_zoom_type');
		var $zoomTypeWindowRows = $(['#jform_com_sellacious_image_zoom_lens_border_width',
			'#jform_com_sellacious_image_zoom_lens_border_color',
			'#jform_com_sellacious_image_zoom_lens_background_color'].join(',')).closest('div.row');

		$zoomType.change(function () {
			var zoomTypeVal = $zoomType.find('input[type="radio"]').filter(':checked').val();
			if (zoomTypeVal == 'window') {
				$zoomTypeWindowRows.show();
			} else {
				$zoomTypeWindowRows.hide();
			}
		}).triggerHandler('change');

		$('#jform_com_sellacious_image_zoom_type_mobile').change(function () {
			if ($(this).find('input[type="radio"]').filter(':checked').val() == 'lens') {
				$(['#jform_com_sellacious_image_zoom_lens_size_mobile'].join(',')).closest('div.row').show();
			} else {
				$(['#jform_com_sellacious_image_zoom_lens_size_mobile'].join(',')).closest('div.row').hide();
			}
		}).triggerHandler('change');

		// Todo: Optimize to be called on specific clicks and not global ones
		$(':input').on('change click', function () {
			var $allowed_product_type = $('#jform_com_sellacious_allowed_product_type').find('input:checked').val();

			// E-Products
			var $eproduct_c = $('#jform_com_sellacious_e_product_expiry_days,#jform_com_sellacious_e_product_download_limit').closest('.input-row');
			$allowed_product_type == 'electronic' || $allowed_product_type == 'both' ? $eproduct_c.show() : $eproduct_c.hide();

			// Physical Products
			if ($allowed_product_type == 'physical' || $allowed_product_type == 'both') {
				$('#jform_com_sellacious_shop_fieldgroup_rma').closest('.row').show();
				// Return
				$('#jform_com_sellacious_purchase_return').closest('.input-row').show();

				if ($('#jform_com_sellacious_purchase_return1').is(':checked')) {
					$('#jform_com_sellacious_purchase_return_icon').closest('.input-row').hide();
					$('#jform_com_sellacious_purchase_return_icon_global,#jform_com_sellacious_purchase_return_tnc').closest('.input-row').show();
				} else if ($('#jform_com_sellacious_purchase_return2').is(':checked')) {
					$('#jform_com_sellacious_purchase_return_icon_global').closest('.input-row').hide();
					$('#jform_com_sellacious_purchase_return_icon,#jform_com_sellacious_purchase_return_tnc').closest('.input-row').show();
				} else {
					$(['#jform_com_sellacious_purchase_return_icon',
						'#jform_com_sellacious_purchase_return_icon_global',
						'#jform_com_sellacious_purchase_return_tnc'].join(',')).closest('.input-row').hide();
				}

				// Exchange
				$('#jform_com_sellacious_purchase_exchange').closest('.input-row').show();

				if ($('#jform_com_sellacious_purchase_exchange1').is(':checked')) {
					$('#jform_com_sellacious_purchase_exchange_icon').closest('.input-row').hide();
					$('#jform_com_sellacious_purchase_exchange_icon_global,#jform_com_sellacious_purchase_exchange_tnc').closest('.input-row').show();
				} else if ($('#jform_com_sellacious_purchase_exchange2').is(':checked')) {
					$('#jform_com_sellacious_purchase_exchange_icon_global').closest('.input-row').hide();
					$('#jform_com_sellacious_purchase_exchange_icon,#jform_com_sellacious_purchase_exchange_tnc').closest('.input-row').show();
				} else {
					$(['#jform_com_sellacious_purchase_exchange_icon',
						'#jform_com_sellacious_purchase_exchange_icon_global',
						'#jform_com_sellacious_purchase_exchange_tnc'].join(',')).closest('.input-row').hide();
				}
			} else {
				$('#jform_com_sellacious_shop_fieldgroup_rma').closest('.row').hide();
				$(['#jform_com_sellacious_purchase_return',
					'#jform_com_sellacious_purchase_return_icon',
					'#jform_com_sellacious_purchase_return_icon_global',
					'#jform_com_sellacious_purchase_return_tnc',
					'#jform_com_sellacious_purchase_exchange',
					'#jform_com_sellacious_purchase_exchange_icon',
					'#jform_com_sellacious_purchase_exchange_icon_global',
					'#jform_com_sellacious_purchase_exchange_tnc'].join(',')).closest('.input-row').hide();
			}

			// Shop >> Price display
			if ($('#jform_com_sellacious_product_price_display0').is(':checked')) {
				$('#jform_com_sellacious_product_price_display_pages').closest('.input-row').hide();
			} else {
				$('#jform_com_sellacious_product_price_display_pages').closest('.input-row').show();
			}

			// Product compare
			if ($('#jform_com_sellacious_product_compare1').is(':checked')) {
				$('#jform_com_sellacious_compare_limit').closest('.input-row').show();
			} else {
				$('#jform_com_sellacious_compare_limit').closest('.input-row').hide();
			}

			// Free Listing
			if ($('#jform_com_sellacious_free_listing1').is(':checked')) {
				$('#jform_com_sellacious_listing_fee').closest('.input-row').hide();
				$('#jform_com_sellacious_listing_fee_recurrence').closest('.input-row').hide();
			} else {
				$('#jform_com_sellacious_listing_fee').closest('.input-row').show();
				$('#jform_com_sellacious_listing_fee_recurrence').closest('.input-row').show();
			}

			// Layout Switcher
			var lsdChk = $('#jform_com_sellacious_list_switcher_display');
			var lsdRdo = $('#jform_com_sellacious_list_style');
			var lsdOpts = lsdChk.find('input[type="checkbox"]');

			lsdOpts.each(function () {
				var cbVal = $(this).val();
				var targetOption = lsdRdo.find('input[value="'+ cbVal +'"]');
				if ($(this).is(':checked')) {
					targetOption.attr('disabled', null);
					targetOption.closest('label').removeClass('hidden');
				} else {
					targetOption.attr('disabled', 'disabled').attr('checked', null).removeAttr('active');
					targetOption.closest('label').removeClass('active').addClass('hidden');

					var lsdRdoAct = lsdRdo.find('input[type="radio"]:not([disabled])');
					if (lsdRdoAct.length > 0) {
						lsdRdoAct.each(function () {
							$(this).attr('checked', null);
							$(this).closest('label').removeClass('active');
						});
						lsdRdoAct.first().attr('checked', 'checked');
						lsdRdoAct.first().closest('label').addClass('active');
					}
				}
			});

			// Image Zoom Enable/Disable
			if ($('#jform_com_sellacious_image_zoom_enable1').is(':checked')) {
				$(['#jform_com_sellacious_image_zoom_type',
					'#jform_com_sellacious_image_zoom_window_width',
					'#jform_com_sellacious_image_zoom_window_height',
					'#jform_com_sellacious_image_zoom_lens_border_width',
					'#jform_com_sellacious_image_zoom_lens_border_color',
					'#jform_com_sellacious_image_zoom_lens_background_color',
					'#jform_com_sellacious_image_zoom_border_width',
					'#jform_com_sellacious_image_zoom_border_color',
					'#jform_com_sellacious_image_zoom_easing_enable',
					'#jform_com_sellacious_image_zoom_lens_size',
					'#jform_com_sellacious_image_zoom_type_mobile',
					'#jform_com_sellacious_image_zoom_lens_size_mobile',
					'#jform_com_sellacious_image_zoom_scroll_enable'].join(',')).closest('.input-row').hide();

			} else {
				var zT = $('#jform_com_sellacious_image_zoom_type').find('input[type="radio"]').filter(':checked').val();
				$(['#jform_com_sellacious_image_zoom_type',
					'#jform_com_sellacious_image_zoom_border_width',
					'#jform_com_sellacious_image_zoom_border_color',
					'#jform_com_sellacious_image_zoom_type_mobile',
					'#jform_com_sellacious_image_zoom_lens_size_mobile',
					'#jform_com_sellacious_image_zoom_scroll_enable'].join(',')).closest('.input-row').show();

				if(zT == 'window') {
					$(['#jform_com_sellacious_image_zoom_window_width',
						'#jform_com_sellacious_image_zoom_window_height',
						'#jform_com_sellacious_image_zoom_lens_border_width',
						'#jform_com_sellacious_image_zoom_lens_border_color',
						'#jform_com_sellacious_image_zoom_lens_background_color',
						'#jform_com_sellacious_image_zoom_easing_enable'].join(',')).closest('div.row').show();

					$(['#jform_com_sellacious_image_zoom_lens_size'].join(',')).closest('div.row').hide();

					$zTM = $('#jform_com_sellacious_image_zoom_type_mobile').find('input[type="radio"]').filter(':checked').val();
					if ($zTM == 'lens') {
						$(['#jform_com_sellacious_image_zoom_lens_size_mobile'].join(',')).closest('div.row').show();
					} else {
						$(['#jform_com_sellacious_image_zoom_lens_size_mobile'].join(',')).closest('div.row').hide();
					}
				} else {
					$(['#jform_com_sellacious_image_zoom_window_width',
						'#jform_com_sellacious_image_zoom_window_height',
						'#jform_com_sellacious_image_zoom_lens_border_width',
						'#jform_com_sellacious_image_zoom_lens_border_color',
						'#jform_com_sellacious_image_zoom_lens_background_color',
						'#jform_com_sellacious_image_zoom_easing_enable'].join(',')).closest('div.row').hide();

					$(['#jform_com_sellacious_image_zoom_lens_size'].join(',')).closest('div.row').show();

					$zTM = $('#jform_com_sellacious_image_zoom_type_mobile').find('input[type="radio"]').filter(':checked').val();
					if ($zTM == 'lens') {
						$(['#jform_com_sellacious_image_zoom_lens_size_mobile'].join(',')).closest('div.row').show();
					} else {
						$(['#jform_com_sellacious_image_zoom_lens_size_mobile'].join(',')).closest('div.row').hide();
					}
				}
			}
		})
		// This is too costly for the browser if called for each element, hence call for just the first one
			.eq(0).triggerHandler('change');

		$('#jform_com_sellacious_category_menu_menutype').change(function () {
			var menutype = $(this).val();
			var $categoryMenuParent = $('#jform_com_sellacious_category_menu_parent');
			var oldVal = $categoryMenuParent.val();
			$categoryMenuParent.val('1').trigger('change');
			$categoryMenuParent.find('option').each(function () {
				$(this).val() === '1' || $(this).remove();
			});
			if (menutype === '-') {
				$categoryMenuParent.closest('.input-row').hide();
			} else {
				$categoryMenuParent.closest('.input-row').show();
				$.ajax({
					url: 'index.php?option=com_menus&task=item.getParentItem&menutype=' + menutype,
					dataType: 'json'
				}).done(function (data) {
					$.each(data, function (i, val) {
						var option = $('<option>');
						option.text(val.title).val(val.id);
						$categoryMenuParent.append(option);
					});
					$categoryMenuParent.val(oldVal).trigger('change');
				});
			}
		}).triggerHandler('change');

		$(document).on('subform-row-add', function (event, row) {
			$(row).find('select').select2();
		});
	});
})(jQuery);
