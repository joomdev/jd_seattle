/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($) {
	// Currently only Iframe modal is supported
	var $html = $('<div class="has-modal modal-overlay"></div><div class="has-modal modal-container">' +
		'<i class="fa fa-times fa-lg pull-right modal-close"></i>' +
		'<div class="clearfix"></div><div class="modal-contents"></div></div>');

	$(document).ready(function () {
		$html.appendTo('body');

		$('.modal-close,.modal-overlay').click(function () {
			$('.has-modal').fadeOut('slow').removeClass('active');
		});

		$('a.btn-modal').click(function (e) {
			e.preventDefault();
			var size = $(this).data('modal') || {};

			if (size == 'full') {
				$('.has-modal.modal-container').addClass('full');
			} else if (typeof size != 'object') {
				$('.has-modal.modal-container').removeClass('full');
			} else {
				$('.has-modal.modal-container').removeClass('full')
					.css('width', size.w ? size.w + 'px' : null)
					.css('height', size.h ? size.h + 'px' : null)
					.css('left', size.w ? '50%' : null)
					.css('margin-left', size.w ? Math.floor(-size.w / 2) + 'px' : null);
			}

			var $iframe = $('<iframe/>', {'src': $(this).attr('href')});
			var $ifr_box = $('.modal-contents').empty();
			$iframe.appendTo($ifr_box);
			$('.has-modal').fadeIn('slow').addClass('active');
		});
	});
})(jQuery);
