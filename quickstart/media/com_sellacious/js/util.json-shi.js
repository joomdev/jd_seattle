/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(function ($) {
	$.prototype.extend({
		/**
		 * Highlight JSON text inside an HTML element.
		 * USAGE: $('#element').jsonSHi(4, 'prefix');
		 *
		 * @param  indent  int     Number of spaces for indentation of the JSON, default = 4
		 * @param  prefix  string  Class prefix if you want to customize the styling
		 *
		 * @return this
		 */
		jsonSHi: function (indent, prefix) {
			var html, json = $(this).text().trim();
			var obj = JSON.decode(json);
			json = JSON.stringify(obj, null, indent || $(this).data('indent') || 4);
			prefix = prefix || $(this).data('prefix') || '';
			json = json.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/&/g, '&amp;');
			var regExp = /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g;
			html = json.replace(regExp, function (match) {
				var clazz = 'number';
				if (/^"/.test(match)) clazz = /:$/.test(match) ? 'key' : 'string';
				else if (/true|false/.test(match)) clazz = 'boolean';
				else if (/null/.test(match)) clazz = 'null';
				return '<span class="' + prefix + clazz + '">' + match + '</span>';
			});
			if ($(this).is('pre')) {
				$(this).addClass('jsonshi').html(html);
			} else {
				$(this).html($('<pre>').addClass('jsonshi').html(html));
			}
			return $(this);
		}
	});
	// Create macro
	$(document).ready(function () {
		$('.jsonshi').jsonSHi();
	});
});
