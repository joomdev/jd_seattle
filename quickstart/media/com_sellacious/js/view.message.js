/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
SellaciousViewMessage = function () {
	return this;
};

(function ($) {
	SellaciousViewMessage.prototype = {
		init: function (selector, token, tags, initial) {
			var $that = this;
			$that.element = $(selector);
			$that.token = token;
			initial = initial || [];

			if ($that.element.length == 0) return false;

			$that.element.select2({
				tags: tags || [],
				formatResult: function (item) {
					// Trick to mimic nested tags due to an issue with select2 matcher
					return item.optgroup ? '<i class="uppercase-small">' + item.text + '</i>' : '&nbsp;&nbsp;&nbsp;&nbsp;' + item.text;
				},
				// when entering a new tag
				createSearchChoice: function (term) {
					var choice = {
						id: $.trim(term),
						text: $.trim(term),
						bulk: false
					};
					// First match from tags available
					$.each(tags, function (i, tag) {
						if (tag.text.toUpperCase() == $.trim(term).toUpperCase()) {
							choice.id = tag.id;
							choice.text = tag.text;
							choice.bulk = tag.bulk;
						}
					});
					// next match from current selection, select2 will be available at the time of calling this
					$.each($that.element.select2('data'), function (i, tag) {
						if (tag.text.toUpperCase() == $.trim(term).toUpperCase()) {
							choice.id = tag.id;
							choice.text = tag.text;
							choice.bulk = tag.bulk;
						}
					});
					// return finally
					return choice;
				},
				initSelection: function (element, callback) {
					// This is a shortcut approach data is passed via PHP for this therefore we don't do multiple ajax
					// {id, text} for single-select, [{id, text},{id, text}] for multi-select
					callback(initial);
				}
			}); // .select2('data', initial);

			$that.element.on('select2-selecting', function (e) {
				if (typeof e.choice == 'object' && typeof e.choice.text != 'undefined') {
					if (e.choice['bulk']) {
						return true;
					}
					else if (/^\s*$/.test(e.choice.text) || /,/.test(e.choice.text)) return false;
					// Creating: e.choice;
					$that.getUser(e.choice.text);
					// Prevent for now ajax will act within 5 seconds if needed
					return false;
				}
			});
		},

		getUser: function (email) {
			var $that = this;
			var $token = $that.token;
			var url = 'index.php?option=com_sellacious&task=user.getUserAjax';
			var data = {key: 'email', value: email};
			data[$token] = 1;

			$.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				timeout: 5000
			}).done(function (response) {
				if (response.state == 1) {
					var data = $that.element.select2('data');
					var choice = {
						id: response.data.id,
						text: response.data.name,
						bulk: false
					};
					data.push(choice); // = $.extend(data, [choice]);
					$that.element.select2('data', data);
					$that.element.select2('search', '');
					Joomla.removeMessages();
				}
				else Joomla.renderMessages({info: [response.message]});
			}).fail(function (jqXHR) {
				console.log(email, 'Failed', jqXHR.responseText);
				Joomla.renderMessages({error: ['Failed to communicate to the server. Please try again later.']});
			});
		}
	};

	$(document).ready(function () {
		$('[type="reset"]').click(function () {
			return confirm('This will discard your composed message. Click CANCEL to abort or OK to continue.');
		});
	});
})(jQuery);
