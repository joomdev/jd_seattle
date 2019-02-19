/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
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

	$('.btn-add-cart').click(function () {
		var code = $(this).data('item');
		var checkout = $(this).data('checkout');
		var paths = Joomla.getOptions('system.paths', {});
		var base = paths.base || paths.root || '';
		$.ajax({
			url: base + '/index.php?option=com_sellacious&task=cart.addAjax&format=json',
			type: 'POST',
			data: {p: code},
			cache: false,
			dataType: 'json'
		}).done(function (response) {
			if (response.state == 1) {
				$(document).trigger('cartUpdate', ['add', {uid: response.data.uid}]);
				Joomla.renderMessages({success: [response.message]});
				if (checkout && response.data['redirect']) {
					window.location.href = response.data['redirect'];
				} else {
					// Open cart in modal
					$cartModal = $('#modal-cart');
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

	$('.btn-toggle').click(function () {
		$(this).find('[data-toggle="true"]').toggleClass('hidden');
	});

	$(".btn-group > .btn").click(function () {
		$(".btn-group > .btn").removeClass("active");
		$(this).addClass("active");
	});

	var setGridHeight = function () {
		var boxH = 0;

		$('.product-box').each(function () {
			var eleHeight = this.offsetHeight;
			boxH = (eleHeight > boxH ? eleHeight : boxH);
		});

		$('head').append('<style>.grid-layout .product-box { height: ' + boxH + 'px; }</style>');
	};

	var $productsPage = $('#products-page');

	$('.switch-style').click(function () {
		var switchedLayout = $(this).data('style');
		var $productsBox = $('#products-box');

		$productsPage.removeClass('list-layout')
			.removeClass('grid-layout').removeClass('masonry-layout').addClass(switchedLayout);

		if (switchedLayout === 'list-layout') {
			$productsBox.isotope().isotope('destroy');
		} else if (switchedLayout === 'grid-layout') {
			$productsBox.isotope().isotope('destroy');
			if (typeof setGridHeight === 'function') {
				setGridHeight();
				setGridHeight = null;
			}
		} else if (switchedLayout === 'masonry-layout') {
			setTimeout(function () {
				$productsBox.isotope({
					itemSelector: '.product-wrap',
					layoutMode: 'masonry'
				});
			}, 400);
		}
	}).filter('.active').triggerHandler('click');
});

