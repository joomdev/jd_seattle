/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var SellaciousViewProduct = {
	Variant: function () {
		return this;
	},

	Related: function () {
		return this;
	}
};

(function ($) {
	// Fixed version of jQuery.clone function
	(function (original) {
		$.fn.clone = function () {
			var result = original.apply(this, arguments),
				o_textarea = this.find('textarea').add(this.filter('textarea')),
				r_textarea = result.find('textarea').add(result.filter('textarea')),
				o_select = this.find('select').add(this.filter('select')),
				r_select = result.find('select').add(result.filter('select'));

			var i, l;
			for (i = 0, l = o_textarea.length; i < l; ++i) $(r_textarea[i]).val($(o_textarea[i]).val());
			for (i = 0, l = o_select.length; i < l; ++i) r_select[i].selectedIndex = o_select[i].selectedIndex;

			return result;
		};
	})($.fn.clone);

	SellaciousViewProduct.Variant.prototype = {
		init: function (selector, token, baseDir) {
			var $that = this;
			$that.token = token;
			$that.baseDir = baseDir;
			$that.element = $(selector);

			/*
			// Earlier we force open first variant, now we have set it as optional so just close the form.
			if ($that.element.find('#variants-list').find('tbody > tr').length) {
				$that.hideForm();
			} else $that.getVariant(0, function (data) {
				$that.showForm(data);
			});
			*/
			$that.hideForm();

			$that.element.on('click', '#btn-save-variant', function () {
				$that.saveVariant(function (id) {
					$that.hideForm();
					$that.getVariant(id, function (data) {
						$that.addRow(data);
					});
				});
			});

			$that.element.on('click', '#btn-apply-variant', function () {
				$that.saveVariant(function (id) {
					// Do not hide form, but update image field
					var fileplus = $that.element.find('#jform_variant_images_wrapper').data('plugin.fileplus');
					if (fileplus) {
						fileplus.options.target.record_id = id;
						fileplus.rebuild();
					}

					var eproductmedia = $that.element.find('#jform_variant_images_wrapper').data('plugin.eproductmedia');
					if (eproductmedia) {
						eproductmedia.options.target.record_id = id;
						// eproductmedia.options.product_id = id;
						// eproductmedia.options.seller_uid = id;
						// eproductmedia.options.variant_id = id;
						eproductmedia.rebuild();
					}

					$that.getVariant(id, function (data) {
						$that.addRow(data);
					});
				});
			});

			$that.element.on('click', '#btn-close-variant', function () {
				var id = $that.element.find('#jform_variant_id').val();
				$that.hideForm();
				if (id > 0) {
					$that.getVariant(id, function (data) {
						$that.addRow(data);
					});
				}
			});

			$that.element.on('click', '.edit-variant', function () {
				var id = $(this).data('id');
				Joomla.renderMessages({info: ['Please wait while we load the variant for editing&hellip;']});
				$that.getVariant(id, function (data) {
					$that.showForm(data);
				});
			});

			$that.element.on('click', '.copy-variant', function () {
				Joomla.renderMessages({info: ['Please wait while we load the copy of the variant&hellip;']});
				$that.getVariant($(this).data('id'), function (data) {
					data.id = 0;
					data.images = [];
					data.eproduct = [];
					$that.showForm(data);
				});
			});

			$that.element.on('click', '.delete-variant', function (e) {
				var $target = $(e.target).is('.delete-variant') ? $(e.target) : $(e.target).closest('.delete-variant');
				if ($target.data('confirm')) {
					$target.data('confirm', false);
					$target.html('<i class="fa fa-times"></i> Delete');
					Joomla.renderMessages({info: ['Please wait while we attempt to remove the selected variant&hellip;']});
					var id = $(this).data('id');
					$that.removeVariant(id, function () {
						$that.element.find('#variant-row-' + id).fadeOut('slow').remove();
						$(document).trigger('removeVariant', id);
					});
				} else {
					$target.data('confirm', true);
					$target.html('<i class="fa fa-question-circle"></i> Sure');
					setTimeout(function () {
						$target.data('confirm', false);
						$target.html('<i class="fa fa-times"></i> Delete');
					}, 5000);
				}
			});
		},

		saveVariant: function (callback) {
			var $that = this;
			var form = $('<form/>').append($that.element.clone());

			if (!document.formvalidator.isValid(form)) {
				// Joomla doesn't handle the cloned form elements well, so we do it here
				var message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
				var error = [];
				var errors = $(form).find("input.invalid, textarea.invalid, select.invalid, fieldset.invalid, button.invalid");
				for (var i = 0; i < errors.length; i++) {
					$that.element.find('#' + errors[i].id).addClass('invalid');
					var $label = $that.element.find('label[for=' + errors[i].id + ']').addClass('invalid');
					if ($label.text() !== 'undefined') {
						error[i] = message + $label.text().replace("*", "");
					}
				}
				Joomla.renderMessages({error: error});
				return;
			}

			var data = form.serializeObject();

			data['jform']['variant']['product_id'] = $('#jform_id').val();
			data['option'] = 'com_sellacious';
			data['task'] = 'variant.saveAjax';
			data[$that.token] = 1;

			$.ajax({
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: data
			}).done(function (response) {
				if (response.state == 1) {
					$that.element.find('#jform_variant_id').val(response.data);
					Joomla.renderMessages({success: [response.message]});
					// We need to update the list also after save
					if (typeof callback === 'function')
						callback(response.data);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				console.log(response.responseText);
			});
		},

		getVariant: function (id, callback) {
			var $that = this;
			var data = {};
			data['option'] = 'com_sellacious';
			data['task'] = 'variant.getItemAjax';
			data[$that.token] = 1;
			data['id'] = id;

			$.ajax({
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: data
			}).done(function (response) {
				if (response.state == 1) {
					Joomla.removeMessages();
					if (typeof callback === 'function')
						callback(response.data);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				Joomla.renderMessages({warning: ['Failed to load the details for selected variant due to some server error.']});
				console.log(response.responseText);
			});
		},

		removeVariant: function (id, callback) {
			var $that = this;
			var data = {};
			data['option'] = 'com_sellacious';
			data['task'] = 'variant.deleteAjax';
			data[$that.token] = 1;
			data['id'] = id;

			$.ajax({
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: data
			}).done(function (response) {
				if (response.state == 1) {
					Joomla.renderMessages({success: [response.message]});
					if (typeof callback === 'function')
						callback(response.data);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				Joomla.renderMessages({warning: ['Failed to remove selected variant due to some server error.']});
				console.log(response.responseText);
			});
		},

		clearForm: function ($fieldset) {
			$fieldset.find(':input').not('[type="checkbox"],[type="radio"]').val('').trigger('change').trigger('keyup');
			$fieldset.find('[type="checkbox"],[type="radio"]').prop('checked', false);

			var fileplus = $fieldset.find('#jform_variant_images_wrapper').data('plugin.fileplus');
			if (fileplus) {
				fileplus.options.target.record_id = 0;
				fileplus.updateList([], true);
				fileplus.rebuild();
			}

			var eproductmedia = $fieldset.find('#jform_variant_eproduct_wrapper').data('plugin.eproductmedia');
			if (eproductmedia) {
				eproductmedia.options.variant_id = 0;
				eproductmedia.updateRecords([], true);
				eproductmedia.rebuild();
			}
		},

		hideForm: function () {
			var $that = this;
			$that.element.find('#add-variant').show();
			var $fieldset = $that.element.find('fieldset').hide();

			// Handle required fields
			$fieldset.find('[required],.required')
				.removeAttr('required').removeAttr('aria-required').removeClass('required')
				.attr('disabled', 'disabled')
				.addClass('required-off');

			// Clear all values
			$that.clearForm($fieldset);
		},

		fillForm: function ($fieldset, data) {
			if (typeof data === 'undefined') return;

			$fieldset.trigger('onVariantFillForm', [data]);

			$fieldset.find('#jform_variant_id').val(data.id || 0);
			$fieldset.find('#jform_variant_title').val(data.title);
			$fieldset.find('#jform_variant_local_sku').val(data['local_sku']);
			$fieldset.find('#jform_variant_alias').val(data['alias']);

			var features = data['features'] || [];
			$fieldset.find('input[id^="jform_variant_features"]').each(function (i) {
				$(this).val(features[i] || '');
			});

			var fileplus = $fieldset.find('#jform_variant_images_wrapper').data('plugin.fileplus');
			if (fileplus) {
				fileplus.options.target.record_id = data.id;
				fileplus.updateList(data.images || [], true);
				fileplus.rebuild();
			}

			var eproductmedia = $fieldset.find('#jform_variant_eproduct_wrapper').data('plugin.eproductmedia');
			if (eproductmedia) {
				eproductmedia.options.variant_id = data.id;
				eproductmedia.updateRecords(data['eproducts'] || [], true);
				eproductmedia.rebuild();
			}

			if (typeof data.fields === 'undefined') return;

			$.each(data.fields, function (index, field) {
				var element = $fieldset.find('#jform_variant_fields_' + field['field_id']);
				var fld_val = field['field_value'];

				if (element.is('div.btn-group')) {
					element.find('[type="checkbox"]').each(function () {
						$(this).prop('checked', $.inArray($(this).val(), fld_val) >= 0);
					});
				} else if (element.is('[type="checkbox"]')) {
					element.prop('checked', $(this).val() == fld_val);
				} else {
					// keyup trigger used to update color picker's display only
					element.val(fld_val).trigger('change').trigger('keyup');
				}
			});
		},

		showForm: function (data) {
			var $that = this;
			$that.element.find('#add-variant').hide();
			var $fieldset = $that.element.find('fieldset');

			$fieldset.find('.required-off')
				.removeAttr('disabled')
				.attr('required', 'required').attr('aria-required', 'true').addClass('required')
				.removeClass('required-off');
			$fieldset.find('.invalid').removeClass('invalid');

			// Clear all values, and fill new values
			$that.clearForm($fieldset);
			$that.fillForm($fieldset, data);

			$fieldset.show();
		},

		addRow: function (data) {
			var $that = this;

			var $row = $that.element.find('#variant-row-' + data.id);
			var $new_row = $(data.html);

			if ($row.length) {
				$row.replaceWith($new_row);
			} else {
				$that.element.find('#variants-list').find('tbody').append($new_row);
			}

			$(document).trigger('addVariant', $.extend({}, data, {html: null}));
		}
	};

	SellaciousViewProduct.Related.prototype = {
		init: function (selector, token) {
			var $that = this;
			$that.element = $(selector);
			$that.token = token;
			$that.preview = $(selector + '_preview');

			if ($that.element.length === 0) return false;

			var tags = $that.element.data('tags');

			$that.element.select2({
				tags: tags,
				createSearchChoice: function (term) {
					var choice = {
						id: $.trim(term),
						text: $.trim(term)
					};
					// First match from tags available
					$.each(tags, function (i, tag) {
						if (tag.text.toUpperCase() === $.trim(term).toUpperCase()) {
							choice.id = tag.id;
							choice.text = tag.text;
						}
					});
					// next match from current selection, select2 will be available at the time of calling this
					$.each($that.element.select2('data'), function (i, tag) {
						if (tag.text.toUpperCase() === $.trim(term).toUpperCase()) {
							choice.id = tag.id;
							choice.text = tag.text;
						}
					});
					// return finally
					return choice;
				}
			});

			$that.element.on('select2-selecting', function (e) {
				if (typeof e.choice === 'object' && typeof e.choice.text !== 'undefined') {
					if (/^\s*$/.test(e.choice.text)) {
						return false;
					} else if (/,/.test(e.choice.text)) {
						alert('Commas are not allowed.');
						return false;
					} else if (e.choice['existing']) {
						// Existing item should be already available, may be faded
						var displayed = $.grep($('.del-related-group'), function (btn) {
							var b = $(btn).data('id') == e.choice['existing'];
							// Trigger will suffice the action required
							if (b && $(btn).data('deleted')) $(btn).trigger('click');
							return b;
						});
						// Load from ajax, if already displayed ones doesn't match, this should never happen ideally
						if (displayed.length === 0) $that.loadProducts(e.choice['existing'], e.choice.text);
						// Trigger above will add automatically, return false to prevent duplicate
						else return false;
					} else {
						$that.addGroupRow(e.choice.text);
					}
				}
			});

			// We do not allow removal from select2, use provided separate button for it in the preview section
			$that.element.on('select2-removing', function () {
				return false;
			});

			// Timer used by delete button handler
			// var timer = [];

			$that.preview.on("click", '.del-related-group', function (e) {
				var $target = $(e.target).is('.del-related-group') ? $(e.target) : $(e.target).closest('.del-related-group');

				var id = $target.data('id');
				var data = $that.element.select2('data');

				// Remove any pending timeout for this button, as user has clicked now
				// if (timer[id]) clearTimeout(timer[id]);

				// Delete/Restore behaviour, skip confirmation as we don't actually delete existing ones directly
				if ($target.data('deleted')) {
					// Update select2 value
					$.each(tags, function (i, value) {
						if (value['existing'] == id) {
							data.push(value);
							return false;
						}
					});
					$that.element.select2('data', data);

					$target.data('deleted', false);
					$target.addClass('btn-danger').removeClass('btn-success')
						.html('<i class="fa fa-times"></i> Remove');
					$target.closest('tr').find('.group-items').fadeTo('slow', 1.0);
				}
				else {
					// Update select2 value
					data = $.grep(data, function (value) {
						return value['existing'] ? value['existing'] != id : value.text != id;
					});
					$that.element.select2('data', data);

					// Allow restoration of already existing groups, remove new ones immediately
					if ($target.closest('tr').data('existing')) {
						$target.data('confirm', false);
						$target.data('deleted', true);
						$target.closest('tr').find('.group-items').fadeTo('slow', .3);
						$target.removeClass('btn-danger').addClass('btn-success')
							.html('<i class="fa fa-check"></i> Restore');
					} else {
						$target.closest('tr').fadeOut('slow').remove();
					}
				}
			});

			// Preload existing, not included in form-field html to retain design consistency
			$that.preloadExisting();
		},

		preloadExisting: function () {
			var $that = this;
			var items = $that.element.data('value');
			var data = [];

			$.each(items, function (i, item) {
				$that.loadProducts(item['existing'], item.text);
				data.push(item);
			});
			$that.element.select2('data', data);
		},

		loadProducts: function (group /*, label*/) {
			var $that = this;
			$.ajax({
				url: 'index.php?option=com_sellacious&view=relatedproducts&tmpl=raw&filter[group]=' + group,
				type: 'GET',
				cache: false,
				async: true,
				success: function (response) {
					if (response == '') {
						Joomla.renderMessages({warning: ['Unknown error encountered while trying to load existing products for selected related product group.']});
					} else {
						var $row = $('<tr/>').data('existing', true);
						$row.append(
							'<td class="group-items">' + response + '</td>\
							<td style="vertical-align: top !important; width: 50px; text-align: right;">\
								<button type="button" class="btn btn-xs btn-danger del-related-group" data-id="' + group + '">\
								<i class="fa fa-times"></i> Remove</button>\
							</td>'
						);
						$that.preview.first('tbody').append($row);
					}
				},
				error: function (jqXHR) {
					console.log(jqXHR.responseText);
				}
			});
		},

		addGroupRow: function (group) {
			var $that = this;
			var $row = $('<tr/>');
			$row.append(
				'<td>\
					<table class="table table-stripped table-noborder w100p">\
					<thead><tr style="background: #deefc9">\
						<td colspan="2">' + group + '</td><td style="width:15px;">new</td>\
					</tr></thead></table>\
				</td>\
				<td style="vertical-align: top !important; width: 50px; text-align: right;">\
					<button type="button" class="btn btn-xs btn-danger del-related-group" data-id="' + group + '">\
					<i class="fa fa-times"></i> Remove</button>\
				</td>'
			);
			$that.preview.first('tbody').append($row);
		}
	};

	$(document).ready(function () {
		var seller_uid = $('#jform_seller_uid').val();

		if (seller_uid > 0) {
			// Input size for call/email
			var $phone = $('#jform_seller_phone');
			var $email = $('#jform_seller_email');

			$phone.css('width', Math.min(($phone.val() || '').length + 5, 240) + 'ex');
			$email.css('width', Math.min(($email.val() || '').length + 5, 240) + 'ex');

			// Edit links for call/email
			var $edit_btn = $('<a target="_blank">edit <i class="fa fa-share"></i> </a>')
				.attr('class', 'btn btn-xs btn-primary pull-left').css('margin-right', '10px')
				.attr('href', 'index.php?option=com_sellacious&view=user&layout=edit&id=' + seller_uid);

			$phone.parent().append($edit_btn.clone());
			$email.parent().append($edit_btn.clone());

			// Show / hide price blocks depending on selected option
			var $showPrice = $('#jform_seller_price_display').find('input[type="radio"]');

			var pricesFallback = $('#jform_prices_fallback').closest('div.row');
			var pricesProduct = $('#jform_prices_product').closest('div.row');
			var pricesVariant = $('#jform_prices_variants').closest('div.row');
			var askPriceForm = $('#jform_seller_query_form').closest('div.row');
			var sellerPhone = $phone.closest('div.row');
			var sellerEmail = $email.closest('div.row');

			$showPrice.change(function () {
				pricesFallback.hide();
				pricesProduct.hide();
				pricesVariant.hide();
				sellerPhone.hide();
				sellerEmail.hide();
				askPriceForm.hide();

				var checked = $showPrice.filter(':checked');

				if (checked.length) {
					switch (checked.val()) {
						case '0':
							pricesFallback.show();
							pricesProduct.show();
							pricesVariant.show();
							break;
						case '1':
							sellerPhone.show();
							break;
						case '2':
							sellerEmail.show();
							break;
						case '3':
							askPriceForm.show();
							break;
						default:
					}
				}
			}).triggerHandler('change');

			// Shipping config
			var $flatShip = $('#jform_seller_flat_shipping').find('input[type="radio"]');
			var flatFee = $('#jform_seller_shipping_flat_fee').closest('div.input-row');
			$flatShip.change(function () {
				var value = $flatShip.filter(':checked').val();
				value == 1 ? flatFee.removeClass('hidden') : flatFee.addClass('hidden');
			}).triggerHandler('change');

			// Allow / disallow returns and exchange of products by customer
			$('#jform_seller_return_days').change(function () {
				var allow = parseInt($(this).val());
				allow = isNaN(allow) ? 0 : allow;
				var ret = $('#jform_seller_return_tnc').closest('div.input-row');
				allow ? ret.removeClass('hidden') : ret.addClass('hidden');
			}).triggerHandler('change');

			$('#jform_seller_exchange_days').change(function () {
				var allow = parseInt($(this).val());
				allow = isNaN(allow) ? 0 : allow;
				var ret = $('#jform_seller_exchange_tnc').closest('div.input-row');
				allow > 0 ? ret.removeClass('hidden') : ret.addClass('hidden');
			}).triggerHandler('change');

			// Listing type new / used / refurbished
			var $iType = $('#jform_seller_listing_type').find('input[type="radio"]');

			$iType.change(function () {
				var value = $iType.filter(':checked').val();
				var $iCond = $('#jform_seller_item_condition').closest('div.input-row');
				value == 1 ? $iCond.addClass('hidden') : $iCond.removeClass('hidden');
			}).triggerHandler('change');
		}

		// E-Product delivery mode
		var $deliveryMode = $('#jform_seller_delivery_mode');
		var $eproductDelivery = $deliveryMode.find('input[type="radio"]');
		var message = '<div class="enterprise-message text text-danger padding-5">Only available with <b>Sellacious Enterprise</b></div>';
		$deliveryMode.closest('div.input-row').find('.controls').append(message);

		$eproductDelivery.change(function () {
			var value = $eproductDelivery.filter(':checked').val();
			var $iRow = $deliveryMode.closest('.controls').find('.enterprise-message');
			value === 'download' || value === 'none' ? $iRow.addClass('hidden') : $iRow.removeClass('hidden');
		}).triggerHandler('change');

		document.formvalidator.setHandler('primary_video', function(value, element) {
			var regex = /^(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(&\S+)?/;
			value.match(regex);
			return (RegExp.$3.indexOf('youtu') > -1 || RegExp.$3.indexOf('vimeo') > -1);
		});

		$(document).on('onMapChangeLocation', function (event, lat, lng) {
			var location = (Math.round(lat * 1000000) / 1000000) + ',' + (Math.round(lng * 1000000) / 1000000);
			$('#jform_basic_location').val(location);
		});

		$(document).on('OnMapGeoCode', function (event, address_components) {
			$.each(address_components, function(key, component) {
				if ($.inArray('sublocality_level_1', component.types) != -1) {
					$('#jform_basic_geolocation_locality').val(component.long_name);
				} else if ($.inArray('sublocality_level_2', component.types) != -1) {
					$('#jform_basic_geolocation_sublocality').val(component.long_name);
				} else if ($.inArray('locality', component.types) != -1) {
					$('#jform_basic_geolocation_city').val(component.long_name);
				} else if ($.inArray('administrative_area_level_2', component.types) != -1) {
					$('#jform_basic_geolocation_district').val(component.long_name);
				} else if ($.inArray('administrative_area_level_1', component.types) != -1) {
					$('#jform_basic_geolocation_state').val(component.long_name);
				} else if ($.inArray('country', component.types) != -1) {
					$('#jform_basic_geolocation_country').val(component.long_name);
				} else if ($.inArray('postal_code', component.types) != -1) {
					$('#jform_basic_geolocation_zip').val(component.long_name);
				}
			});
		});

		$('#jform_basic_location').on('change', function () {
			$(document).trigger('onMapChangeLatLng', [$(this).val().split(',')]);
		});
	});
})(jQuery);
