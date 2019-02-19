/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
Joomla = window.Joomla || {};
Joomla.submitbutton = function (task, form) {
	Joomla.submitform(task, form);
};

jQuery(document).ready(function ($) {
	// Initialize cart modal
	var $cartModal = $('#modal-cart');
	if ($cartModal.length) {
		var oo = new SellaciousViewCartAIO;
		oo.token = $('#formToken').attr('name');
		oo.initCart('#modal-cart .modal-body', true);
		$cartModal.find('.modal-body').html('<div id="cart-items"></div>');
		$cartModal.data('CartModal', oo);
	}

	$('.btn-toggle').click(function () {
		$(this).find('[data-toggle="true"]').toggleClass('hidden');
	});

	$('.variant_spec').change(function () {
		Joomla.submitform('product.switchVariant', this.form);
	});

	$('.btn-review').click(function () {
		var $reviewBox = $('#reviewBox');
		$reviewBox.addClass('focused').find('input[type="text"]').focus();
		setTimeout(function () {
			$('#reviewBox').removeClass('focused');
		}, 1500);
	});

	$('.btn-wishlist').not('.disabled').click(function () {
		var $this = $(this);
		var code = $this.data('item');

		if (!code) {
			var guest = $this.data('guest');
			var href = $this.data('href');
			if (guest) {
				if (confirm('You need to login to access your wishlist. Do you want to login?'))
					window.location.href = href || 'index.php?option=com_users&view=login';
			} else if (href) window.location.href = href;

			return;
		}

		var paths = Joomla.getOptions('system.paths', {});
		var baseUrl = (paths.base || paths.root || '') + '/index.php';

		$.ajax({
			url: baseUrl + '?option=com_sellacious&task=wishlist.addAjax',
			type: 'POST',
			data: {p: code},
			cache: false,
			dataType: 'json',
			success: function (response) {
				if (response.state === 1) {
					$this.find('i.fa').addClass('fa-heart').removeClass('fa-heart-o');
					$this.find('span').html('Added to Wishlist');
					$this.data('href', response.data['redirect'])
						.data('item', null);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			},
			error: function (jqXHR) {
				Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
				console.log(jqXHR.responseText);
			}
		});
	});

	$('.btn-add-cart').click(function () {
		var $this = $(this);
		var code = $this.data('item');
		var checkout = $this.data('checkout');
		if (!code) return;
		var q = $('#product-quantity').val() || 1;
		var paths = Joomla.getOptions('system.paths', {});
		var base = paths.base || paths.root || '';
		$.ajax({
			url: base + '/index.php?option=com_sellacious&task=cart.addAjax&format=json',
			type: 'POST',
			data: {p: code, quantity: q},
			cache: false,
			dataType: 'json'
		}).done(function (response) {
			if (response.state == 1) {
				$(document).trigger('cartUpdate', ['add', {uid: response.data.uid}]);
				Joomla.renderMessages({success: [response.message]});
				if (checkout && response.data['redirect']) {
					window.location.href = response.data['redirect'];
				} else if ($cartModal.length) {
					var o = $cartModal.data('CartModal');
					o.navStep('cart');
					$cartModal.modal('show');
				}
			} else {
				Joomla.renderMessages({error: [response.message]});
			}
		}).fail(function (jqXHR) {
			Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
			console.log(jqXHR.responseText);
		});
	});
});
