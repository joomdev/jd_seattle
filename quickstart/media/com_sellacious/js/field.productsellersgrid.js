/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var ProductSellersGridField = function (element, options) {
	this.element = null;
	this.options = {};
	this.init(element, options);
};

jQuery(function ($) {
	ProductSellersGridField.prototype = {
		init: function (element, options) {
			var self = this;
			this.element = $(element);
			this.input = this.element.find('.jff-psg-input');
			this.options = $.extend({}, this.options, options);

			this.element.on('click', '.jff-psg-switch-btn', function () {
				var def = 'Any unsaved changes will be lost. Continue to switching?';
				var message = Joomla.JText._('COM_SELLACIOUS_PRODUCT_SELLER_SWITCH_WARNING_EDIT_LOST', def);
				if (confirm(message)) {
					self.input.val($(this).data('seller-uid'));
					Joomla.submitbutton('product.setSeller');
				}
			})
		}
	};

	$(document).ready(function () {
		$('.jff-productsellersgrid').each(function () {
			$(this).data('productsellersgrid', new ProductSellersGridField(this, {}));
		})
	});
});
