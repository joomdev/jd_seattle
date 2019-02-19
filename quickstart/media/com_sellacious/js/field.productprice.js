/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


var JFormFieldProductPrice = function () {
	this.options = {
		id : 'jform'
	};
};

(function ($) {
	JFormFieldProductPrice.prototype = {

		setup : function (options) {
			$.extend(this.options, options);

			var that = this;
			var $id = '#' + that.options.id;

			$($id).on('change', '.sfpprow-cp,.sfpprow-lp,.sfpprow-fp,.sfpprow-ovrprice', function () {
				that.recalculate();
			});

			$($id).on('click', '.sfpprow-margin-type', function () {
				that.recalculate();
			});

			// Allow margin type switch from margin input using '#', '$', '%'
			$($id).on('change', '.sfpprow-margin', function () {
				var amt = $(this).val();
				var marginType = $($id).find('.sfpprow-margin-type');

				var value = parseFloat(amt.replace(/%|\$|#/g, ''));
				$(this).val(isNaN(value) ? '0.00' : value.toFixed(2));

				if (/\$|#/.test(amt)) marginType.prop('checked', true).trigger('click');
				else if (/%/.test(amt)) marginType.prop('checked', false).trigger('click');

				that.recalculate();
			});
		},

		recalculate : function () {
			var zero = 0;
			var $id = '#' + this.options.id;

			var $cost_price = $($id + '_cost_price');
			var $list_price = $($id + '_list_price');
			var $margin = $($id + '_margin');
			var $margin_type = $($id + '_margin_type');
			var $calculated_price = $($id + '_calculated_price');
			var $override_price = $($id + '_ovr_price');

			var cost_price = parseFloat($cost_price.val());
			var list_price = parseFloat($list_price.val());
			var margin = parseFloat($margin.val());
			var margin_type = $margin_type.is(':checked');
			var override_price = parseFloat($override_price.val());

			cost_price = isNaN(cost_price) ? zero : cost_price;
			list_price = isNaN(list_price) ? zero : list_price;
			margin = isNaN(margin) ? zero : margin;
			override_price = isNaN(override_price) ? zero : override_price;

			var calculated_price = margin_type ? cost_price * (1 + margin / 100) : cost_price + margin;

			$cost_price.val(cost_price.toFixed(2));
			$list_price.val(list_price.toFixed(2));
			$margin.val(margin.toFixed(2));
			$calculated_price.val(calculated_price.toFixed(2));
			$override_price.val(override_price.toFixed(2));
		}
	}
})(jQuery);
