/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// Account section - Login or registration
jQuery(function ($) {
	var sectionAccount = {

		name: 'account',

		elements: {
			container: '#cart-opc-account',
			form: '.opc-account-form',
			input_email: '#login_email',
			input_userid: '#login_user_id',
			input_password: '#login_passwd',
			input_guest_flag: '#is_guest_checkout',
			btn_submit_email: '#login_email_submit',
			btn_change_email: '#login_email_change',
			btn_submit_password: '#login_password_submit',
			btn_submit_register: '#login_email_register',
			btn_submit_logout: '#login_logout',
			btn_guest_checkout: '#btn_guest_checkout',
			el_row_email: '#login_email-row',
			el_row_password: '#login_passwd-row',
			el_row_guest: '#guest_checkout-info'
		},

		element: function (name) {
			return this.container.find(this.elements[name]);
		},

		setup: function ($opc, elements) {
			var $this = this;
			$this.opc = $opc;

			$.extend($this.elements, elements);
			$this.container = $($this.elements.container);

			$this.container
				.on('click', '.btn-edit', function () {
					$this.edit = true;
					$this.opc.navSection($this.name);
				})

				.on("change", "input[type!='hidden']", function(){
					var $form = $(this).closest("form");
					$form.attr("data-form-changed", true);
				})

				.on("focus", "input, textarea, select", function (event) {
					$this.opc.focusSection(event, $this);
				})

				.on("focusin, click", function (event) {
					$this.opc.focusSection(event, $this);
				});

			$this.element('btn_guest_checkout').click(function () {
				$this.guest();
			});

			$this.element('btn_submit_email').click(function () {
				$(this).attr('disabled', 'disabled');
				var email = $this.element('input_email').val();
				$this.checkEmail(email);
			});

			$this.element('btn_change_email').click(function () {
				$this.promptEmail();
			});

			$this.element('btn_submit_password').click(function () {
				$this.login();
			});

			$this.element('btn_submit_register').click(function () {
				$this.register();
			});

			$this.element('btn_submit_logout').click(function () {
				if (confirm(Joomla.JText._('COM_SELLACIOUSOPC_CART_CONFIRM_LOGOUT_ACTION_MESSAGE', 'Are you sure you want to logout?')))
					$this.logout();
			});
		},

		sectionIn: function () {
			var $this = this;
			var email = $this.element('input_email').val();
			var userid = $this.element('input_userid').val();
			userid = parseInt(userid);
			userid = isNaN(userid) ? 0 : userid;
			if ($this.edit) {
				$this.promptEmail();
			} else if (userid && email != '') {
				$this.setAccount({id: userid, email: email});
			} else if ($this.element('input_guest_flag').val() == 1 && email != '') {
				$this.opc.guest = true;
				$this.setAccount({id: 0, email: email});
			} else {
				$this.promptEmail();
			}
		},

		sectionOut: function () {
			// Section out from account section is trivial. It would simply mean logout and abort checkout altogether.
		},

		setAccount: function (user, refresh) {
			var $this = this;
			$this.opc.guest = !user.id;

			refresh = refresh ? refresh : 0;

			if (refresh) {
				$this.opc.refreshSections();
			}

			$this.element('btn_submit_logout').toggleClass('hidden', $this.opc.guest);
			$this.element('el_row_guest').toggleClass('hidden', !$this.opc.guest);

			$this.element('input_email').val(user.email).removeClass('hidden').attr('disabled', 'disabled');

			$this.element('el_row_email').removeClass('hidden');
			$this.element('input_userid').val(user.id);
			$this.element('btn_submit_email').addClass('hidden');
			$this.element('btn_change_email').addClass('hidden');
			$this.element('btn_submit_register').addClass('hidden');
			$this.element('btn_guest_checkout').addClass('hidden');
			$this.element('el_row_password').addClass('hidden');
		},

		promptEmail: function () {
			var $this = this;
			$this.element('el_row_email').removeClass('hidden');
			$this.element('input_email').removeClass('hidden').removeAttr('disabled').focus();
			$this.element('btn_submit_email').removeClass('hidden').removeAttr('disabled');
			$this.element('el_row_password').addClass('hidden');
			$this.element('btn_change_email').addClass('hidden');
			$this.element('btn_submit_logout').addClass('hidden');
			$this.element('btn_submit_register').addClass('hidden');

			$this.element('btn_guest_checkout').addClass('hidden');
			$this.element('el_row_guest').addClass('hidden');
		},

		promptRegister: function () {
			var $this = this;
			$this.element('btn_submit_email').addClass('hidden');
			$this.element('btn_change_email').removeClass('hidden');
			$this.element('btn_submit_logout').addClass('hidden');
			$this.element('btn_submit_register').removeClass('hidden');
			$this.element('el_row_password').addClass('hidden');
			$this.element('el_row_guest').addClass('hidden');
			$this.element('btn_guest_checkout').removeClass('hidden');
			$this.element('input_email').attr('disabled', 'disabled');
		},

		promptLogin: function () {
			var $this = this;
			$this.element('btn_submit_email').addClass('hidden');
			$this.element('btn_submit_logout').addClass('hidden');
			$this.element('btn_submit_register').addClass('hidden');
			$this.element('btn_change_email').removeClass('hidden');
			$this.element('el_row_password').removeClass('hidden');
			$this.element('el_row_guest').addClass('hidden');
			$this.element('input_email').attr('disabled', 'disabled');
			$this.element('btn_guest_checkout').removeClass('hidden');
			$this.element('input_password').focus();
		},

		checkEmail: function (email) {
			var $this = this;
			if ($this.opc.ajax) $this.opc.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'user.checkEmailAjax',
				email: email
			};
			data[$this.opc.token] = 1;
			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.opc.overlay($this);
				},
				complete: function () {
					$this.opc.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status == 1001 || response.status == 1004 || response.status == 1005) {
					// Invalid OR Blocked OR Non-activated email
					$this.promptEmail();
					$this.opc.renderMessages({warning: [response.message]}, $this.container);
				} else if (response.status == 1002) {
					// Unregistered email
					$this.promptRegister();
					$this.opc.renderMessages({success: [response.message]}, $this.container);
				} else if (response.status == 1003) {
					// Registered email
					$this.promptLogin();
					$this.opc.renderMessages({success: [response.message]}, $this.container);
				} else {
					// Some error
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		guest: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellaciousopc',
				task: 'opc.guestAjax',
				format: 'json',
				email: $this.element('input_email').val()
			};
			data[$this.opc.token] = 1;
			$this.opc.renderMessages({info: [Joomla.JText._('COM_SELLACIOUSOPC_CART_AIO_GUEST_CHECKOUT_INIT_PROGRESS')]}, $this.container);
			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.opc.overlay($this);
				},
				complete: function () {
					$this.opc.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status == 1) {
					$this.opc.setToken(response.data.token);
					$this.setAccount(response.data, 1);
					$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.edit = false;
				} else {
					$this.opc.renderMessages({warning: [response.message]}, $this.container);
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			}).always(function () {
				$this.element('btn_submit_register').removeAttr('disabled');
				$this.element('btn_change_email').removeAttr('disabled');
			});
		},

		register: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'user.registerAjax',
				email: $this.element('input_email').val()
			};
			data[$this.opc.token] = 1;

			$this.element('btn_submit_register').attr('disabled', 'disabled');
			$this.element('btn_change_email').attr('disabled', 'disabled');
			$this.opc.renderMessages({info: [Joomla.JText._('COM_SELLACIOUSOPC_CART_OPC_REGISTRATION_PROGRESS')]}, $this.container);

			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.opc.overlay($this);
				},
				complete: function () {
					$this.opc.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1001) { // Invalid email
					$this.promptEmail();
					$this.opc.renderMessages({warning: [response.message]}, $this.container);
				} else if (response.status === 1022) { // Auto Login Failed
					$this.promptEmail();
					$this.opc.renderMessages({info: [response.message]}, $this.container);
				} else if (response.status == 1011) { // Already logged in
					$this.opc.setToken(response.data.token);
					$this.opc.renderMessages({warning: [response.message]}, $this.container);
					$this.setAccount(response.data, 1);
					window.location.reload();
				} else if (response.status == 1023) { // Auto Login Success
					$this.opc.setToken(response.data.token);
					$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.setAccount(response.data, 1);
					$this.edit = false;
					window.location.reload();
				} else {                              // Registration failed
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			}).always(function () {
				$this.element('btn_submit_register').removeAttr('disabled');
				$this.element('btn_change_email').removeAttr('disabled');
			});
		},

		login: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'user.loginAjax',
				email: $this.element('input_email').val(),
				passwd: $this.element('input_password').val()
			};
			data[$this.opc.token] = 1;

			$this.element('btn_submit_password').attr('disabled', 'disabled');
			$this.element('btn_change_email').attr('disabled', 'disabled');
			$this.opc.renderMessages({info: [Joomla.JText._('COM_SELLACIOUSOPC_CART_AIO_LOGIN_PROGRESS')]}, $this.container);

			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.opc.overlay($this);
				},
				complete: function () {
					$this.opc.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1001) {         // Invalid email
					$this.promptEmail();
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				} else if (response.status === 1011) {  // Already logged in
					$this.opc.setToken(response.data.token);
					$this.opc.renderMessages({warning: [response.message]}, $this.container);
					$this.setAccount(response.data, 1);
				} else if (response.status === 1014) {  // Login Success, also update session token
					$this.opc.setToken(response.data.token);
					$this.opc.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.setAccount(response.data, 1);
					$this.edit = false;
					window.location.reload();
				} else {                                // Invalid email or password or Login Failed etc
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			}).always(function () {
				$this.element('btn_submit_password').removeAttr('disabled');
				$this.element('btn_change_email').removeAttr('disabled');
			});
		},

		saveSection: function (e) {
			var $this = this;
			var form = $this.element('form');

			if (form.data("form-changed")) {
				var email = $this.element('input_email');

				if (email.attr('disabled') != 'disabled' && !$this.element('btn_submit_email').hasClass('hidden')) {
					$this.element('btn_submit_email').trigger('click');
				}
			}
		},

		logout: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'user.logoutAjax'
			};
			data[$this.opc.token] = 1;
			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.opc.overlay($this);
				},
				complete: function () {
					$this.opc.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status == 1) {
					$this.opc.renderMessages({success: [response.message]}, $this.container);
					setTimeout(function () {
						window.location.href = window.location.href.split('#')[0];
					}, 600);
				} else {
					$this.opc.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.opc.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	};

	$(window).on('checkoutReady', function (e, $opc) {
		$opc.addSection($.extend({}, sectionAccount), 'Login', {elements: {container: '#cart-opc-account'}});
		$opc.navSection(sectionAccount.name);
	});
});
