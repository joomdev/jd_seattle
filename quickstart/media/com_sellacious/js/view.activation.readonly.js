/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($) {
	var validate = function (force) {
		var $license = $('.license-verify');
		var vars = ['name', 'email', 'sitename', 'siteurl', 'sitekey'];
		$.ajax({
			url: 'index.php?option=com_sellacious&task=activation.getLicenseAjax',
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {
				force: force ? 1 : 0
			},
			beforeSend: function () {
				$license.addClass('checking').removeClass('active error unregistered void inactive');
				$.each($.extend([], vars, ['subscription', 'expiry_date']), function (i, key) {
					$('.license-' + key).addClass('hidden').find('span').text('');
				});
			}
		})
			.done(function (response) {
				if (response.status !== 1) {
					$license.addClass('error');
				} else if (response.data.registered !== true) {
					$license.addClass('unregistered');
				} else if (response.data.modified === true) {
					$license.addClass('void');
				} else if (response.data.active !== true) {
					$license.addClass('inactive');
				} else {
					$license.addClass('active');
					$.each(vars, function (i, key) {
						if (typeof response.data[key] !== 'undefined' && response.data[key]) {
							$('.license-' + key).removeClass('hidden').find('>span').text(response.data[key]);
						}
					});
					if (response.data['site_id']) {
						var v = response.data['site_id'].match(/[\w]{1,4}/g).join('<i class="char-spacer"></i>');
						$('.license-site_id').removeClass('hidden').find('>span').html(v);
					}
					if (response.data['expiry_date']) {
						$('.license-subscription').removeClass('hidden').find('>span').text(response.data['subscription']);
						$('.license-expiry_date').removeClass('hidden').find('>span').text(response.data['expiry_date']);
					} else {
						$('.premium-prompt').removeClass('hidden');
					}
				}
			})
			.fail(function (jqXHR) {
				console.log(jqXHR.responseText);
				$license.addClass('error');
			})
			.always(function () {
				$license.removeClass('checking');
			});
	};

	var getTrial = function () {
		$.MessageBox({
			buttonDone: "Yes",
			buttonFail: "No",
			message: Joomla.JText._('COM_SELLACIOUS_ACTIVATION_CONFIRM_TRIAL_MESSAGE', 'Are you sure?').replace(/\n/g, '<br/>'),
			queue: false
		}).done(function () {
			$.ajax({
				url: 'index.php?option=com_sellacious&task=activation.requestTrialAjax',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {}
			})
				.done(function (response) {
					if (response.status === 1) {
						// Activated
						setTimeout(function () {
							window.location.reload();
						}, 2000);
					} else {
						alert(response.message);
					}
				})
				.fail(function (jqXHR) {
					console.log(jqXHR.responseText);
				});
		});
	};

	$(document).ready(function () {
		$(document).on('click', '.request-trial', getTrial);
		var force = $('.license-validate[data-auto-validate]').data('auto-validate');
		force || $(document).on('click', '.license-validate', function () {
			validate(true);
		});
		validate(force);
	});
})(jQuery);
