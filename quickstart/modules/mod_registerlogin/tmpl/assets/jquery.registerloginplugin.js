/**
 * @package		Register Login Joomla Module
 * @author		JoomDev
 * @copyright	Copyright (C) 2018 Joomdev, Inc. All rights reserved.
 * @license    GNU/GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */
(function ($) {
	$.fn.jdRegisterLogin = function () {
		return this.each(function () {
			let _this = $(this);
			_this.find('[data-tab]').hide();
			let _targetActive = _this.find('.active[data-tab-target]').data('tab-target');
			$(_targetActive).show();
			_this.find('[data-tab-target]').click(function () {
				$(this).parent().siblings().children('[data-tab-target]').removeClass('active');
				$(this).addClass('active');
				let _targetNext = $(this).parent().siblings().children().data('tab-target');
				let _target = $(this).data('tab-target');
				$(_targetNext).hide();
				$(_target).show();
			});
		});
	};
}(jQuery));