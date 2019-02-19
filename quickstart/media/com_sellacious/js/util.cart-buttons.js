/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function($) {
	$('.btn-content-cart').click(function () {
		var code = $(this).data('item');
		var checkout = $(this).data('checkout');
		if (!code) return;
		var paths = Joomla.getOptions('system.paths', {});
		var baseUrl = (paths.base || paths.root || '') + '/index.php';
		$.ajax({
			url: baseUrl + '?option=com_sellacious&task=cart.addAjax&format=json',
			type: 'POST',
			data: {p: code, quantity: 1},
			cache: false,
			dataType: 'json'
		}).done(function (response) {
			if (response.state === 1) {
				$(document).trigger('cartUpdate', ['add', {uid: response.data.uid}]);
				Joomla.renderMessages({success: [response.message]});
				if (checkout && response.data['redirect']) {
					window.location.href = response.data['redirect'];
				}
			} else Joomla.renderMessages({error: [response.message]});
		}).fail(function (jqXHR) {
			Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
			console.log(jqXHR.responseText);
		});
	});
});
