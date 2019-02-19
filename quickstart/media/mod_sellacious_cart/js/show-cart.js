/**
 * @version     1.6.1
 * @package     Sellacious Cart Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(function ($) {

	$(document).ready(function () {

		var getToken = function () {
			var token = '';
			$('input[type="hidden"][name]').each(function () {
				var name = $(this).attr('name');
				var value = $(this).val();
				if (value == 1 && name.length == 32) token = name;
			});
			return token;
		};

		var loadModuleCart = function () {
			var data = {
				option: 'com_sellacious',
				task: 'cart.getCartAjax',
				format: 'json'
			};
			var paths = Joomla.getOptions('system.paths', {});
			var base = paths.base || paths.root || '';
			var token = getToken();
			data[token] = 1;
			$.ajax({
				url: base + '/index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data
			}).done(function (response) {
				if (response.status == 1) {
					setHTML(response.data, response.total);
				} else {
					$('.mod-products-list').html(response.message);
				}
			}).fail(function (jqXHR) {
				console.log(jqXHR.responseText);
			});
		};

		var setHTML = function (items, total) {
			$('.mod-grand-total').html(total);
			$('.mod-total-products').html(items.length);
			var cartItems = $('.mod-products-list');
			cartItems.html('');

			$.each(items, function(k, v){
				var html = '';

				html += '<div class="container">';
				html += '<a href="'+ v.link +'">';
				html += '<div class="prices" style="float: right;">';
				html +=  v.total;
				html += '</div>';
				html += '<div class="product_row"><span class="quantity">';
				html +=  v.quantity;
				html += '</span>&nbsp;x&nbsp;<span class="product_name">';
				html +=  v.title;
				html += '</span></div>';
				html += '</a>';
				html += '</div>';

				cartItems.append(html);
			});

			var cartAppend = '';
			cartAppend += '<div class="container"><div style="float: right">';
			if (items.length) {
			cartAppend += Joomla.JText._('MOD_SELLACIOUS_CART_PLUS_TAXES');
			}
			else {
				cartAppend += Joomla.JText._('MOD_SELLACIOUS_CART_EMPTY_CART_NOTICE');
			}
			cartItems.append('</div> </div>');

			cartItems.append(cartAppend);

			return true;
		};

		$(document).on('cartUpdate', function () {
			loadModuleCart();
		});

		loadModuleCart();

		// Initialize cart modal
		var $cartModal = $('#modal-cart');

		if ($cartModal.length && !$cartModal.data('CartModal')) {
			var oo = new SellaciousViewCartAIO;
			oo.token = $('#formToken').attr('name');
			oo.initCart('#modal-cart .modal-body', true);
			$cartModal.find('.modal-body').html('<div id="cart-items"></div>');
			$cartModal.data('CartModal', oo);
		}

		$('#btn-modal-cart').on('click', function () {
			var o = $cartModal.data('CartModal');
			o.token = $(this).data('token');
			o.navStep('cart');
			$cartModal.modal('show');
		});

		$('.mod-cart-ul').addClass('hidden');

		$(".mod-sellacious-cart").hover(function(){
			$('.mod-cart-ul').removeClass('hidden');
		},function(){
			$('.mod-cart-ul').addClass('hidden');
		});
	});
});
