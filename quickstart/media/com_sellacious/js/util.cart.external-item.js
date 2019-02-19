/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(function ($) {
	$(document).ready(function() {
		$('.btn-cart-add-external').click(function () {
			var $this = $(this);

			var pid = $this.data('product_id');
			if (!pid) return;

			var tid = $this.data('transaction_id');
			var sid = $this.data('source_id');
			var uid = $this.data('unique_id');
			var identifier = [sid || '', tid || '', uid || pid || ''].join('/');

			var options = {
				product: {
					id: pid,
					title: $this.data('product_title'),
					local_sku: $this.data('product_sku'),
					link_url: $this.data('link_url'),
					image_url: $this.data('image_url'),
					type: $this.data('product_type'),
					stock_capacity: typeof $this.data('stock') === 'undefined' ? false : $this.data('stock'),
					seller_currency: $this.data('currency'),
					length: $this.data('length'),
					width: $this.data('width'),
					height: $this.data('height'),
					size_unit: $this.data('size_unit'),
					weight: $this.data('weight'),
					weight_unit: $this.data('weight_unit')
				},
				price: {
					margin: $this.data('price_margin'),
					margin_type: $this.data('margin_percent'),
					cost_price: $this.data('cost_price'),
					list_price: $this.data('list_price'),
					calculated_price: $this.data('calculated_price'),
					ovr_price: $this.data('flat_price')
				},
				shipping: {
					flat_shipping: $this.data('flat_shipping'),
					flat_fee: $this.data('shipping_fee')
				}
			};

			var checkout = $this.data('checkout');
			var paths = Joomla.getOptions('system.paths', {});
			var baseUrl = (paths.base || paths.root || '') + '/index.php';
			$.ajax({
				url: baseUrl + '?option=com_sellacious&task=cart.addExternalAjax&format=json',
				type: 'POST',
				data: {i: identifier, options: options, quantity: 1},
				cache: false,
				dataType: 'json'
			}).done(function (response) {
				if (response.state === 1) {
					$(document).trigger('cartUpdate', ['add', {uid: response.data.uid}]);
					if (checkout && response.data['redirect']) {
						window.location.href = response.data['redirect'];
					} else {
						alert('Product added to cart successfully!');
					}
				} else alert(response.message);
			}).fail(function (jqXHR) {
				console.log(jqXHR.responseText);
				alert('There was an error while processing your request. Please try later.');
			});
		});
	});
});
