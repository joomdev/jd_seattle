/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(function ($) {
	var thElm;
	var startOffset;
	$(document).ready(function (e) {
		var thisArg = document.querySelectorAll("table.resize-col th,table th.resize-col");
		Array.prototype.forEach.call(thisArg, function (th) {
			th.style.position = 'relative';
			var grip = document.createElement('div');
			grip.innerHTML = "&nbsp;";
			grip.style.top = '0';
			grip.style.right = '0';
			grip.style.bottom = '0';
			grip.style.width = '5px';
			grip.style.position = 'absolute';
			grip.style.cursor = 'col-resize';
			grip.addEventListener('mousedown', function (e) {
				thElm = th;
				startOffset = th.offsetWidth - e.pageX;
			});
			th.appendChild(grip);
		});
		var $tables = $('table.resize-col th,table th.resize-col').closest('table');
		$tables.each(function () {
			$(this).find('th').each(function () {
				var wd = $(this).outerWidth();
				$(this).data('w', wd).attr('data-w', wd);
			});
		});
	});
	document.addEventListener('mousemove', function (e) {
		if (thElm) {
			thElm.style.width = startOffset + e.pageX + 'px';
			var wd = $(thElm).outerWidth();
			$(thElm).data('w', wd).attr('data-w', wd);
		}
	});
	document.addEventListener('mouseup', function () {
		thElm = undefined;
	});
});
