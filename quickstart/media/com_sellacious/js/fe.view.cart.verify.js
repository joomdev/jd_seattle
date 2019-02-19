/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
Joomla = window.Joomla || {};

Joomla.submitbutton = function (task, form) {
	form = form || document.getElementById('adminForm');

	if (document.formvalidator.isValid(form)) {
		Joomla.submitform(task, form);
	} else {
		alert('Please fill all the required values before placing an order.');
	}
};

jQuery(function ($) {
	$(document).ready(function () {
		$('.shoprule-info-toggle').click(function () {
			var uid = $(this).data('uid');
			$(this).find('i').toggleClass('fa-plus-square-o').toggleClass('fa-minus-square-o');
			$('.' + uid + '-info').toggleClass('hidden');
			return false;
		});
	});
});
