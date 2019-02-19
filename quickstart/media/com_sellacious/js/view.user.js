/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var SellaciousViewUser = {
	Address: function () {
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

	SellaciousViewUser.Address.prototype = {
		init: function (selector, token, controller) {
			var $that = this;
			$that.token = token;
			$that.element = $(selector);
			$that.controller = controller || 'user';

			$that.hideForm();

			// Don't show initially opened @2015-12-06@
			/*
			if ($that.element.find('#addresses-list').find('tbody > tr').length == 0) {
				$that.getAddress(0, function (data) {
					$that.showForm(data);
				});
			}
			*/

			$that.element.on('click', '#btn-save-address', function () {
				$that.saveAddress(function (id) {
					$that.hideForm();
					$that.getAddress(id, function (data) {
						$that.addRow(data);
					});
				});
			});

			$that.element.on('click', '#btn-apply-address', function () {
				$that.saveAddress(function (id) {
					// do not hide form, but update rows
					$that.getAddress(id, function (data) {
						$that.addRow(data);
					});
				});
			});

			$that.element.on('click', '#btn-close-address', function () {
				$that.hideForm();
				Joomla.removeMessages();
			});

			$that.element.on('click', '.edit-address', function () {
				var id = $(this).data('id');
				Joomla.renderMessages({info: [Joomla.JText._('COM_SELLACIOUS_USER_LOAD_WAIT_ADDRESS', 'Please wait while we load the address&hellip;')]});
				$that.getAddress(id, function (data) {
					$that.showForm(data);
				});
			});

			$that.element.on('click', '.copy-address', function () {
				Joomla.renderMessages({info: [Joomla.JText._('COM_SELLACIOUS_USER_LOAD_WAIT_ADDRESS', 'Please wait while we load the address&hellip;')]});
				$that.getAddress($(this).data('id'), function (data) {
					data.id = 0;
					data.images = [];
					$that.showForm(data);
				});
			});

			$that.element.on('click', '.delete-address', function (e) {
				var $target = $(e.target).is('.delete-address') ? $(e.target) : $(e.target).closest('.delete-address');
				if ($target.data('confirm')) {
					$target.data('confirm', false);
					$target.html('<i class="fa fa-times"></i> Delete');
					Joomla.renderMessages({info: ['Please wait while we attempt to remove the selected address&hellip;']});
					var id = $(this).data('id');
					$that.removeAddress(id, function () {
						$('#address-row-' + id).fadeOut('slow').remove();
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

		saveAddress: function (callback) {
			var $that = this;
			var form = $('<form/>').append($that.element.clone());

			if (!document.formvalidator.isValid(form)) {
				// Joomla doesn't handle the cloned form elements well, so we do it here
				var message = Joomla.JText._('JLIB_FORM_FIELD_INVALID', 'Invalid field: ');
				var error = [];
				var errors = $(form).find("input.invalid, textarea.invalid, select.invalid, fieldset.invalid, button.invalid");
				for (var i = 0; i < errors.length; i++) {
					$('#' + errors[i].id).addClass('invalid');
					var $label = $that.element.find('label[for="' + errors[i].id + '"]').addClass('invalid');
					if ($label.text() !== 'undefined') {
						error[i] = message + $label.text().replace("*", "");
					}
				}
				Joomla.renderMessages({error: error});
				return;
			}

			var data = form.serializeObject();

			data['jform']['address']['user_id'] = $('#jform_id').val();
			data['option'] = 'com_sellacious';
			data['task'] = $that.controller + '.saveAddressAjax';
			data[$that.token] = 1;

			$.ajax({
				url     : 'index.php',
				type    : 'post',
				dataType: 'json',
				data    : data
			}).done(function (response) {
				if (response.state == 1) {
					$('#jform_address_id').val(response.data);
					Joomla.renderMessages({success: [response.message]});
					// We need to update the list also after save
					if (typeof callback == 'function')
						callback(response.data);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				console.log(response.responseText);
			});
		},

		getAddress: function (id, callback) {
			var $that = this;
			var data = {};
			data['option'] = 'com_sellacious';
			data['task'] = $that.controller + '.getAddressAjax';
			data[$that.token] = 1;
			data['id'] = id;

			$.ajax({
				url     : 'index.php',
				type    : 'post',
				dataType: 'json',
				data    : data
			}).done(function (response) {
				if (response.state == 1) {
					Joomla.removeMessages();
					if (typeof callback == 'function')
						callback(response.data);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				Joomla.renderMessages({warning: ['Failed to load the selected address due to some server error.']});
				console.log(response.responseText);
			});
		},

		removeAddress: function (id, callback) {
			var $that = this;
			var data = {};
			data['option'] = 'com_sellacious';
			data['task'] = $that.controller + '.deleteAddressAjax';
			data[$that.token] = 1;
			data['id'] = id;

			$.ajax({
				url     : 'index.php',
				type    : 'post',
				dataType: 'json',
				data    : data
			}).done(function (response) {
				if (response.state == 1) {
					Joomla.renderMessages({success: [response.message]});
					if (typeof callback == 'function')
						callback(response.data);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				Joomla.renderMessages({warning: ['Failed to remove selected address due to some server error.']});
				console.log(response.responseText);
			});
		},

		hideForm: function () {
			var $that = this;
			$that.element.find('#add-address').show();
			var fSet = $that.element.find('fieldset');
			fSet.hide();
			fSet.find('[required],.required').removeAttr('required').removeAttr('aria-required')
				.removeClass('required').addClass('required-off')
				.val('NULL'); // Setting a dummy value will suppress any 'required' check at the server side
		},

		showForm: function (data) {
			var $that = this;
			$that.element.find('#add-address').hide();
			var $fieldset = $that.element.find('fieldset');
			$fieldset.find('.required-off')
				.attr('required', 'required').attr('aria-required', 'true')
				.removeClass('required-off').addClass('required');
			$fieldset.find('[id^="jform_address_"]').val(''); // Resetting values to blank
			$fieldset.find('.invalid').removeClass('invalid');

			if (data) $.each(data, function (key, value) {
				var $input = $fieldset.find('#jform_address_' + key);
				if ($input.data('select2')) {
					// The chaining dependency of the inputs may affect the behavior if ajax response does not come in
					// correct order of hierarchy. But so far it appears that it is not affecting anything during
					// initial selection. Therefore ignored for now.
					$input.select2('val', value).trigger('change');
				} else {
					// KeyUp trigger used to update color picker's display only
					$input.val(value).trigger('change').trigger('keyup');
				}
			});

			$fieldset.show();
		},

		addRow: function (data) {
			var $that = this;

			var $row = $that.element.find('#address-row-' + data.id);
			var $new_row = $(data.html);

			$row.length ? $row.replaceWith($new_row) : $that.element.find('#addresses-list').find('tbody').append($new_row);

			// todo: currently we do not have primary address concept
			// This was intended to be a quick hack, but turned out to offer more feature like new (first) address
			if ($that.element.find('.address-primary').filter(':checked').length === 0) {
				$new_row.find('.address-primary').prop('checked', true);
			}
		}
	};

	$(document).ready(function () {
		document.formvalidator.setHandler('mobile', function (value) {
			return /^(\+\d{1,3}[- ]?)?\d{10}$/.test(value) || /NULL/.test(value);
		});

		$(document).on('onMapChangeLocation', function (event, lat, lng) {
			var location = (Math.round(lat * 1000000) / 1000000) + ',' + (Math.round(lng * 1000000) / 1000000);
			$('#jform_seller_store_location').val(location);
		});

		$('#jform_seller_store_location').on('change', function () {
			$(document).trigger('onMapChangeLatLng', [$(this).val().split(',')]);
		});
	});
})(jQuery);
