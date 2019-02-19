/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// todo: THIS IS NOT USED ANYMORE, IF THIS IS TO BE FOREVER THEN REMOVE THIS FILE. THIS MAY BE ALREADY OUTDATED.
(function ($) {
	$(document).ready(function () {
		$('.btn-toggle').click(function () {
			var cell = $(this).closest('td');
			var rec = $(cell).data('row');

			$('#order-items-' + rec).toggleClass('hidden');
			cell.find('.btn-toggle').toggleClass('hidden');
		});

		$('.hover-toggle').hover(function () {
			$(this).find('.hover-toggle-el').toggleClass('hidden');
		});

		$('.btn-pay').click(function () {
			$('#payment-form').find('.aio-disabled').removeAttr('disabled').removeClass('aio-disabled');
			var index = $(this).data('index');
			$('input[id^=cb]').prop('checked', false);
			$('#cb' + index).prop('checked', true);
		});

		$('.btn-pay-now').on('click', function () {
			// todo: we should run validation against the selected form
			try {
				var $form = $(this).closest('form');
				var object = $form.serializeObject();
				var id = $('input[id^=cb]').filter(':checked').val();
				var token = $('#formToken').attr('name');

				if (object['jform'] && id && token) {
					var values = object['jform'];

					var data = {
						option: 'com_sellacious',
						task: 'orders.setPaymentAjax',
						jform: values,
						cid: [id]
					};

					data[token] = 1;

					$('#payment-form').find('form').find(':input').addClass('aio-disabled').attr('disabled', 'disabled');

					var paths = Joomla.getOptions('system.paths', {});
					var baseUrl = (paths.base || paths.root || '') + '/index.php';

					$.ajax({
						url: baseUrl,
						type: 'POST',
						dataType: 'json',
						cache: false,
						data: data,
						success: function (response) {
							if (response.status === 1) {
								$('#payment-form').modal('hide');
								Joomla.renderMessages({success: [response.message]});
								window.location.href = 'index.php?option=com_sellacious&task=order.initPayment&' + 'id=' + id + '&' + token + '=1';
							} else {
								Joomla.renderMessages({warning: [response.message]});
								$('#payment-form').find('.aio-disabled').removeAttr('disabled').removeClass('aio-disabled');
							}
						},
						error: function (jqXHR) {
							console.log(jqXHR.responseText);
							$('#payment-form').find('.aio-disabled').removeAttr('disabled').removeClass('aio-disabled');
						}
					});
				}
			} catch (e) {
				console.log(e);
			}
		});
	});
})(jQuery);
