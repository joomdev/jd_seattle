/**
 * @package		Register Login Joomla Module
 * @author		JoomDev
 * @copyright	Copyright (C) 2018 Joomdev, Inc. All rights reserved.
 * @license    GNU/GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */

jQuery(document).ready(function () {
	function error() {
		jQuery('#error_message1').css("padding", "15px");
		jQuery('#error_message1').css("background", "#f2dede");
		jQuery('#error_message1').css("color", "#a94442");
		jQuery('#error_message1').css("display", "inline-block");
	}
	jQuery(".view_").change(function () {
		if (jQuery(this).val() == 1) {
			jQuery('#login_form input#openview').val(jQuery(this).val());
		} else if (jQuery(this).val() == 2) {
			jQuery('#registration_ input#openview').val(jQuery(this).val());
		}
	});
	jQuery("#registration_form").validate({
		submitHandler: function () {
			var submit = jQuery('#register_submit');
			jQuery.ajax({
				url: 'index.php?option=com_ajax&module=registerlogin&method=getUserRegister&Itemid=+itemId+&format=json',
				type: 'POST',
				data: jQuery('#registration_form').serialize(),
				async: true,
				beforeSend: function () {
					submit.attr('disabled', true);
					jQuery('.regload').show();
				},
				success: function (response) {
					jQuery('.regload').hide();
					submit.removeAttr('disabled');
					if (response) {
						jQuery('form#registration_form input#register_submit').val('Register');
						error();
					}
					if (response == "captcha incorrect") {
						jQuery('#error_message1').html("Captcha incorrect , please enter valid value");
						error();
					} else if (response == "The username you entered is not available. Please pick another username.") {
						jQuery('#error_message1').html(response);
						error();
					} else if (response == "This email address is already registered.") {
						jQuery('#error_message1').html(response);
						error();
					} else if (response == "Please enter your name.") {
						jQuery('#error_message1').html("Sorry the credentials you are using are invalid");
						error();
					} else {
						jQuery('form#registration_form input').val('');
						jQuery('#error_message1').html(response);
						jQuery('#error_message1').css("padding", "15px");
						jQuery('#error_message1').css("background", "#dff0d8");
						jQuery('#error_message1').css("color", "#3c763d");
						jQuery('#error_message1').css("display", "inline-block");
						jQuery('#login_view').click();
					}
				},
				error: function (e) {
					alert("error");
					jQuery('.regload').hide();
					submit.removeAttr('disabled');
					console.log(e);
				}
			});
			return false;
		}
	});
});