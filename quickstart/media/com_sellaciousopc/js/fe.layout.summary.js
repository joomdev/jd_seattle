/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(function ($) {
	// Summary Section - Summary of the cart to show how and what for the customer is going to be charged
	var sectionSummary = {

		name: 'summary',

		elements: {
			container: '#cart-opc-summary',
			cart_items: '#summary-items',
			cart_folded: '#summary-folded',
			input_coupon: 'input.coupon-code'
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

				.on('click', '.btn-edit', function () {
					// Do not call self sectionIn directly as there may be some cleanup required
					$this.opc.navSection($this.name);
				})

				.on('click', '.btn-next', function () {
					var valid = $this.opc.validateCart();

					if (!valid) {
						$this.opc.renderMessages({warning: ['Please provide valid details on all sections.']}, $this.container);
						return false;
					}

					$this.foldSummary(function () {
						var selPayment = $(".select-payment:checked").val();

						var $form = $("#payment-method-" + selPayment).find("form");
						var validData = $this.validate($form);
						if (validData == false) return;

						// If order is already place but we are stuck here for payment, allow graceful bypass without breaking.
						if ($this.opc.order_id > 0) {
							$this.opc.renderMessages({success: [Joomla.JText._('COM_SELLACIOUS_CART_REDIRECT_WAIT_MESSAGE')]}, $this.container);
							$this.executePayment(validData);
						} else {
							// Do not assign data to cart, hold it - place order - call payment with this data
							$this.placeOrder(function (response) {
								$this.opc.order_id = response.data;
								$this.executePayment(validData);
							});
						}
					});
				})

				.on("focusin, click", function (event) {
					$this.opc.focusSection(event, $this);
				})

				.on("focus", "input, textarea, select", function (event) {
					$this.opc.focusSection(event, $this);
				})

				.on('click', '.shoprule-info-toggle', function () {
					var uid = $(this).data('uid');
					$(this).find('i').toggleClass('fa-plus-square-o').toggleClass('fa-minus-square-o');
					$('.' + uid + '-info').toggleClass('hidden');
					return false;
				})

				.on('click', '.btn-apply-coupon', function () {
					var $input = $this.element('input_coupon');
					var code = $input.is('.readonly') ? '' : $input.val();
					$this.setCoupon(code);
				});
		},

		sectionIn: function () {
			var $this = this;
			$this.container.removeClass('hidden');
			$this.showSummary();
		},

		sectionOut: function () {
		},

		saveSection: function (e) {
		},

		showSummary: function (callback) {
			var $this = this;

			$this.element('cart_items').find('.hasTooltip').tooltip();
			if (typeof callback == 'function') callback();
		},

		setCoupon: function (code) {
			var $this = this;
			var data = {
				option: 'com_sellaciousopc',
				task: 'opc.setCouponAjax',
				format: 'json',
				code: code
			};
			data[$this.opc.token] = 1;

			if ($this.ajax) $this.ajax.abort();
			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.opc.overlay($this);
				},
				complete: function () {
					$this.opc.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status == 1) {
					$this.showSummary(function () {
						$this.opc.refreshSections();
						$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
					});
				} else {
					alert(response.message);
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		foldSummary: function (callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellaciousopc',
				task: 'opc.getSummaryAjax',
				format: 'json'
			};

			var email = $('#login_email').val();
			if (email) {
				data.email = email;
			}

			data[$this.opc.token] = 1;
			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.opc.overlay($this);
				},
				complete: function () {
					$this.opc.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status == 1) {
					// Values Returned
					var cart = response.data;
					$this.opc.hash = cart.hash;
					$this.opc.setToken(cart.token);

					if (typeof callback == 'function') callback(response);
				} else if (response.status == 1031) {
					// Not logged in
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				} else {
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		validate: function ($form) {
			var $this = this;
			var $sysMsg = $('#system-message-container').empty();

			if (!document.formvalidator.isValid($form[0])) {
				$this.opc.renderMessages({}, $this.container);
				$this.container.find('.opc-message-container').html($sysMsg.html());
				$sysMsg.empty();
				return false;
			}
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

			return $fileData;
		},

		placeOrder: function (callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellaciousopc',
				task: 'opc.placeOrderAjax',
				format: 'json',
				hash: $this.opc.hash
			};
			data[$this.opc.token] = 1;
			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.opc.overlay($this);
				},
				complete: function () {
					$this.opc.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status == 1) {
					// Do not hide the message automatically
					$this.opc.renderMessages({success: [response.message]}, $this.container);
					if (typeof callback == 'function') callback(response);
				} else if (response.status == 1031 || response.status == 1041) {
					// Not logged in or Hash mismatch
					$this.opc.renderMessages({warning: [response.message]}, $this.container);
				} else {
					var msg = Joomla.JText._('COM_SELLACIOUS_CART_ORDER_PAYMENT_INIT_FAILURE');
					$this.opc.renderMessages({warning: [response.message, msg]}, $this.container);
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		executePayment: function ($data) {
			var $this = this;
			$data.append('id', $this.opc.order_id);
			if ($this.ajax) $this.ajax.abort();
			$this.ajax = $.ajax({
				url: 'index.php?option=com_sellacious&task=order.setPaymentAjax',
				type: 'POST',
				data: $data,
				cache: false,
				dataType: 'json',
				processData: false, // Don't process the files
				contentType: false,  // Set content type to false as jQuery will tell the server its a query string request
				beforeSend: function () {
					$this.opc.overlay($this);
				},
				complete: function () {
					$this.opc.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status == 1) {
					window.location.href = response['redirect'];
				} else {
					$this.opc.renderMessages({warning: [response.message, Joomla.JText._('COM_SELLACIOUS_CART_REDIRECT_WAIT_MESSAGE')]}, $this.container);
					window.location.href = response['redirect'];
				}
			}).fail(function (xhr) {
				console.log(xhr.responseText);
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
			});
		}
	}

	$(window).on('checkoutReady', function (e, $opc) {
		$opc.addSection($.extend({}, sectionSummary), 'Review', {elements: {container: '#cart-opc-summary'}});
		$opc.navSection(sectionSummary.name);
	});
});
