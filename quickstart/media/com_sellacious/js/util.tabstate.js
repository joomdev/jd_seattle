/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($) {
	$(window).load(function () {
		var getUriParam = (function (name) {
			name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
				results = regex.exec(location.search);
			return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
		});
		var view = getUriParam('view');
		var option = getUriParam('option');
		var cookieId = option + '.' + view + '.lastTab';
		$(document).on('show.bs.tab', 'a[data-toggle="tab"]', function (e) {
			$.cookie(cookieId, $(e.target).attr('href'));
		});
		var lastTab = $.cookie(cookieId) || '';
		if (lastTab !== '') {
			var $tab = $('a[href="' + lastTab + '"]');
			if ($tab.length) {
				$tab.click();
				$tab.parent('li').addClass('active');
			} else $.cookie(cookieId, '');
		}
	});
})(jQuery);
