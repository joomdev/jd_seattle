/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


(function ($) {
	$(document).ready(function () {
		$('.btn-toggle').each(function () {
			this.onselectstart = function () {
				return false;
			}
		}).click(function (e) {
			if (e.shiftKey) {
				// Check current state
				var frame = $(this).closest('.toggle-frame');
				var changed = $(this).closest('.toggle-element').is('.visibility-changed');

				// Reset All
				frame.find('.visibility-changed').filter('.hidden').removeClass('hidden').removeClass('visibility-changed');
				frame.find('.visibility-changed').not('.hidden').addClass('hidden').removeClass('visibility-changed');

				// Set all to reverse of current state
				if (!changed) frame.find('.toggle-element').toggleClass('hidden').toggleClass('visibility-changed');
				// document.getSelection().removeAllRanges();
			} else {
				var box = $(this).closest('.toggle-box');
				box.find('.toggle-element').toggleClass('hidden').toggleClass('visibility-changed');
			}
			return false;
		});

		// Make first visible
		var frame = $('.toggle-frame');
		var box = frame.find('.toggle-box').eq(0);
		box.find('.toggle-element').toggleClass('hidden').toggleClass('visibility-changed');

		var msgTimer = 0;

		function messageWait(messages, wait) {
			Joomla.renderMessages(messages);

			// Clear any pending timeOut otherwise it may conflict
			if (msgTimer) clearTimeout(msgTimer);

			msgTimer = setTimeout(function () {
				Joomla.removeMessages();
			}, wait || 8000);
		}

		$('.btn-download').click(function () {

			var delivery_id = $(this).data('delivery');
			var file_id = $(this).data('file');

			if (!file_id || !delivery_id) {
				messageWait({info: ['Error: Invalid download link.']});
				return;
			}

			messageWait({info: ['Validating your download&hellip; Please wait&hellip;']});

			var paths = Joomla.getOptions('system.paths', {});
			var baseUrl = (paths.base || paths.root || '') + '/index.php';

			$.ajax({
				url: baseUrl + '?option=com_sellacious&task=download.check&delivery_id=' + delivery_id + '&id=' + file_id,
				type: 'POST',
				dataType: 'json',
				cache: false,
				success: function (response) {
					var messages;
					if (response.status === 1) {
						messages = {success: ['File found. Initiating download&hellip;']};
						window.location.href = 'index.php?option=com_sellacious&task=download.download&delivery_id=' + delivery_id + '&id=' + file_id;
					} else {
						messages = {warning: [response.message]};
					}
					messageWait(messages, 12000);
				},
				error: function (jqXHR) {
					messageWait({error: ['An unknown error was encountered when trying to download the file.']}, 12000);
					console.log(jqXHR.responseText);
				}
			});
		});

	});
})(jQuery);
