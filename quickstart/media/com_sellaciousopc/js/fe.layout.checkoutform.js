/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(function ($) {
	// Checkout questions Section - Miscellaneous questions or even customer survey can be put here.
	var sectionCheckoutForm = {

		name: 'checkoutform',

		elements: {
			container: '#cart-opc-checkoutform',
			form_editor: '#checkoutform-editor',
			form: 'form#checkoutform-container',
			viewer: '#checkoutform-folded'
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

				.on('change', $this.element('form'), function () {
					var $form = $this.element('form');
					$form.attr("data-form-changed", true);
				})

				.on('click', '.btn-edit', function () {
					// Do not call self sectionIn directly as there may be some cleanup required
					$this.opc.navSection($this.name);
				})
		},

		sectionIn: function () {
			var $this = this;
			$this.showForm();
		},

		sectionOut: function () {
		},

		saveSection: function (e) {
			var $this = this;

			var form = $this.element('form');

			if (form.data("form-changed")) {
				$this.saveForm(function () {
					form.attr("data-form-changed", false);

					$this.opc.refreshSections();
				});
			}
		},

		showForm: function (callback) {
			var $this = this;

			// Have some HTML
			var $container = $this.element('form');

			$container.find('.hasTooltip').tooltip();
			$container.find('.hasSelect2').select2();
			$container.find('.hasPopover').popover({trigger: 'hover'});
			$container.find('.hasCalendar').dcalendarpicker();
			$this.element('form_editor').removeClass('hidden');
			if (typeof callback == 'function') callback(response);
		},

		saveForm: function (callback) {
			var $this = this;

			var $form = $this.element('form');
			var $formData = $form.serializeArray();

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
				url: 'index.php?option=com_sellaciousopc&task=opc.saveCheckoutFormAjax&format=json',
				type: 'POST',
				data: $fileData,
				cache: false,
				dataType: 'json',
				processData: false, // Don't process the files
				contentType: false,  // Set content type to false as jQuery will tell the server its a query string request
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.status == 1) {
					$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.element('viewer').html(response.data).removeClass('hidden');
					if (typeof callback == 'function') callback(response);
				} else if (response.message == '') {
					if (typeof callback == 'function') callback(response);
				} else {
					$this.opc.refreshSections($this);
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	}

	$(window).on('checkoutReady', function (e, $opc) {
		$opc.addSection($.extend({}, sectionCheckoutForm), 'Questions', {elements: {container: '#cart-opc-checkoutform'}});
		$opc.navSection(sectionCheckoutForm.name);
	});
});
