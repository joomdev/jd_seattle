/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
SellaciousViewCartAIO = function () {
	this.token = '__INVALID_TOKEN__';
	this.hash = '';
	this.ajax = null;
	this.steps = {};
	this.first = '';
	this.last = '';
	this.order_id = 0;
	this.guest = false;
	this.baseUrl = 'index.php';

	return this;
};

jQuery(function ($) {
	$.fn.hScroll = function (amount) {
		amount = amount || 120;
		$(this).bind('DOMMouseScroll mousewheel', function (event) {
			var origEvent = event.originalEvent;
			var direction = origEvent.detail ? origEvent.detail * -amount : origEvent.wheelDelta, position = $(this).scrollLeft();
			position += direction > 0 ? -amount : amount;
			$(this).scrollLeft(position);
			event.preventDefault();
		});
	};

	SellaciousViewCartAIO.prototype = {
		options: {
			container: '#cart-aoi-container',
			el_step_progressbar: '#cart-aio-steps'
		},

		element: function (name) {
			return this.container.find(this.options[name]);
		},

		init: function (container, options) {
			var $this = this;

			$.extend($this.options, options);

			if (container) $this.options.container = container;
			$this.container = $($this.options.container);

			if ($this.container.length === 0) {
				console.log('No container defined for Cart AIO object. Could not build checkout handler.');
				return;
			}

			var paths = Joomla.getOptions('system.paths', {});
			$this.baseUrl = (paths.base || paths.root || '') + '/index.php';

			$(document).on('updateToken', function (e, token) {
				$this.token = token;
			});

			$(document).trigger('checkoutInit', [$this]);

			$(document).trigger('checkoutReady', [$this]);

			// Call first step
			$this.navStep($this.first);
		},

		initCart: function (container, modal) {
			var $this = this;

			var paths = Joomla.getOptions('system.paths', {});
			$this.baseUrl = (paths.base || paths.root || '') + '/index.php';

			$(document).on('updateToken', function (e, token) {
				$this.token = token;
			});

			$(document).trigger('cartInit', [$this, container, modal]);
		},

		addStep: function (handler, title, options) {
			if (typeof handler !== 'object' || typeof handler.name !== 'string') {
				console.log('Handler not specified or handler has no name!');
				return;
			}

			var $this = this;
			var $step = {handler: handler, next: false, prev: false};

			// Try to use label from container in the HTML layout
			var hc = handler.elements['container'];
			var element = $this.container ? $this.container.find(hc) : $(hc);
			title = element ? element.find('legend,.legend').eq(0).text() || title : title;

			// HTML should be abstracted somewhere
			if (this.container) {
				this.element('el_step_progressbar').find('ul.steps').append(
					'<li id="aio-step-' + handler.name + '" class="">' +
					'<span class="step-counter"></span><span class="text hidden-phone">' + title + '</span>' +
					'<span class="chevron"></span></li>'
				);
			}

			// Stack the steps and build doubly linked list
			if ($this.first == '') {
				$this.first = handler.name;
				$this.last = handler.name;
			} else {
				$step.prev = $this.last;
				$this.steps[$this.last].next = handler.name;
				$this.last = handler.name;
			}

			handler.setup($this, options);

			$this.steps[handler.name] = $step;
		},

		hideStep: function (name) {
			this.element('el_step_progressbar').find('ul.steps').find('#aio-step-' + name).hide();
		},

		showStep: function (name) {
			this.element('el_step_progressbar').find('ul.steps').find('#aio-step-' + name).show();
		},

		setStep: function (step) {
			if (this.container) {
				var $pb = this.element('el_step_progressbar');
				$pb.find('li.active').removeClass('active');
				$pb.find('#aio-step-' + step).addClass('active');
			}
		},

		navStep: function (name, relative) {
			var $this = this;

			if (typeof $this.steps[name] != 'object') {
				console.log('The selected checkout step: "' + name + '" is not configured.');
				return;
			}

			if (relative) {
				var reference = $this.steps[name][relative];
				if (typeof $this.steps[reference] != 'object') {
					console.log('The selected checkout step: "' + reference + '" referencing "' + relative + '" to "' + name + '" is not configured.');
					return;
				}
				name = reference;
			}

			var stepIn = false;
			var current = $this.first;
			var $step;

			while (current) {
				$step = $this.steps[current];
				if (current == name) {
					stepIn = true;
				} else if (stepIn) {
					$step.handler.stepOut();
				}
				current = $step.next;
			}

			$this.steps[name].handler.stepIn();
		},

		renderMessages: function (messages, $stepContainer, timeOut) {
			var $this = this, type, $msgBox, i;
			var timer;

			if ($stepContainer) {
				var $container = $stepContainer.find('.aio-message-container');
				if (!$container.length) {
					$container = $('<div>', {'class': 'aio-message-container'}).prependTo($stepContainer);
					// Move fieldset legend back to first
					$stepContainer.find('legend,.legend').eq(0).prependTo($stepContainer);
				}
			} else {
				$container = $('#system-message-container');
			}
			$container.empty().show();

			var classNames = {
				notice: 'alert alert-info',
				message: 'alert alert-success'
			};
			var ucFirst = function (t) {
				return t.replace(/^(\w)/, function (m) {
					return m.toUpperCase();
				});
			};

			if (timer = $stepContainer.data('message-timeout')) clearTimeout(timer);

			for (type in messages) {
				if (!messages.hasOwnProperty(type)) continue;

				$msgBox = $('<div/>', {'class': classNames[type] || 'alert alert-' + type});

				$('<button/>', {type: 'button', 'data-dismiss': 'alert', 'class': 'close'}).text('×').appendTo($msgBox);
				$('<h4/>', {'class': 'alert-heading'}).html(Joomla.JText._(type) || ucFirst(type)).appendTo($msgBox);

				for (i = messages[type].length - 1; i >= 0; i--) {
					$('<div/>').html(messages[type][i]).appendTo($msgBox);
				}

				$msgBox.appendTo($container);
			}

			if (timeOut != null) {
				timer = setTimeout(function () {
					$this.removeMessages($stepContainer);
				}, timeOut);
				$stepContainer.data('message-timeout', timer);
			}
		},

		removeMessages: function ($stepContainer) {
			if ($stepContainer.is('.trace')) throw 'Tracing callback';

			$stepContainer.find('.aio-message-container').fadeOut(3000, function () {
				$stepContainer.find('.aio-message-container').empty();
			});
		},

		overlay: function ($step, hide) {
			if (typeof $step == 'undefined' || typeof $step.container == 'undefined') return;
			var $overlay = $step.container.find('.ajax-overlay');
			if (!$overlay.length) {
				$overlay = $('<div>', {'class': 'ajax-overlay'});
				$step.container.append($overlay);
			}
			$step.container.toggleClass('ajax-running', !hide);
		},

		setToken: function (token) {
			this.token = token;
			$(document).trigger('updateToken', [token]);
		}
	};

	// Account Step - Login or registration
	var stepAccount = {

		name: 'account',

		elements: {
			container: '#cart-aio-account',
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

		setup: function ($aio, elements) {
			var $this = this;
			$this.aio = $aio;

			$.extend($this.elements, elements);
			$this.container = $($this.elements.container);

			$this.container.on('click', '.btn-edit', function () {
				$this.edit = true;
				$this.aio.navStep($this.name);
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
				if (confirm(Joomla.JText._('COM_SELLACIOUS_CART_CONFIRM_LOGOUT_ACTION_MESSAGE', 'Are you sure you want to logout?')))
					$this.logout();
			});
		},

		stepIn: function () {
			var $this = this;
			$this.aio.setStep($this.name);
			var email = $this.element('input_email').val();
			var userid = $this.element('input_userid').val();
			userid = parseInt(userid);
			userid = isNaN(userid) ? 0 : userid;
			if ($this.edit) {
				$this.promptEmail();
			} else if (userid && email != '') {
				$this.setAccount({id: userid, email: email});
			} else if ($this.element('input_guest_flag').val() == 1 && email != '') {
				$this.aio.guest = true;
				$this.setAccount({id: 0, email: email});
			} else {
				$this.promptEmail();
			}
		},

		stepOut: function () {
			// Step out from account step is trivial. It would simply mean logout and abort checkout altogether.
		},

		setAccount: function (user) {
			var $this = this;
			$this.aio.guest = !user.id;

			$this.element('btn_submit_logout').toggleClass('hidden', $this.aio.guest);
			$this.element('el_row_guest').toggleClass('hidden', !$this.aio.guest);

			$this.element('input_email').val(user.email).removeClass('hidden').attr('disabled', 'disabled');

			$this.element('el_row_email').removeClass('hidden');
			$this.element('input_userid').val(user.id);
			$this.element('btn_submit_email').addClass('hidden');
			$this.element('btn_change_email').addClass('hidden');
			$this.element('btn_submit_register').addClass('hidden');
			$this.element('btn_guest_checkout').addClass('hidden');
			$this.element('el_row_password').addClass('hidden');

			$this.aio.navStep($this.name, 'next');
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
			if ($this.aio.ajax) $this.aio.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'user.checkEmailAjax',
				email: email
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1001 || response.status === 1004 || response.status === 1005) {
					// Invalid OR Blocked OR Non-activated email
					$this.promptEmail();
					$this.aio.renderMessages({warning: [response.message]}, $this.container);
				} else if (response.status === 1002) {
					// Unregistered email
					$this.promptRegister();
					$this.aio.renderMessages({success: [response.message]}, $this.container);
				} else if (response.status === 1003) {
					// Registered email
					$this.promptLogin();
					$this.aio.renderMessages({success: [response.message]}, $this.container);
				} else {
					// Some error
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		guest: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'cart.guestAjax',
				format: 'json',
				email: $this.element('input_email').val()
			};
			data[$this.aio.token] = 1;
			$this.aio.renderMessages({info: [Joomla.JText._('COM_SELLACIOUS_CART_AIO_GUEST_CHECKOUT_INIT_PROGRESS')]}, $this.container);
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					$this.aio.setToken(response.data.token);
					$this.setAccount(response.data);
					$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container);
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
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
			data[$this.aio.token] = 1;

			$this.element('btn_submit_register').attr('disabled', 'disabled');
			$this.element('btn_change_email').attr('disabled', 'disabled');
			$this.aio.renderMessages({info: [Joomla.JText._('COM_SELLACIOUS_CART_AIO_REGISTRATION_PROGRESS')]}, $this.container);

			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1001) { // Invalid email
					$this.promptEmail();
					$this.aio.renderMessages({warning: [response.message]}, $this.container);
				} else if (response.status === 1022) { // Auto Login Failed
					$this.promptEmail();
					$this.aio.renderMessages({info: [response.message]}, $this.container);
				} else if (response.status === 1011) { // Already logged in
					$this.aio.setToken(response.data.token);
					$this.aio.renderMessages({warning: [response.message]}, $this.container);
					$this.setAccount(response.data);
				} else if (response.status === 1023) { // Auto Login Success
					$this.aio.setToken(response.data.token);
					$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.setAccount(response.data);
				} else {                              // Registration failed
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
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
			data[$this.aio.token] = 1;

			$this.element('btn_submit_password').attr('disabled', 'disabled');
			$this.element('btn_change_email').attr('disabled', 'disabled');
			$this.aio.renderMessages({info: [Joomla.JText._('COM_SELLACIOUS_CART_AIO_LOGIN_PROGRESS')]}, $this.container);

			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1001) {         // Invalid email
					$this.promptEmail();
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				} else if (response.status === 1011) {  // Already logged in
					$this.aio.setToken(response.data.token);
					$this.aio.renderMessages({warning: [response.message]}, $this.container);
					$this.setAccount(response.data);
				} else if (response.status === 1014) {  // Login Success, also update session token
					$this.aio.setToken(response.data.token);
					$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.setAccount(response.data);
				} else {                                // Invalid email or password or Login Failed etc
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			}).always(function () {
				$this.element('btn_submit_password').removeAttr('disabled');
				$this.element('btn_change_email').removeAttr('disabled');
			});
		},

		logout: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'user.logoutAjax'
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					$this.aio.renderMessages({success: [response.message]}, $this.container);
					setTimeout(function () {
						window.location.href = window.location.href.split('#')[0];
					}, 600);
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	};

	// Cart Step - Manage cart items and quantity
	var stepCart = {

		name: 'cart',

		elements: {
			container: '#cart-aio-items',
			cart_items: '#cart-items',
			cart_folded: '#cart-items-folded'
		},

		options: {modal: false},

		element: function (name) {
			return this.container.find(this.elements[name]);
		},

		setup: function ($aio, options) {
			var $this = this;
			$this.aio = $aio;

			if (options) {
				if (options.elements) {
					$.extend($this.elements, options.elements);
					delete options.elements;
				}
				$.extend($this.options, options);
			}
			$this.container = $($this.elements.container);

			$this.container

				.on('click', '.btn-clear-cart', function () {
					if (confirm(Joomla.JText._('COM_SELLACIOUS_CART_CONFIRM_CLEAR_CART_ACTION_MESSAGE')))
						$this.clearCart();
				})

				.on('click', '.btn-remove-item', function () {
					var uid = $(this).data('uid');
					$this.remove(uid);
					return false;
				})

				.on('change', '.item-quantity', function () {
					var old = $(this).data('value');

					var quantity = parseInt($(this).val());
					quantity = isNaN(quantity) || quantity < 1 ? 1 : quantity;
					$(this).val(quantity);
					if (quantity != old) $this.setQuantity($(this), quantity);
					return false;
				})

				.on('click', '.btn-refresh', function () {
					$this.showCart();
				})

				.on('click', '.btn-next', function () {
					$this.foldCart(function () {
						$this.aio.navStep($this.name, 'next');
					});
				})

				.on('click', '.btn-edit', function () {
					// Do not call self stepIn directly as there may be some cleanup required
					$this.aio.navStep($this.name);
				});
		},

		stepIn: function () {
			var $this = this;
			$this.aio.setStep($this.name);

			$this.container.removeClass('hidden');
			$this.showCart();
		},

		stepOut: function () {
			// Hide all elements as they all will be reloaded later
			var $this = this;
			$this.container.addClass('hidden');
			$this.element('cart_items').empty().addClass('hidden');
			$this.element('cart_folded').empty().addClass('hidden');
		},

		remove: function (uid) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'cart.removeItemAjax',
				format: 'json',
				uid: uid
			};

			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					$(document).trigger('cartUpdate', ['remove', {uid: uid}]);
					$this.showCart(function () {
						$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
					});
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		clearCart: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'cart.clearAjax',
				format: 'json'
			};

			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
					$(document).trigger('cartUpdate', ['clear']);
					window.location.reload(true);
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		showCart: function (callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();

			$this.element('cart_folded').empty().addClass('hidden');
			$this.element('cart_items').removeClass('hidden');

			var data = {
				option: 'com_sellacious',
				task: 'cart.getItemsHtmlAjax',
				format: 'json',
				modal: $this.options.modal ? 1 : 0,
				readonly: 0
			};

			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1031 || response.status !== 1) {
					$this.element('cart_items').html('<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
						'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>');
					// Not logged in or a failure
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				} else {
					// HTML Returned
					$this.element('cart_items').html(response.data).find('.hasTooltip').tooltip();
					if (typeof callback === 'function') callback(response);
				}
			}).fail(function (jqXHR) {
				$this.element('cart_items').html('<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
					'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>');
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		setQuantity: function ($element, value) {
			var $this = this;
			var uid = $element.data('uid');
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'cart.setQuantityAjax',
				format: 'json',
				uid: uid,
				quantity: value
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					$(document).trigger('cartUpdate', ['quantity', {uid: uid, quantity: value}]);
					$this.showCart(function () {
						$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
					});
				} else {
					$element.val($element.data('value'));
					$this.aio.renderMessages({warning: [response.message]}, $this.container);
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		foldCart: function (callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'cart.getSummaryAjax',
				format: 'json'
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					// Values Returned
					var cart = response.data;
					$this.aio.hash = cart.hash;
					var html = '<div class="clearfix"></div>' +
						'<span class="h2">' + (cart.count || 0) + (parseInt(cart.count || 0) > 1 ? ' Items' : ' Item') + '</span>' +
						'<a class="btn btn-small pull-right btn-default btn-edit"><i class="fa fa-edit"></i> Change</a>';

					$this.element('cart_items').addClass('hidden');
					$this.element('cart_folded').html(html).removeClass('hidden');

					if (typeof callback === 'function') callback(response);
				} else if (response.status === 1031) {
					// Not logged in
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	};

	// Address Step - Manage Addresses and Ship To & Bill To choices
	var stepAddress = {
		name: 'address',

		formFields: {},

		elements: {
			container: '#cart-aio-address',
			editor: '#address-editor',
			viewer: '#address-viewer',
			viewer_billing: '#address-billing-text',
			viewer_shipping: '#address-shipping-text',
			boxes_container: '#address-items',
			modals_container: '#address-modals',
			input_billing: '#address-billing',
			input_shipping: '#address-shipping',
			box_single_class: '.address-item',
			btn_ship_here: '.btn-ship-here',
			btn_bill_here: '.btn-bill-here',
			modal_form: '.address-form-content',
			btn_add_new: '.btn-add-address'
		},

		element: function (name) {
			return this.container.find(this.elements[name]);
		},

		setup: function ($aio, elements) {
			var $this = this;
			$this.aio = $aio;

			$.extend($this.elements, elements);
			$this.container = $($this.elements.container);

			$this.container

				.on('click', '.btn-ship-here', function () {
					var address_id = $(this).data('id');

					$this.element('input_shipping').val(address_id);
					$this.element('btn_ship_here').removeClass('active').removeClass('btn-success');

					$(this).addClass('btn-success active');
					$this.element('editor').addClass('has-shipping');
				})

				.on('click', '.btn-bill-here', function () {
					var address_id = $(this).data('id');

					$this.element('input_billing').val(address_id);
					$this.element('btn_bill_here').removeClass('active').removeClass('btn-success');

					$(this).addClass('btn-success active');
					$this.element('editor').addClass('has-billing');
				})

				.on('click', '.remove-address', function () {
					var address_id = $(this).data('id');
					if (address_id && confirm(Joomla.JText._('COM_SELLACIOUS_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE')))
						$this.remove(address_id);
				})

				.on('click', '.btn-save-address', function () {
					var formKey = $this.element('modal_form', true);
					var $form = $(this).closest('.modal').find(formKey);
					var data = $this.validate($form);

					if (data) $this.save(data, function () {
						$('#address-form-' + data.id).modal('hide');
						// Reset the form filled values in add new address
						if (data.id == 0) {
							var fields = $this.getFormFields();
							$.each(fields, function (fieldKey) {
								if (fieldKey !== 'id') {
									var $field = $form.find('.address-' + fieldKey).filter(':input');
									$field.data('select2') ? $field.select2('val', '') : $field.val('');
								}
							});
						}
					});
				})

				.on('click', '.btn-next', function () {
					var billTo = $this.element('input_billing').val();
					var shipTo = $this.element('input_shipping').val();

					billTo = parseInt(billTo);
					billTo = isNaN(billTo) ? 0 : billTo;
					shipTo = parseInt(shipTo);
					shipTo = isNaN(shipTo) ? 0 : shipTo;

					var hasBT = $this.element('editor').is('.has-billing');
					var hasST = $this.element('editor').is('.has-shipping');

					var isShippable = $this.element('boxes_container').data('shippable');

					var btSet = billTo && hasBT;
					var stSet = (shipTo && hasST) || !isShippable;

					if (btSet && stSet) {
						$this.aio.removeMessages($this.container);
						$this.setSelected(billTo, shipTo, function (response) {
							$this.element('editor').addClass('hidden');
							$this.element('viewer').removeClass('hidden');
							$this.aio.navStep($this.name, 'next');
						});
					} else if (btSet) {
						$this.aio.renderMessages({warning: [Joomla.JText._('COM_SELLACIOUS_CART_ADDRESS_SHIPPING_EMPTY_MESSAGE')]}, $this.container);
					} else if (stSet) {
						$this.aio.renderMessages({warning: [Joomla.JText._('COM_SELLACIOUS_CART_ADDRESS_BILLING_EMPTY_MESSAGE')]}, $this.container);
					} else {
						$this.aio.renderMessages({warning: [Joomla.JText._('COM_SELLACIOUS_CART_ADDRESSES_EMPTY_MESSAGE')]}, $this.container);
					}
				})

				.on('click', '.btn-edit', function () {
					// Do not call self stepIn directly as there may be some cleanup required
					$this.aio.navStep($this.name);
				});
		},

		stepIn: function () {
			var $this = this;
			$this.aio.setStep($this.name);
			$this.container.removeClass('hidden');
			$this.loadEditor();
		},

		stepOut: function () {
			// Hide all elements as they all will be reloaded later
			var $this = this;
			$this.container.addClass('hidden');
			$this.element('editor').addClass('hidden');
			$this.element('viewer').addClass('hidden');
		},

		loadEditor: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();

			$this.element('editor').removeClass('hidden');
			$this.element('viewer').addClass('hidden');
			$this.element('boxes_container').html('<br><h3>Please wait&hellip;</h3>').removeClass('hidden');
			$this.element('modals_container').html('');

			var data = {
				option: 'com_sellacious',
				task: 'cart.getAddressesHtmlAjax',
				format: 'json'
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1031) {
					// Not logged in
					$this.aio.renderMessages({warning: [response.message]}, $this.container);
					setTimeout(function () {
						window.location.reload();
					}, 2000);
				} else if (response.status === 1032) {
					$this.element('boxes_container').html(response.data[0]).data('shippable', response.data[2] || false);
					$this.element('modals_container').html(response.data[1]);

					$this.element('boxes_container').find('.hasTooltip').tooltip();
					$this.element('boxes_container').find('.hasSelect2').select2();
					$this.element('boxes_container').popover({trigger: 'hover'});

					$this.element('modals_container').find('.hasTooltip').tooltip();
					$this.element('modals_container').find('.hasSelect2').select2();
					$this.element('modals_container').popover({trigger: 'hover'});

					var shipTo = $this.element('input_shipping').val();
					var billTo = $this.element('input_billing').val();

					$this.element('editor').removeClass('has-shipping');
					$this.element('editor').removeClass('has-billing');

					if (shipTo) $this.element('btn_ship_here').each(function () {
						if ($(this).data('id') == shipTo) {
							$(this).addClass('btn-success active');
							$this.element('editor').addClass('has-shipping');
						}
					});

					if (billTo) $this.element('btn_bill_here').each(function () {
						if ($(this).data('id') == billTo) {
							$(this).addClass('btn-success active');
							$this.element('editor').addClass('has-billing');
						}
					});

					// Open first edit form automatically
					var $boxes = $this.element('box_single_class') || [];
					if ($boxes.length === 0) $this.element('btn_add_new').click();
				} else {
					$this.element('boxes_container').html('<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
						'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>');
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.element('boxes_container').html('<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
					'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>');
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			}).always(function () {
				if ($this.aio.guest) {
					var $boxes = $this.element('box_single_class') || [];
					$boxes.find('.remove-address').addClass('hidden');
					$this.element('btn_add_new').toggleClass('hidden', $boxes.length >= 2);
				}
			});
		},

		remove: function (address_id) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'user.removeAddressAjax',
				id: address_id
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1031) {
					// Not logged in
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				} else if (response.status === 1033) {
					// Removed
					$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.loadEditor();
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		getFormFields: function () {
			var $this = this;
			// Optimised to calculate only once
			if (Object.keys($this.formFields).length === 0) {
				var forms = $this.element('modal_form');
				if (forms.length) {
					forms.eq(0).find('[class*=" address-"],[class^="address-"]').each(function () {
						var field = $(this).attr('class').match(/address-([\w]+)/i);
						if (field) $this.formFields[field[1]] = $(this).is('.required') ? 1 : 0;
					});
				}
			}
			return $this.formFields;
		},

		validate: function ($form) {
			var $this = this;
			var data = {};
			var invalid = {};
			var valid = true;

			var fields = $this.getFormFields();

			$.each(fields, function (fieldKey, required) {
				var field = $form.find('.address-' + fieldKey);
				var field_input = field.is('fieldset') ? field.find('input:checked') : field.filter(':input');
				var labels = $(field).closest('tr').find('label');
				var value = $.trim(field_input.val());
				if (required && value === '') {
					field_input.addClass('invalid');
					labels.addClass('invalid');
					valid = false;
					invalid[fieldKey] = value;
				} else {
					field_input.removeClass('invalid');
					labels.removeClass('invalid');
					data[fieldKey] = value;
				}
			});
			if (valid)
				return data;
			$this.aio.renderMessages({warning: ['Invalid or incomplete address form.']}, $this.container);
			console.log('Invalid form:', data, invalid);
			return false;
		},

		save: function (address, callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'cart.saveAddressAjax',
				format: 'json',
				address: address,
				id: address.id
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1031) { // Not logged in
					$this.container.find('#address-form-' + address.id).modal('hide');
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				} else if (response.status === 1035) { // Saved
					$this.container.find('#address-form-' + address.id).modal('hide');
					$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.loadEditor();

					if (typeof callback === 'function') callback();
				} else {
					alert(response.message);
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		setSelected: function (bill_to, ship_to, callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'cart.setAddressesAjax',
				format: 'json',
				billing: bill_to,
				shipping: ship_to
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					var $billTo = $this.element('btn_bill_here').filter('.active').closest($this.elements.box_single_class);
					var $shipTo = $this.element('btn_ship_here').filter('.active').closest($this.elements.box_single_class);

					var $billToViewer = $this.element('viewer_billing');
					var $shipToViewer = $this.element('viewer_shipping');
					var fields = $this.getFormFields();

					$.each(fields, function (fieldKey, required) {
						if ($billTo.length) $billToViewer.find('.address_' + fieldKey).text($billTo.find('.address_' + fieldKey).text());
						if ($shipTo.length) $shipToViewer.find('.address_' + fieldKey).text($shipTo.find('.address_' + fieldKey).text());
					});

					$billToViewer.toggleClass('hidden', !$billTo.length);
					$shipToViewer.toggleClass('hidden', !$shipTo.length);

					if (typeof callback === 'function') callback(response);
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	};

	// Shipment Step - Select shipment methods and any forms that may be required for the selected method of order shipment
	var stepShipment = {

		name: 'shipment',

		elements: {
			container: '#cart-aio-shippingform',
			form_editor: '#shippingform-editor',
			form_folded: '#shippingform-folded',
			shipping_form: 'form#shipment-form',
			form_viewer: '#shippingform-response'
		},

		element: function (name) {
			return this.container.find(this.elements[name]);
		},

		setup: function ($aio, elements) {
			var $this = this;
			$this.aio = $aio;

			$.extend($this.elements, elements);
			$this.container = $($this.elements.container);

			// Keep this step hidden initially, we'll show it if/when its needed
			// $this.aio.hideStep($this.name);

			$this.container

				.on('change', 'select.select-shipment', function () {
					var uid = $(this).data('uid');
					var val = $(this).val();
					var prefix = 'shipment_form' + (uid ? '_' + uid : '');
					var elc = prefix;
					var eli = prefix + '_' + val;

					$this.container.find('.' + elc).removeClass('active');
					$this.container.find('#' + eli).addClass('active');
				})

				.on('click', '.btn-next', function () {
					$this.saveForm(function () {
						$this.element('form_editor').addClass('hidden');
						$this.element('form_folded').removeClass('hidden');
						$this.aio.navStep($this.name, 'next');
					});
				})

				.on('click', '.btn-edit', function () {
					// Do not call self stepIn directly as there may be some cleanup required
					$this.aio.navStep($this.name);
				});
		},

		stepIn: function () {
			var $this = this;
			$this.aio.setStep($this.name);
			$this.container.removeClass('hidden');
			$this.showForms();
		},

		stepOut: function () {
			// Hide all elements as they all will be reloaded later
			var $this = this;
			$this.container.addClass('hidden');
			$this.element('form_editor').addClass('hidden');
			$this.element('form_folded').addClass('hidden');
		},

		showForms: function (callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();

			$this.element('form_editor').removeClass('hidden').html('<br><h3>Please wait&hellip;</h3>');
			$this.element('form_folded').addClass('hidden');
			var data = {
				option: 'com_sellacious',
				task: 'cart.getShippingFormAjax',
				format: 'json'
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status !== 1) {
					$this.aio.showStep($this.name);
					$this.aio.renderMessages({warning: [response.message]}, $this.container);
					$this.element('form_editor').addClass('hidden').empty();
				} else if (response.data) {
					// Have some HTML
					$this.aio.showStep($this.name);
					var $container = $this.element('form_editor');
					$container.html(response.data);
					$container.find('.hasTooltip').tooltip();
					$container.find('.hasSelect2').select2();
					$container.find('.hasPopover').popover({trigger: 'hover'});
					$container.find('.hasCalendar').dcalendarpicker();
					if (typeof callback === 'function') callback(response);
				} else {
					// No HTML, No Form, Hide step -> force step out
					$this.element('form_editor').html('<br><h5>No shipment method available.</h5>');
					if (typeof callback === 'function') callback(response);

					$this.aio.hideStep($this.name);
					$this.stepOut();
					$this.aio.navStep($this.name, 'next');
				}
			}).fail(function (jqXHR) {
				$this.aio.showStep($this.name);
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				$this.element('form_editor').empty().addClass('hidden');
				console.log(jqXHR.responseText);
			});
		},

		saveForm: function (callback) {
			var $this = this;
			var $form = $this.element('shipping_form');
			var $formData = $form.serializeArray();

			// Just in case the form has some file inputs
			var $fileData = new FormData();
			$form.find('input[type="file"]').each(function () {
				if (this.files && this.files.length) {
					$fileData.append($(this).attr('name'), this.files[0]);
				}
			});
			$.each($formData, function (index, input) {
				$fileData.append(input.name, input.value);
			});
			$fileData.append($this.aio.token, '1');

			if ($this.ajax) $this.ajax.abort();
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl + '?option=com_sellacious&task=cart.saveShippingFormAjax&format=json',
				type: 'POST',
				data: $fileData,
				cache: false,
				dataType: 'json',
				// Don't process the files
				processData: false,
				// Set content type to false as jQuery will tell the server its a query string request
				contentType: false,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.element('form_viewer').html(response.data);
					if (typeof callback === 'function') callback();
				} else if (response.message == '') {
					if (typeof callback === 'function') callback();
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	};

	// Checkout questions Step - Miscellaneous questions or even customer survey can be put here.
	var stepCheckoutForm = {

		name: 'checkoutform',

		elements: {
			container: '#cart-aio-checkoutform',
			form_editor: '#checkoutform-editor',
			form: 'form#checkoutform-container',
			viewer: '#checkoutform-folded'
		},

		element: function (name) {
			return this.container.find(this.elements[name]);
		},

		setup: function ($aio, elements) {
			var $this = this;
			$this.aio = $aio;

			$.extend($this.elements, elements);
			$this.container = $($this.elements.container);

			// Keep this step hidden initially, we'll show it if/when its needed
			$this.aio.hideStep($this.name);

			$this.container

				.on('click', '.btn-next', function () {
					$this.saveForm(function () {
						$this.element('form_editor').addClass('hidden');
						$this.element('viewer').removeClass('hidden');
						// Prepare viewer here
						$this.aio.navStep($this.name, 'next');
					});
				})

				.on('click', '.btn-edit', function () {
					// Do not call self stepIn directly as there may be some cleanup required
					$this.aio.navStep($this.name);
				})
		},

		stepIn: function () {
			var $this = this;
			$this.aio.setStep($this.name);
			$this.container.removeClass('hidden');
			$this.showForm();
		},

		stepOut: function () {
			// Hide all elements as they all will be reloaded later
			var $this = this;
			$this.container.addClass('hidden');
			$this.element('form_editor').addClass('hidden');
			$this.element('viewer').addClass('hidden');
		},

		showForm: function (callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			$this.aio.showStep($this.name);
			$this.container.removeClass('hidden');
			$this.element('form').html('<br><h3>Please wait&hellip;</h3>');
			$this.element('form_editor').removeClass('hidden');
			$this.element('viewer').addClass('hidden');

			var data = {
				option: 'com_sellacious',
				task: 'cart.getCheckoutFormAjax',
				format: 'json'
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status !== 1) {
					$this.aio.renderMessages({warning: [response.message]}, $this.container);
					$this.element('form').empty().addClass('hidden');
				} else if (response.data) {
					// Have some HTML
					var $container = $this.element('form');
					$container.html(response.data);
					$container.find('.hasTooltip').tooltip();
					$container.find('.hasSelect2').select2();
					$container.find('.hasPopover').popover({trigger: 'hover'});
					$container.find('.hasCalendar').dcalendarpicker();
					$this.element('form_editor').removeClass('hidden');
					if (typeof callback === 'function') callback(response);
				} else {
					// No HTML, No Form, Hide step
					$this.aio.hideStep($this.name);
					$this.container.addClass('hidden');
					$this.element('form_editor').addClass('hidden');
					$this.aio.navStep($this.name, 'next');
					if (typeof callback === 'function') callback(response);
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				$this.element('form').empty();
				$this.element('form_editor').addClass('hidden');
				console.log(jqXHR.responseText);
			});
		},

		saveForm: function (callback) {
			var $this = this;

			var $form = $this.element('form');
			var $formData = $form.serializeArray();

			var $fileData = new FormData();
			$form.find('input[type="file"]').each(function () {
				if (this.files && this.files.length) {
					$fileData.append($(this).attr('name'), this.files[0]);
				}
			});
			$.each($formData, function (index, input) {
				$fileData.append(input.name, input.value);
			});
			$fileData.append($this.aio.token, 1);

			if ($this.ajax) $this.ajax.abort();
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl + '?option=com_sellacious&task=cart.saveCheckoutFormAjax&format=json',
				type: 'POST',
				data: $fileData,
				cache: false,
				dataType: 'json',
				processData: false, // Don't process the files
				contentType: false,  // Set content type to false as jQuery will tell the server its a query string request
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
					$this.element('viewer').html(response.data).removeClass('hidden');
					if (typeof callback === 'function') callback(response);
				} else if (response.message == '') {
					if (typeof callback === 'function') callback(response);
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	};

	// Summary Step - Summary of the cart to show how and what for the customer is going to be charged
	var stepSummary = {

		name: 'summary',

		elements: {
			container: '#cart-aio-summary',
			cart_items: '#summary-items',
			cart_folded: '#summary-folded',
			input_coupon: 'input.coupon-code'
		},

		element: function (name) {
			return this.container.find(this.elements[name]);
		},

		setup: function ($aio, elements) {
			var $this = this;
			$this.aio = $aio;

			$.extend($this.elements, elements);
			$this.container = $($this.elements.container);

			$this.container

				.on('click', '.btn-refresh', function () {
					$this.showSummary();
				})

				.on('click', '.btn-next', function () {
					$this.foldSummary(function () {
						$this.aio.navStep($this.name, 'next');
					});
				})

				.on('click', '.btn-edit', function () {
					// Do not call self stepIn directly as there may be some cleanup required
					$this.aio.navStep($this.name);
				})

				.on('click', '.shoprule-info-toggle', function () {
					var uid = $(this).data('uid');
					$(this).find('i').toggleClass('fa-plus-square-o').toggleClass('fa-minus-square-o');
					$('.' + uid + '-info').toggleClass('hidden');
					return false;
				})

				.on('click', '.btn-apply-coupon', function () {
					var $input = $this.element('input_coupon');
					var code = $input.is('.readonly') ? '' : $input.val();
					$this.setCoupon(code);
				});
		},

		stepIn: function () {
			var $this = this;
			$this.aio.setStep($this.name);
			$this.container.removeClass('hidden');
			$this.showSummary();
		},

		stepOut: function () {
			// Hide all elements as they all will be reloaded later
			var $this = this;
			$this.container.addClass('hidden');
			$this.element('cart_items').addClass('hidden');
			$this.element('cart_folded').addClass('hidden');
		},

		showSummary: function (callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();

			$this.element('cart_folded').empty().addClass('hidden');
			$this.element('cart_items').html('<br><h3>Please wait&hellip;</h3>').removeClass('hidden');

			var data = {
				option: 'com_sellacious',
				task: 'cart.getItemsHtmlAjax',
				format: 'json',
				modal: 0,
				readonly: 1
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1031 || response.status !== 1) {
					$this.element('cart_items').html('<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
						'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>');
					// Not logged in or a failure
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				} else {
					// HTML Returned
					$this.element('cart_items').html(response.data).find('.hasTooltip').tooltip();
					if (typeof callback === 'function') callback(response);
				}
			}).fail(function (jqXHR) {
				$this.element('cart_items').html('<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
					'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>');
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		foldSummary: function (callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'cart.getSummaryAjax',
				format: 'json'
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					// Values Returned
					var cart = response.data;
					$this.aio.hash = cart.hash;
					var count = (cart.count || 0) + (parseInt(cart.count || 0) > 1 ? ' Items' : ' Item');

					var lbl = Joomla.JText._('COM_SELLACIOUS_CART_GRAND_TOTAL_LABEL');
					var total = cart['total_formatted'];
					var html = '<table class="w100p">' +
						'<tr>' +
						'<td class="center"><span class="h2">' + count + '</span></td>' +
						'<td class="center">' + lbl + ': <span class="grand-total strong"> ' + total + '</span></td>' +
						'<td><button type="button" class="btn btn-small pull-right btn-default btn-edit"><i class="fa fa-edit"></i> Change</button></td>' +
						'</tr>' +
						'</table>';

					$this.element('cart_items').addClass('hidden');
					$this.element('cart_folded').html(html).removeClass('hidden');

					if (typeof callback === 'function') callback(response);
				} else if (response.status === 1031) {
					// Not logged in
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		setCoupon: function (code) {
			var $this = this;
			var data = {
				option: 'com_sellacious',
				task: 'cart.setCouponAjax',
				format: 'json',
				code: code
			};
			data[$this.aio.token] = 1;

			if ($this.ajax) $this.ajax.abort();
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					$this.showSummary(function () {
						$this.aio.renderMessages({success: [response.message]}, $this.container, 2000);
					});
				} else {
					alert(response.message);
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		}
	};

	// Payment Step - Customer to make payment for the order
	var stepPayment = {

		name: 'payment',

		elements: {
			container: '#cart-aio-payment',
			forms_container: '#payment-forms'
		},

		element: function (name) {
			return this.container.find(this.elements[name]);
		},

		setup: function ($aio, elements) {
			var $this = this;
			$this.aio = $aio;

			$.extend($this.elements, elements);
			$this.container = $($this.elements.container);

			$this.container.on('click', '.btn-pay-now', function () {
				var $form = $(this).closest('form');
				var validData = $this.validate($form);
				if (validData === false) return;

				// If order is already place but we are stuck here for payment, allow graceful bypass without breaking.
				if ($this.aio.order_id > 0) {
					$this.aio.renderMessages({success: [Joomla.JText._('COM_SELLACIOUS_CART_REDIRECT_WAIT_MESSAGE')]}, $this.container);
					$this.executePayment(validData);
				} else {
					// Do not assign data to cart, hold it - place order - call payment with this data
					$this.placeOrder(function (response) {
						$this.aio.order_id = response.data;
						$this.executePayment(validData);
					});
				}
			});

			$this.container.on('click', '.btn-refresh', function () {
				$this.aio.navStep($this.name);
			});
		},

		stepIn: function () {
			var $this = this;
			$this.aio.setStep($this.name);
			$this.container.removeClass('hidden');

			$this.showOptions();
		},

		stepOut: function () {
			// Hide all elements as they all will be reloaded later
			var $this = this;
			$this.container.addClass('hidden');
		},

		showOptions: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'cart.getPaymentFormsAjax',
				format: 'json'
			};
			data[$this.aio.token] = 1;
			$this.element('forms_container').html('<br><h3>Please wait&hellip;</h3>').removeClass('hidden');
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					// HTML Returned
					$this.element('forms_container').html(response.data).find('.hasTooltip').tooltip();
				} else if (response.status === 1031) {
					// Not logged in
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				} else {
					$this.aio.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		validate: function ($form) {
			var $this = this;
			var $sysMsg = $('#system-message-container').empty();

			if (!document.formvalidator.isValid($form[0])) {
				$this.aio.renderMessages({}, $this.container);
				$this.container.find('.aio-message-container').html($sysMsg.html());
				$sysMsg.empty();
				return false;
			}
			var $formData = $form.serializeArray();

			var $fileData = new FormData();
			$form.find('input[type="file"]').each(function () {
				if (this.files && this.files.length) {
					$fileData.append($(this).attr('name'), this.files[0]);
				}
			});
			$.each($formData, function (index, input) {
				$fileData.append(input.name, input.value);
			});
			$fileData.append($this.aio.token, 1);

			return $fileData;
		},

		placeOrder: function (callback) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'cart.placeOrderAjax',
				format: 'json',
				hash: $this.aio.hash
			};
			data[$this.aio.token] = 1;
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					// Do not hide the message automatically
					$this.aio.renderMessages({success: [response.message]}, $this.container);
					if (typeof callback === 'function') callback(response);
				} else if (response.status === 1031 || response.status === 1041) {
					// Not logged in or Hash mismatch
					$this.aio.renderMessages({warning: [response.message]}, $this.container);
				} else {
					var msg = Joomla.JText._('COM_SELLACIOUS_CART_ORDER_PAYMENT_INIT_FAILURE');
					$this.aio.renderMessages({warning: [response.message, msg]}, $this.container);
				}
			}).fail(function (jqXHR) {
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		executePayment: function ($data) {
			var $this = this;
			$data.append('id', $this.aio.order_id);
			if ($this.ajax) $this.ajax.abort();
			$this.ajax = $.ajax({
				url: $this.aio.baseUrl + '?option=com_sellacious&task=order.setPaymentAjax',
				type: 'POST',
				data: $data,
				cache: false,
				dataType: 'json',
				processData: false, // Don't process the files
				contentType: false,  // Set content type to false as jQuery will tell the server its a query string request
				beforeSend: function () {
					$this.aio.overlay($this);
				},
				complete: function () {
					$this.aio.overlay($this, true);
				}
			}).done(function (response) {
				if (response.status === 1) {
					window.location.href = response['redirect'];
				} else {
					$this.aio.renderMessages({warning: [response.message, Joomla.JText._('COM_SELLACIOUS_CART_REDIRECT_WAIT_MESSAGE')]}, $this.container);
					window.location.href = response['redirect'];
				}
			}).fail(function (xhr) {
				console.log(xhr.responseText);
				$this.aio.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
			});
		}
	};

	$(document).on('checkoutInit', function (event, $aio) {
		if (typeof $aio === 'undefined') return;

		$aio.addStep($.extend({}, stepAccount), 'Login');
		$aio.addStep($.extend({}, stepAddress), 'Addresses');
		$aio.addStep($.extend({}, stepShipment), 'Shipment');
		$aio.addStep($.extend({}, stepCheckoutForm), 'Questions');
		$aio.addStep($.extend({}, stepSummary), 'Review');
		$aio.addStep($.extend({}, stepPayment), 'Payment');
	});

	$(document).on('cartInit', function (event, $aio, container, modal) {
		if (typeof $aio === 'undefined') return;
		$aio.addStep($.extend({}, stepCart), 'Cart', {elements: {container: container}, modal: modal});
	});
});
