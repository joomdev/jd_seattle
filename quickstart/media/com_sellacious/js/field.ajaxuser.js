/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


var JFormFieldAjaxUser = function () {
	this.options = {}
};

(function ($) {
	var Ajax = function (url, data, callback, fallback) {
		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: data,
			success: function (response) {
				if (typeof callback == 'function') callback(response);
			},
			error: function (jqXHR) {
				console.log(jqXHR.responseText);
				if (typeof fallback == 'function') fallback();
			}
		});
	};

	JFormFieldAjaxUser.prototype = {
		setup : function (options) {
			$.extend(this.options, options);

			var that = this;
			var $id = '#' + that.options.id;
			$($id + '_ui').change(function () {
				that.getUser();
			});
		},

		getUser: function () {
			var that = this;
			var $id = '#' + that.options.id;
			var $token = that.options.token;
			var email = $($id + '_ui').val();

			var url = 'index.php?option=com_sellacious&task=user.getUserAjax';
			var data = {key: 'email', value: email};
			data[$token] = 1;

			Ajax(url, data, function (response) {
				console.log(email, 'success', response);
				if (response.state == 1) {
					$($id).val(response.data.id);
					$($id + '_name').text(response.data.name + ' (' + response.data['username'] + ')').css('color', '');
					Joomla.removeMessages();
					that.options.submit && Joomla.submitform(that.options.submit);
				} else {
					$($id).val('');
					$($id + '_name').text('Invalid email...').css('color', 'red');
					Joomla.renderMessages({warning: [response.message]});
				}
			}, function () {
				console.log(email, 'failed');
				Joomla.renderMessages({error: ['Failed to load selected user. Please try again later.']});
			});
		}
	}
})(jQuery);
