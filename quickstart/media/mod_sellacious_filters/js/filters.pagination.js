/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	var $filters = $('.mod-sellacious-filters');

	$('.cat-show-more a').on('click', function (e) {
		var pData = {
			option: 'com_ajax',
			module: 'sellacious_filters',
			method: 'getCategories',
			format: 'json',
			list_limit: 15
		};

		$.ajax({
			type: 'POST',
			data: pData,
			cache: false,
			dataType: 'json'
		}).done(function (response) {
			if (response.state == 1) {
				$('#filter-list-group').html(response.data);
			} else {
				Joomla.renderMessages({error: [response.message]});
			}
		}).fail(function (jqXHR) {
			Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
			console.log(jqXHR.responseText);
		});
	});
});
