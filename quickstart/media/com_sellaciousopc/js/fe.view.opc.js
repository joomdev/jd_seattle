/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(function ($) {
	$(window).load(function () {
		var token = $('#formToken').attr('name');

		var $cartOPC = $('#cart-opc-container');
		var $opc = new SellaciousViewOPC;

		if ($cartOPC.length) {
			$opc.token = token;
			$opc.init('#cart-opc-container');
			$cartOPC.data('CartOPC', $opc);
		}

		$(".hide-section").find("button, select, a").attr("disabled", true);

		// Cart Section - Manage cart items and quantity
		var sectionCart = {

			name: 'cart',

			elements: {
				container: '#cart-opc-items',
				cart_items: '#cart-items',
				cart_folded: '#cart-items-folded'
			},

			options: {modal: false},

			element: function (name) {
				return this.container.find(this.elements[name]);
			},

			setup: function ($opc, options) {
				var $this = this;
				$this.opc = $opc;

				if (options) {
					if (options.elements) {
						$.extend($this.elements, options.elements);
						delete options.elements;
					}
					$.extend($this.options, options);
				}
				$this.container = $($this.elements.container);

				$this.container

					.on('click', '.btn-clear-cart', function () {
						if (confirm(Joomla.JText._('COM_SELLACIOUSOPC_CART_CONFIRM_CLEAR_CART_ACTION_MESSAGE')))
							$this.clearCart();
					})

					.on('click', '.btn-remove-item', function () {
						var uid = $(this).data('uid');
						$this.remove(uid, function () {
							$this.options.sopc.refreshSections();
						});
						return false;
					})

					.on('change', '.item-quantity', function () {
						var old = $(this).data('value');

						var quantity = parseInt($(this).val());
						quantity = isNaN(quantity) || quantity < 1 ? 1 : quantity;
						$(this).val(quantity);
						if (quantity != old) $this.setQuantity($(this), quantity, function () {
							$this.options.sopc.refreshSections();
						});

						return false;
					})

					.on('click', '.btn-refresh', function () {
						$this.showCart();
					})

					.on('click', '.btn-edit', function () {
						// Do not call self sectionIn directly as there may be some cleanup required
						$this.opc.navSection($this.name);
					});
			},

			sectionIn: function () {
				var $this = this;
				$this.showCart();
			},

			remove: function (uid, callback) {
				var $this = this;
				if ($this.ajax) $this.ajax.abort();
				var data = {
					option: 'com_sellaciousopc',
					task: 'opc.removeItemAjax',
					format: 'json',
					uid: uid
				};
				var paths = Joomla.getOptions('system.paths', {});
				var base = paths.base || paths.root || '';
				data[$this.opc.token] = 1;
				$this.ajax = $.ajax({
					url: base + '/index.php',
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
						$(document).trigger('cartUpdate', ['remove', {uid: uid}]);
						$this.showCart(function () {
							$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
						});
						if (typeof callback == 'function') callback(response);
					} else {
						$this.opc.renderMessages({warning: [response.message]}, $this.container)
					}
				}).fail(function (jqXHR) {
					$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
					console.log(jqXHR.responseText);
				});
			},

			clearCart: function () {
				var $this = this;
				if ($this.ajax) $this.ajax.abort();
				var data = {
					option: 'com_sellaciousopc',
					task: 'opc.clearAjax',
					format: 'json'
				};
				var paths = Joomla.getOptions('system.paths', {});
				var base = paths.base || paths.root || '';
				data[$this.opc.token] = 1;
				$this.ajax = $.ajax({
					url: base + '/index.php',
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
						$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
						$(document).trigger('cartUpdate', ['clear']);
						window.location.reload(true);
					} else {
						$this.opc.renderMessages({warning: [response.message]}, $this.container)
					}
				}).fail(function (jqXHR) {
					$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
					console.log(jqXHR.responseText);
				});
			},

			showCart: function (callback) {
				var $this = this;
				if ($this.ajax) $this.ajax.abort();

				$this.element('cart_folded').empty().addClass('hidden');
				$this.element('cart_items').removeClass('hidden');

				var data = {
					option: 'com_sellaciousopc',
					task: 'opc.getItemsHtmlAjax',
					format: 'json',
					modal: $this.options.modal ? 1 : 0,
					readonly: 0
				};
				var paths = Joomla.getOptions('system.paths', {});
				var base = paths.base || paths.root || '';
				data[$this.opc.token] = 1;
				$this.ajax = $.ajax({
					url: base + '/index.php',
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
					if (response.status == 1031 || response.status != 1) {
						$this.element('cart_items').html('<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
							'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>');
						// Not logged in or a failure
						$this.opc.renderMessages({warning: [response.message]}, $this.container)
					} else {
						// HTML Returned
						$this.element('cart_items').html(response.data).find('.hasTooltip').tooltip();
						if (typeof callback == 'function') callback(response);
					}
				}).fail(function (jqXHR) {
					$this.element('cart_items').html('<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
						'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>');
					$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
					console.log(jqXHR.responseText);
				});
			},

			setQuantity: function ($element, value, callback) {
				var $this = this;
				var uid = $element.data('uid');
				if ($this.ajax) $this.ajax.abort();
				var data = {
					option: 'com_sellaciousopc',
					task: 'opc.setQuantityAjax',
					format: 'json',
					uid: uid,
					quantity: value
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
						$(document).trigger('cartUpdate', ['quantity', {uid: uid, quantity: value}]);
						$this.showCart(function () {
							$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
						});
						if (typeof callback == 'function') callback(response);
					} else {
						$element.val($element.data('value'));
						$this.opc.renderMessages({warning: [response.message]}, $this.container);
					}
				}).fail(function (jqXHR) {
					$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
					console.log(jqXHR.responseText);
				});
			},
		}

		var $cartModal = $('#modal-cart');

		if ($cartModal.length) {
			var oo = new SellaciousViewOPC;
			oo.token = token;
			oo.initCart('#modal-cart .modal-body', sectionCart, true, $opc);
			$cartModal.find('.modal-body').html('<div id="cart-items"></div>');
			$cartModal.data('CartModal', oo);
			$(document).on("click", '.btn-cart-modal', function () {
				var o = $cartModal.data('CartModal');
				o.navSection('cart');
				$cartModal.modal('show')
			});
		} else {
			$('.btn-cart-modal').addClass('hidden');
		}
	});
});

