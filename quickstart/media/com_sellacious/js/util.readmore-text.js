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
		$('.readmore').each(function () {
			var text = $(this).text();
			var length = $(this).data('readmore') || 100;
			if (text.length <= length) return;

			var s_text = text.substring(0, length - 5);

			$(this).data('fulltext', text);
			$(this).data('text', s_text);
			$(this).data('readmoreState', 1);

			$(this).text(s_text);
			$(this).append('&hellip;&nbsp;<a href="#" class="readmore-toggle">&plus; more</a>');
		})
			.on('click', '.readmore-toggle', function (e) {
				e.preventDefault();
				var $block = $(this).closest('.readmore');
				var state = $block.data('readmoreState');
				if (state) {
					$block.text($block.data('fulltext'));
					$block.append('&hellip;&nbsp;<a href="#" class="readmore-toggle">&ndash; less</a>');
				} else {
					$block.text($block.data('text'));
					$block.append('&hellip;&nbsp;<a href="#" class="readmore-toggle">&plus; more</a>');
				}
				$block.data('readmoreState', !state);
			});
	});
});
