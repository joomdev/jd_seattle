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
		const KEY_ESCAPE = 27;

		$(document).keyup(function(e) {
			if (e.keyCode == KEY_ESCAPE) {
				$('.static-modal').fadeTo('slow', 0).addClass('hidden');
			}
		});

		$('.hide-modal').click(function (e) {
			e.preventDefault();
			$('.static-modal').fadeTo('slow', 0).addClass('hidden');
		});

		$('.show-modal').click(function (e) {
			e.preventDefault();
			$('.static-modal').addClass('hidden');
			$(this).closest('td').find('.static-modal').fadeTo('fast', 1).removeClass('hidden');
		});
	});
})(jQuery);
