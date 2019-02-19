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
		var $badgeOption = $('#jform_params_badge_options');
		var $badgeIcon = $('#jform_params_badge_icon').closest('div.input-row');
		var $badgeText = $('#jform_params_badge_text').closest('div.input-row');
		var $badgeTextColor = $('#jform_params_badge_styles_color').closest('div.input-row');
		$($badgeOption).change(function () {
			var badgeSelectedOption = $badgeOption.find('input[type="radio"]').filter(':checked').val();
			if (badgeSelectedOption == 'icon') {
				$badgeIcon.show();
				$badgeText.hide();
				$badgeTextColor.hide();
			} else if (badgeSelectedOption == 'text') {
				$badgeIcon.hide();
				$badgeText.show();
				$badgeTextColor.show();
			}
			else {
				$badgeIcon.hide();
				$badgeText.hide();
			}
		}).triggerHandler('change');
	});
})(jQuery);
