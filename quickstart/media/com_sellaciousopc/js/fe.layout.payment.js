/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(function ($) {
	// Payment Section - Customer to make payment for the order
	var sectionPayment = {

		name: 'payment',

		elements: {
			container: '#cart-opc-payment',
			forms_container: '#payment-forms'
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

				.on("focusin, click", function (event) {
					$this.opc.focusSection(event, $this);
				})

				.on("focus", "input, textarea, select", function (event) {
					$this.opc.focusSection(event, $this);
				})

				.on('change', '.select-payment', function () {
					var value = $(this).val();

					$(".payment-method").removeClass("hidden").addClass("hidden");
					$("input[name='jform[method_id]']").attr("disabled", true);

					$("#payment-method-" + value).removeClass("hidden");
					$("#jform_method_" + value).attr("disabled", false);

					$this.element('forms_container').attr("data-form-changed", true);
				});
		},

		sectionIn: function () {
			var $this = this;
			$this.container.removeClass('hidden');

			$this.showOptions();
		},

		sectionOut: function () {
		},

		saveSection: function (e) {
			var $this = this;

			var form = $this.element('forms_container');

			if (form.data("form-changed")) {
				$this.saveForm(function () {
					form.attr("data-form-changed", false);

					$this.opc.refreshSections();
				});
			}
		},

		showOptions: function () {
			var $this = this;

			$this.element('forms_container').find('.hasTooltip').tooltip();
		},

		saveForm: function (callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();

			var data = {
				option: 'com_sellaciousopc',
				task: 'opc.savePaymentSelection',
				format: 'json',
				payment_id: $('.select-payment:checked').val()
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
					$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
					if (typeof callback == 'function') callback(response);
				} else if (response.message == '') {
					if (typeof callback == 'function') callback(response);
				} else {
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	}

	$(window).on('checkoutReady', function (e, $opc) {
		$opc.addSection($.extend({}, sectionPayment), 'Payment', {elements: {container: '#cart-opc-payment'}});
		$opc.navSection(sectionPayment.name);
	});
});
