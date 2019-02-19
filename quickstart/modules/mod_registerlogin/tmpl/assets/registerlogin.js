/**
 * @package		Register Login Joomla Module
 * @author		JoomDev
 * @copyright	Copyright (C) 2018 Joomdev, Inc. All rights reserved.
 * @license    GNU/GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */

jQuery(document).ready(function () {
	jQuery(".view_").change(function () {
		if (jQuery(this).val() == 1) {
			var captcha = jQuery('#captcha2').html();
			jQuery('#login_form').show();
			jQuery('#login_form input#openview').val(jQuery(this).val());
			jQuery('#captcha1').html(captcha);
		} else if (jQuery(this).val() == 2) {
			var captcha = jQuery('#captcha1').html();
			jQuery('#captcha2').html(captcha);
			jQuery('#registration_ input#openview').val(jQuery(this).val());
		}
	});
	jQuery("#login-form").validate({});
	jQuery("#registration_form").validate({
		rules: {
			recaptcha_response_field: "required"
		},
		errorPlacement: function (error, element) {
			if (element.attr("name") == "recaptcha_response_field") {
				error.insertAfter("#registration_form #recaptcha_area");
			} else if (element.attr("name") == "terms") {
				error.insertAfter("#registration_form #terms_");
			} else {
				error.insertAfter(element);
			}
		},
	});
});