/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
function listItemTask2(id, task, prefix, form) {
	var f = form || document.adminForm,
		i = 0, cbx,
		cb = f[prefix + id];

	if (!cb) return false;

	while (true) {
		cbx = f[prefix + i];
		if (!cbx) break;
		cbx.checked = false;
		i++;
	}

	cb.checked = true;
	Joomla.submitform(task);

	return false;
}

(function ($) {
	$(document).ready(function () {
		var $token = '';

		$('input[type="hidden"]').each(function () {
			if ($(this).attr('name').length === 32 && parseInt($(this).val()) === 1) {
				$token = $(this).attr('name');
				return false;
			}
		});

		$('.btn-spl-listing').on('click', '.btn', function (e) {
			e.preventDefault();
			var $this = $(e.target);

			if ($this.is('.active')) return;

			var id = $this.data('id');
			var catid = $this.data('catid');
			var seller_uid = $this.data('seller_uid');

			if (catid === null || !seller_uid || !id) return;

			var data = {};
			data['option'] = 'com_sellacious';
			data['task'] = 'products.sellerListingAjax';
			data['cid'] = [id];
			data['catid'] = catid;
			data['seller_uid'] = seller_uid;
			data[$token] = 1;

			$.ajax({
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: data
			}).done(function (response) {
				if (response.status === 1) {
					if (response['redirect']) {
						window.location.href = response['redirect'];
					} else {
						response.message.length && Joomla.renderMessages({success: [response.message]});
						$this.addClass('active').removeClass('btn-primary').addClass('btn-danger');
					}
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				Joomla.renderMessages({warning: ['Failed to process your request due to some server error.']});
				console.log(response.responseText);
			});
		});

		new ClipboardJS('.btn-copy-code', {
			text: function(trigger) {
				return $(trigger).data('text');
			}
		}).on('success', function (e) {
			var $element = $(e.trigger);
			var oText = $element.attr('data-original-title');
			$element.tooltip('destroy').attr('title', 'Code copied to clipboard!').tooltip().tooltip('show');
			setTimeout(function () {
				$element.tooltip('hide').tooltip('destroy').attr('title', oText).tooltip();
			}, 1000);
		}).on('error', $.noop);
	});
})(jQuery);
