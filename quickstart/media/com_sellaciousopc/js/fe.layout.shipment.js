/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// Shipment Section - Select shipment methods and any forms that may be required for the selected method of order shipment
jQuery(function ($) {
	var sectionShipment = {

		name: 'shipment',

		elements: {
			container: '#cart-opc-shippingform',
			form_editor: '#shippingform-editor',
			form_folded: '#shippingform-folded',
			shipping_form: 'form#shipment-form',
			shipment_form: '.shipment-method-form',
			form_viewer: '#shippingform-response'
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

				.on('change', '.select-shipment', function () {
					var $this = $opc;
					var uid = $(this).data('uid');
					var val = $(this).val();
					var prefix = 'shipment_form' + (uid ? '_' + uid : '');
					var elc = prefix;
					var eli = prefix + '_' + val;

					$this.container.find('.' + elc).removeClass('active');
					$this.container.find('#' + eli).addClass('active');

					var $form = $(this).closest("form");
					$form.attr("data-form-changed", true);
				})

				.on("focusin, click", function (event) {
					$this.opc.focusSection(event, $this);
				})

				.on("focus", "input, textarea, select", function (event) {
					$this.opc.focusSection(event, $this);
				})

				.on('change', '.shipment_form select:not(.select-shipment), .shipment_form input[type!=\'hidden\'], .shipment_form textarea, #shipment-form select:not(.select-shipment), #shipment-form input[type!=\'hidden\'], #shipment-form textarea', function () {
					var $form = $(this).closest("form");
					$form.attr("data-form-changed", true);
				})

				.on('click', '.btn-edit', function () {
					// Do not call self sectionIn directly as there may be some cleanup required
					$this.opc.navSection($this.name);
				});
		},

		sectionIn: function () {
			var $this = this;
			$this.container.removeClass('hidden');
			$this.showForms();
		},

		sectionOut: function () {
		},

		saveSection: function (e) {
			var $this = this;

			var form = $this.element('shipping_form');

			if (form.data("form-changed")) {
				$this.saveForm(function () {
					form.attr("data-form-changed", false);

					$this.opc.refreshSections();
				});
			}
		},

		showForms: function (callback) {
			var $this = this;

			var $container = $this.element('form_editor');
			$container.find('.hasTooltip').tooltip();
			$container.find('.hasSelect2').select2();
			$container.find('.hasPopover').popover({trigger: 'hover'});
			$container.find('.hasCalendar').dcalendarpicker();

			if (typeof callback == 'function') callback();
		},

		saveForm: function (callback) {
			var $this = this;
			var $form = $this.element('shipping_form');
			var $formData = $form.serializeArray();

			// Just in case the form has some file inputs
			var $fileData = new FormData();
			$form.find('input[type="file"]').each(function () {
				if (this.files && this.files.length) {
					$fileData.append($(this).attr('name'), this.files[0]);
				}
			});
			$.each($formData, function (index, input) {
				$fileData.append(input.name, input.value);
			});
			$fileData.append($this.opc.token, 1);

			if ($this.ajax) $this.ajax.abort();
			$this.ajax = $.ajax({
				url: 'index.php?option=com_sellaciousopc&task=opc.saveShippingFormAjax&format=json',
				type: 'POST',
				data: $fileData,
				cache: false,
				dataType: 'json',
				// Don't process the files
				processData: false,
				// Set content type to false as jQuery will tell the server its a query string request
				contentType: false,
				beforeSend: function () {
					$this.opc.overlay($this);
				},
				complete: function () {
					$this.opc.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status == 1) {
					$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.element('form_viewer').html(response.data);
					if (typeof callback == 'function') callback();
				} else if (response.message == '') {
					if (typeof callback == 'function') callback();
				} else {
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
					if (typeof callback == 'function') callback();
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	}

	$(window).on('checkoutReady', function (e, $opc) {
		$opc.addSection($.extend({}, sectionShipment), 'Shipment', {elements: {container: '#cart-opc-shippingform'}});
		$opc.navSection(sectionShipment.name);
	});
});
