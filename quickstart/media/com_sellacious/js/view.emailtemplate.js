/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('.accordion-heading').click(function (e) {
		if (!$(e.target).is('a')) $(this).find('a').click();
	});

	Joomla.submitbutton = function (task, form) {
		form = form || document.getElementById('adminForm');
		var task2 = task.split('.')[1] || '';

		if (task2 != 'cancel') {
			if ($(form).find('#jform_send_actual_recipient').find('input[type="radio"]:checked').val() == '0') {
				var to = $(form).find('#jform_recipients').val();
				var cc = $(form).find('#jform_cc').val();
				var bcc = $(form).find('#jform_bcc').val();

				if ($.trim(to) == '' && $.trim(cc) == '' && $.trim(bcc) == '') {
					Joomla.renderMessages({warning: [
						Joomla.JText._('COM_SELLACIOUS_EMAILTEMPLATE_ACTUAL_RECIPIENTS_OR_ALTERNATE_REQUIRED_WARNING',
							'If not sending to actual recipients then you must specify at least one of Recipient, Cc, or Bcc')]});
					return false;
				}
			}
			Joomla.submitform(task, form, true);
		} else {
			Joomla.submitform(task, form, false);
		}
	};
});
