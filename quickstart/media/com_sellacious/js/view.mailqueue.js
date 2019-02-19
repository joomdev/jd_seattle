/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('.btn-modal').click(function () {
		var $cWrapper = $(this).closest('td');
		var content = $cWrapper.find('.mail-content').html();
		var $backdrop = $('<div class="modal-shadow"></div>');
		var $close = $('<div class="pull-right btn-close"><i class="fa fa-times"></i></div>');
		var $content = $('<div/>').html(content);
		var $body = $('<div class="mail-body-modal"></div>').append($close).append($content);
		$('<div class="mail-modal-container"></div>').append($backdrop).append($body).appendTo('body');
	});

	$('body').on('click', '.btn-close', function () {
		$('.mail-modal-container').remove();
	}).on('keyup', function (e) {
		const KEY_ESCAPE = 27;
		if (e.keyCode == KEY_ESCAPE)
			$('.mail-modal-container').remove();
	});
});
