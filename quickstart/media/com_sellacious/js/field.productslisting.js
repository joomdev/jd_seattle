/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


(function ($) {
	$(document).ready(function () {
		$('.products_listing_days').change(function () {
			var el = $(this);
			var days = el.val();
			var term = el.data('recurrence');
			var price = el.data('price');

			days = parseInt(days);
			term = parseInt(term);
			price = parseFloat(price);

			days = isNaN(days) ? 0 : Math.abs(days);
			term = isNaN(term) ? 0 : term;
			price = isNaN(price) ? 0 : price;

			el.val(days);

			var dest = el.attr('id').replace(/_days$/, '_cost');
			var dest_field = $('#' + dest);

			var cost = term ? Math.ceil(days / term) * price : days ? price : 0;
			dest_field.val(cost.toFixed(2)).trigger('change');
		}).trigger('change');
	});
})(jQuery);
