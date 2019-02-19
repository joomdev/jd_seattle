/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	var getToken = function () {
		var token = '';
		$('input[type="hidden"][name]').each(function () {
			var name = $(this).attr('name');
			if (name.length === 32 && parseInt($(this).val()) === 1) {
				token = name;
				return false;
			}
		});
		return token;
	};

	$('.btn-pay-now').on('click', function (e) {
		var order_id = $('#order_id').val();
		var $form = $(this).closest('form');
		var $fileData = new FormData();
		var $formData = $form.serializeArray();
		var token = getToken();
		$form.find('input[type="file"]').each(function () {
			if (this.files && this.files.length) {
				$fileData.append($(this).attr('name'), this.files[0]);
			}
		});
		$.each($formData, function (index, input) {
			$fileData.append(input.name, input.value);
		});

		$fileData.append(token, 1);
		$fileData.append('id', order_id);

		var paths = Joomla.getOptions('system.paths', {});
		var baseUrl = (paths.base || paths.root || '') + '/index.php';

		$.ajax({
			url: baseUrl + '?option=com_sellacious&task=order.setPaymentAjax',
			type: 'POST',
			data: $fileData,
			cache: false,
			dataType: 'json',
			processData: false, // Don't process the files
			contentType: false  // Set content type to false as jQuery will tell the server its a query string request
		}).done(function (response) {
			if (response.status === 1) {
				window.location.href = response['redirect'];
			} else {
				Joomla.renderMessages({warning: [response.message]});
			}
		}).fail(function (xhr) {
			console.log(xhr.responseText);
			Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
		});
	});
});
