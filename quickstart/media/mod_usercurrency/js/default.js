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

		// Todo: make jqTransform usage conditional
		$("#mod_usercurrency_block").jqTransform();

		var element = $("#mod_usercurrency_block").find("div.jqTransformSelectWrapper ul li a");

		element.bind('click', function(){

			var value = $("#mod_usercurrency_block").find("option:selected").val();
			var $this = $(this);

			$.ajax({
				url: 'index.php?option=com_sellacious&task=setCurrencyAjax',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {c: value},
				success: function (response) {
					if (response.state == 1) {
						// Reload so that the prices are reflected accordingly
						window.location.href = window.location.href.split('#')[0];
					} else {
						$this.val('');
						Joomla.renderMessages({warning: [response.message]});
					}
				},
				error: function (jqXHR) {
					Joomla.renderMessages({warning: 'Failed to change your currency display preference.'});
					console.log(jqXHR.responseText);
				}
			});
		});
	});
})(jQuery);
