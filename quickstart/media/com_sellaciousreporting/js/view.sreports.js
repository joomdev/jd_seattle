/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(document).ready(function ($) {
	$("#show-hide-cols-btn").on("click", function () {
		$('.ColVis_collection').toggle(400, function(){
			if ($(this).is(":hidden")) {
				$("form#adminForm").submit();
			}
		});
	});
});

jQuery(document).click(function(e)
{
	if (!jQuery(e.target).closest('#ul-columns-name').length && e.target.id != 'show-hide-cols-btn')
	{
		var hideShow = jQuery(".ColVis_collection").is(":hidden");
		jQuery(".ColVis_collection").hide(400, function () {
			if (!hideShow && jQuery(this).is(":hidden")) {
				jQuery("form#adminForm").submit();
			}
		});
	}
});
