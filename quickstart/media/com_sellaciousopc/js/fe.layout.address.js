/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// Address Section - Manage Addresses and Ship To & Bill To choices
jQuery(function ($) {
	var sectionAddress = {
		name: 'address',

		formFields: {},

		elements: {
			container: '#cart-opc-address',
			editor: '#address-editor',
			viewer: '#address-viewer',
			viewer_billing: '#address-billing-text',
			viewer_shipping: '#address-shipping-text',
			boxes_container: '#address-items',
			modals_container: '#address-modals',
			address_forms: '#address-forms',
			input_billing: '#address-billing',
			input_shipping: '#address-shipping',
			box_single_class: '.address-item',
			btn_ship_here: '.btn-ship-here',
			btn_bill_here: '.btn-bill-here',
			modal_form: '.address-form-content',
			btn_add_new: '.btn-add-address',
			billing_address_form: '.billing_address_form',
			shipping_address_form: '.shipping_address_form',
		},

		element: function (name) {
			return this.container.find(this.elements[name]);
		},

		setup: function ($opc, elements) {
			var $this = this;
			$this.opc = $opc;

			$.extend($this.elements, elements);
			$this.container = $($this.elements.container);

			$this.container

				.on('click', '.btn-ship-here', function () {
					var address_id = $(this).data('id');

					$this.element('input_shipping').val(address_id);
					$this.element('btn_ship_here').removeClass('active').removeClass('btn-success');

					$(this).addClass('btn-success active');
					$this.element('editor').addClass('has-shipping');

					var billTo = $this.element('input_billing').val();
					var shipTo = $this.element('input_shipping').val();

					billTo = parseInt(billTo);
					billTo = isNaN(billTo) ? 0 : billTo;
					shipTo = parseInt(shipTo);
					shipTo = isNaN(shipTo) ? 0 : shipTo;

					$this.setSelected(billTo, shipTo, function (response) {
						$this.opc.refreshSections();
					});
				})

				.on('click', '.btn-bill-here', function () {
					var address_id = $(this).data('id');

					$this.element('input_billing').val(address_id);
					$this.element('btn_bill_here').removeClass('active').removeClass('btn-success');

					$(this).addClass('btn-success active');
					$this.element('editor').addClass('has-billing');

					var billTo = $this.element('input_billing').val();
					var shipTo = $this.element('input_shipping').val();

					billTo = parseInt(billTo);
					billTo = isNaN(billTo) ? 0 : billTo;
					shipTo = parseInt(shipTo);
					shipTo = isNaN(shipTo) ? 0 : shipTo;

					$this.setSelected(billTo, shipTo, function (response) {
						$this.opc.refreshSections();
					});
				})

				.on('click', '.remove-address', function () {
					var address_id = $(this).data('id');
					if (address_id && confirm(Joomla.JText._('COM_SELLACIOUSOPC_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE')))
						$this.remove(address_id);
				})

				.on('click', '.btn-save-address', function () {
					var formKey = $this.element('modal_form', true);
					var $form = $(this).closest('.modal').find(formKey);
					var data = $this.validate($form);

					if (data) $this.save(data, function () {
						$('#address-form-' + data.id).modal('hide');
						// Reset the form filled values in add new address
						if (data.id == 0) {
							var fields = $this.getFormFields();
							$.each(fields, function (fieldKey) {
								if (fieldKey != 'id') {
									var $field = $form.find('.address-' + fieldKey).filter(':input');
									$field.data('select2') ? $field.select2('val', '') : $field.val('');
								}
							});
						}

						$this.opc.refreshSections();
					});
				})

				.on("focusin, click", function (event) {
					$this.opc.focusSection(event, $this);
				})

				.on("focus", "input, textarea, select", function (event) {
					$this.opc.focusSection(event, $this);
				})

				.on("change", ".opc-address-form input[type!='hidden'], .opc-address-form textarea, .opc-address-form select", function(){
					var $form = $(this).closest("form");
					$form.attr("data-form-changed", true);
				})

				.on('change', '#same_as_ship', function () {
					var shipTo = $this.element('input_shipping').val();
					var billTo = 0;

					$this.element('shipping_address_form').find("form").attr("data-form-changed", true);

					if($(this).is(":checked"))
					{
						billTo = shipTo;
						$this.element('billing_address_form').addClass("hidden");
					}
					else
					{
						$this.element('billing_address_form').find("form").attr("data-form-changed", true);
						//$this.element('billing_address_form').find("input[name!='set_billing'], select, textarea").not(".address-country, .address-id").val("");
						$this.element('billing_address_form').removeClass("hidden");
					}

					$this.element('input_billing').val(billTo);

					billTo = parseInt(billTo);
					billTo = isNaN(billTo) ? 0 : billTo;
					shipTo = parseInt(shipTo);
					shipTo = isNaN(shipTo) ? 0 : shipTo;

					// $this.setSelected(billTo, shipTo, function (response) {
					// 	$this.opc.refreshSections();
					// });
				})

				.on('click', '.btn-edit', function () {
					// Do not call self sectionIn directly as there may be some cleanup required
					$this.opc.navSection($this.name);
				});
		},

		saveSection: function (e) {
			var $this = this;
			var forms = $this.container.find("form[data-form-changed='true']");

			if (forms.length) {
				var form = $(forms[0]);
				var data = $this.validate(form);

				if(form.find("input[name='set_shipping']").length)
				{
					data.set_shipping = form.find("input[name='set_shipping']").val();
				}

				if(form.find("input[name='set_billing']").length)
				{
					data.set_billing = form.find("input[name='set_billing']").val();
				}

				data.same_as_ship = $("#same_as_ship:checked").val();

				if (data) $this.save(data, function () {
					form.attr("data-form-changed", false);

					if (forms[1] != undefined) {
						var form2 = $(forms[1]);
						var data = $this.validate(form2);

						if(form2.find("input[name='set_shipping']").length)
						{
							data.set_shipping = form2.find("input[name='set_shipping']").val();
						}

						if(form2.find("input[name='set_billing']").length)
						{
							data.set_billing = form2.find("input[name='set_billing']").val();
						}

						data.same_as_ship = $("#same_as_ship:checked").val();

						if (data) $this.save(data, function () {
							form2.attr("data-form-changed", false);

							$this.opc.refreshSections();
						}, 0);
					} else {
						$this.opc.refreshSections();
					}
				}, 0);

			}
		},

		sectionIn: function (options) {
			var $this = this;
			$this.container.removeClass('hidden');

			var loadAddressForms = (options.loadAddressForms != undefined) ? options.loadAddressForms : 1;

			$this.loadEditor(function(){
			});
		},

		sectionOut: function () {
		},

		loadEditor: function (callback) {
			var $this = this;

			$this.element('boxes_container').find('.hasTooltip').tooltip();
			$this.element('boxes_container').find('.hasSelect2').select2();
			$this.element('boxes_container').popover({trigger: 'hover'});

			$this.element('modals_container').find('.hasTooltip').tooltip();
			$this.element('modals_container').find('.hasSelect2').select2();
			$this.element('modals_container').popover({trigger: 'hover'});

			$this.element('address_forms').find('.hasTooltip').tooltip();
			$this.element('address_forms').find('.hasSelect2').select2();
			$this.element('address_forms').popover({trigger: 'hover'});

			var shipTo = $this.element('input_shipping').val();
			var billTo = $this.element('input_billing').val();

			$this.element('editor').removeClass('has-shipping');
			$this.element('editor').removeClass('has-billing');

			if (shipTo) $this.element('btn_ship_here').each(function () {
				if ($(this).data('id') == shipTo) {
					$(this).addClass('btn-success active');
					$this.element('editor').addClass('has-shipping');
				}
			});

			if (billTo) $this.element('btn_bill_here').each(function () {
				if ($(this).data('id') == billTo) {
					$(this).addClass('btn-success active');
					$this.element('editor').addClass('has-billing');
				}
			});

			if (typeof callback == 'function') callback();
		},

		remove: function (address_id) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellaciousopc',
				task: 'user.removeAddressAjax',
				id: address_id
			};
			data[$this.opc.token] = 1;
			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.status == 1031) {
					// Not logged in
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				} else if (response.status == 1033) {
					// Removed
					$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.opc.refreshSections();
				} else {
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		getFormFields: function () {
			var $this = this;
			// Optimised to calculate only once
			if (Object.keys($this.formFields).length == 0) {
				var forms = $this.element('modal_form');
				if (forms.length) {
					forms.eq(0).find('[class*=" address-"],[class^="address-"]').each(function () {
						var field = $(this).attr('class').match(/address-([\w]+)/i);
						if (field) $this.formFields[field[1]] = $(this).is('.required') ? 1 : 0;
					});
				}
			}
			return $this.formFields;
		},

		validate: function ($form) {
			var $this = this;
			var data = {};
			var invalid = {};
			var valid = true;

			var fields = $this.getFormFields();

			$.each(fields, function (fieldKey, required) {
				var field = $form.find('.address-' + fieldKey);
				var field_input = field.is('fieldset') ? field.find('input:checked') : field.filter(':input');
				var labels = $(field).closest('tr').find('label');
				var value = $.trim(field_input.val());
				if (required && value == '') {
					field_input.addClass('invalid');
					labels.addClass('invalid');
					valid = false;
					invalid[fieldKey] = value;
				} else {
					field_input.removeClass('invalid');
					labels.removeClass('invalid');
					data[fieldKey] = value;
				}
			});
			if (valid)
				return data;
			$this.opc.renderMessages({warning: ['Invalid or incomplete address form.']}, $this.container);
			console.log('Invalid form:', data, invalid);
			return false;
		},

		save: function (address, callback, loadAddressForms) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();

			if(loadAddressForms == undefined) loadAddressForms = 1;

			var billTo = $this.element('input_billing').val();
			var shipTo = $this.element('input_shipping').val();

			var data = {
				option: 'com_sellaciousopc',
				task: 'opc.saveAddressAjax',
				format: 'json',
				address: address,
				billing: billTo,
				shipping: shipTo,
				id: address.id
			};
			data[$this.opc.token] = 1;
			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.status == 1031) { // Not logged in
					$this.container.find('#address-form-' + address.id).modal('hide');
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				} else if (response.status == 1035) { // Saved
					$this.container.find('#address-form-' + address.id).modal('hide');
					$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);

					$this.element('input_billing').val(response.data.billing);
					$this.element('input_shipping').val(response.data.shipping);

					if (typeof callback == 'function') callback();
				} else {
					alert(response.message);
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		setSelected: function (bill_to, ship_to, callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellaciousopc',
				task: 'opc.setAddressesAjax',
				format: 'json',
				billing: bill_to,
				shipping: ship_to
			};
			data[$this.opc.token] = 1;
			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.status == 1) {
					var $billTo = $this.element('btn_bill_here').filter('.active').closest($this.elements.box_single_class);
					var $shipTo = $this.element('btn_ship_here').filter('.active').closest($this.elements.box_single_class);

					var $billToViewer = $this.element('viewer_billing');
					var $shipToViewer = $this.element('viewer_shipping');
					var fields = $this.getFormFields();

					$.each(fields, function (fieldKey, required) {
						if ($billTo.length) $billToViewer.find('.address_' + fieldKey).text($billTo.find('.address_' + fieldKey).text());
						if ($shipTo.length) $shipToViewer.find('.address_' + fieldKey).text($shipTo.find('.address_' + fieldKey).text());
					});

					$billToViewer.toggleClass('hidden', !$billTo.length);
					$shipToViewer.toggleClass('hidden', !$shipTo.length);

					$this.element('input_billing').val(response.data.billing);
					$this.element('input_shipping').val(response.data.shipping);

					$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);

					if (typeof callback == 'function') callback(response);
				} else {
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	};

	$(window).on('checkoutReady', function (e, $opc) {
		$opc.addSection($.extend({}, sectionAddress), 'Address', {elements: {container: '#cart-opc-address'}});
		$opc.navSection(sectionAddress.name);
	});
});
