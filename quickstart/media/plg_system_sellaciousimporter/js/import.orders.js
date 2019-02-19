/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {

	$('#import-orders').find('.btn-import').click(function () {
		var $btn = $(this);
		var $log = $btn.closest('.importer-block').find('.import-log');
		var $token = Joomla.getToken();
		var $ajax = null;
		var interval = 0;
		var data = {};
		data[$token] = 1;
		var iOpts = Joomla.getOptions('com_importer.import') || {};

		var ping = function () {
			if ($ajax) $ajax.abort();
			$ajax = $.ajax({
				url: 'index.php?option=com_sellacious&task=import.importAjax',
				data: $.extend({}, data, {id: iOpts.id || 0}),
				type: 'post',
				dataType: 'json',
				timeout: 15000
			}).done(function (response) {
				// 0 = ERROR, 1 = NOTHING PENDING, 2 = RUNNING, 3 = DONE
				var log = '';
				var state = parseInt(response.state);
				if (response != null && response.data != null && response.data.log != null) log = response.data.log;
				var message = $('<div/>').html(log).text();
				message = '<p>' + response.message + '</p>' + $.trim(message).split(/[\r\n]+/g).reverse().join('<br/>');
				if (state == 1) {
					if (interval) clearInterval(interval);
					$log.prepend(message + '<br>');
				} else if (state == 2) {
					$log.html(message);
				} else if (state == 0) {
					if (interval) clearInterval(interval);
					$log.html(message);
				} else if (state == 3) {
					if (interval) clearInterval(interval);
					$log.html(message);
				} else {
					if (interval) clearInterval(interval);
					$log.prepend('<p>Unknown response from server</p>');
				}
			}).fail(function (xhr) {
				console.log(xhr.responseText);
			});
		};

		if ($btn.is('.active')) {
			$log.removeClass('hidden').html('<p>Working&hellip; Please wait&hellip;</p>');
			interval = setInterval(ping, 5000);
			ping();
		} else {
			var alias = {};
			$('#sortable-area-orders').find('ul.alias-drop').each(function () {
				var k = $(this).data('column');
				var item = $(this).find('.sortable-item');
				if (item.length) alias[k] = item.data('alias');
			});

			$log.removeClass('hidden').html('<p>Working&hellip; Please wait&hellip;</p>');

			$.ajax({
				url: 'index.php?option=com_sellacious&task=import.setMappingAjax',
				data: $.extend({}, data, {alias: alias, id: iOpts.id || 0}),
				type: 'post',
				dataType: 'json',
				timeout: 15000
			}).done(function (response) {
				if (response.state == 1) {
					$('.table-column-map').slideUp('slow');
					interval = setInterval(ping, 5000);
					ping();
				} else {
					$log.prepend(response.message);
				}
			}).fail(function (xhr) {
				console.log(xhr.responseText);
				$log.prepend('<p>Unknown response from server</p>');
			});
		}
	});

});
