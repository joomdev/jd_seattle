/**
 * @version     1.6.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	var getCode = function ($element, checkout) {
		var id = $element.data('id');
		var args = $('#button-params-' + id).text() + '';
		var values = JSON.parse(args);
		var $markup = $('<a></a>', {href: '#', 'class': 'btn btn-primary btn-cart-add-external'});
		var text;
		if (checkout) {
			values['checkout'] = 'true';
			text = Joomla.JText._('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_BUY_NOW', 'Buy');
		} else {
			values['checkout'] = 'false';
			text = Joomla.JText._('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_ADD_TO_CART', 'Add');
		}
		$markup.text(text);
		$.each(values, function (k, v) {
			$markup.attr('data-' + k, v);
		});
		return $markup.prop('outerHTML');
	};

	var onCopy = function (e) {
		var $element = $(e.trigger);
		var oText = $element.attr('data-original-title');
		$element.tooltip('dispose').attr('title', 'Code copied to clipboard!').tooltip().tooltip('show');
		setTimeout(function () {
			$element.tooltip('hide').tooltip('dispose').attr('title', oText).tooltip();
		}, 1000);
	};

	new ClipboardJS('.btn-copy-buynow', {
		text: function(trigger) {
			return getCode($(trigger), true);
		}
	}).on('success', onCopy).on('error', $.noop);

	new ClipboardJS('.btn-copy-addtocart', {
		text: function(trigger) {
			return getCode($(trigger), false);
		}
	}).on('success', onCopy).on('error', $.noop);
});
