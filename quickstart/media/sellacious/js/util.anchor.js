/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('body').on('click', 'a[href^="#"][href!="#"]', function (e) {
		e.preventDefault();
		var h = $(this).attr('href');
		var a = 'a[name="' + h.substring(1) + '"]';
		var d = $(a);
		d.length || (d = $(h));
		d.length && $('html, body').animate({
			scrollTop: d.offset().top
		}, 800);
	});
});
