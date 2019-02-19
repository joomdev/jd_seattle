/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(function ($) {
	$(document).ready(function () {
		var floatHandler = function () {
			var $el = $(this);
			var isInput = $el.is(':input');
			var val = isInput ? $el.val() : $el.text();
			var dec = $el.data('float') || 0;

			val = parseFloat(val);
			val = isNaN(val) ? 0 : val;
			val = val.toFixed(dec);

			isInput ? $el.val(val) : $el.text(val);
		};
		$(document).on('change blur', '[data-float]', floatHandler);

		$('[data-float]').each(function () {
			$(this).trigger('change');
			floatHandler.apply(this);
		});
		$(document.head).append("<style>[data-float] {text-align: right; padding-right: 10px}</style>");
	});
});
