/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Asfaque Ali Ansari <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(function ($) {
	var $slider = $('.products-slider-detail');
	var item_count = $slider.find('a').length;

	if (item_count == 1) $slider.addClass('onethumb');

	$slider.owlCarousel({
		margin: 5,
		dots: false,
		autoWidth: true,
		nav: item_count >= 4,
		mouseDrag: item_count >= 4,
		touchDrag: item_count >= 4,
		navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
		responsive: {
			0: {items: 4}
		}
	});
});
