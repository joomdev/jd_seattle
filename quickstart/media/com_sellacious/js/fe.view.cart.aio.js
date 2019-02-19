/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('#address-items').hScroll(60);

	var token = $('#formToken').attr('name');

	var $cartAOI = $('#cart-aio-container');

	if ($cartAOI.length) {
		var o = new SellaciousViewCartAIO;
		o.token = token;
		o.init('#cart-aio-container');
		$cartAOI.data('CartAIO', o);
	}

	var $cartModal = $('#modal-cart');

	if ($cartModal.length) {
		var oo = new SellaciousViewCartAIO;
		oo.token = token;
		oo.initCart('#modal-cart .modal-body', true);
		$cartModal.find('.modal-body').html('<div id="cart-items"></div>');
		$cartModal.data('CartModal', oo);
		$('.btn-cart-modal').click(function () {
			var o = $cartModal.data('CartModal');
			o.navStep('cart');
			$cartModal.modal('show')
		});
	} else {
		$('.btn-cart-modal').addClass('hidden');
	}
});
