/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

SellaciousViewOPC = function () {
	this.token = '__INVALID_TOKEN__';
	this.hash = '';
	this.ajax = null;
	this.sections = {};
	this.order_id = 0;
	this.guest = false;
	this.cart_elements = {};
	this.last_focus = {};
	this.next_focus = {};

	return this;
};

jQuery(function ($) {
	SellaciousViewOPC.prototype = {
		options: {
			container: '#cart-opc-container'
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
				console.log('No container defined for Cart OPC object. Could not build checkout handler.');
				return;
			}

			$(document).on('updateToken', function (e, token) {
				$this.token = token;
			});

			$(window).trigger('checkoutReady', [$this]);

			// Call all sections
			// $this.refreshSections();
		},

		refreshSections: function(skip) {
			var $this = this;

			// Get Cart Elements
			$this.getCartElements(function(){
				$(window).trigger('checkoutReady', [$this]);
				$this.last_focus = {};

				if (Object.keys($this.next_focus).length) {
					$this.focusSection($this.next_focus.event, $this.next_focus.section);

					var elementName = $($this.next_focus.event.target).attr("name");
					$("[name='" + elementName + "']").focus();
				} else {
					$this.container.find("button, select, a").attr("disabled", true);
				}
			}, skip);
		},

		validateCart: function() {
			var $this = this;
			var sections = $this.sections;
			var errors = 0;

			for (var section in sections) {
				var forms = sections[section].handler.container.find("form");
				forms.each(function () {
					var form = $(this);
					if (!form[0].closest(".hidden") && !form[0].checkValidity()) {
						errors++;
					}
				});
			}

			if (errors) {
				return false;
			} else {
				return true;
			}
		},

		getCartElements: function (callback, skip) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellaciousopc',
				task: 'opc.getCartElementsAjax',
				format: 'json'
			};
			data[$this.token] = 1;
			$this.ajax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$this.customOverlay($this.options.container);
				},
				complete: function () {
					$this.customOverlay($this.options.container, true);
				}
			}).done(function (response) {
				if (response.success) {
					$($this.options.container).html(response.data);
					if (typeof callback == 'function') callback(response);
				} else {
					$this.renderMessages({warning: [response.message]}, $this.container)
				}
			}).fail(function (jqXHR) {
				$this.renderMessages({warning: ['Request failed due to unknown error.']}, $this.container);
				console.log(jqXHR.responseText);
			});
		},

		initCart: function (container, section, modal, sopc) {
			var $this       = this;

			$(document).on('updateToken', function (e, token) {
				$this.token = token;
			});

			$(document).trigger('cartOpcInit', [$this, container, section, modal, sopc]);
		},

		addSection: function (handler, title, options) {
			if (typeof handler != 'object' || typeof handler.name != 'string') {
				console.log('Handler not specified or handler has no name!');
				return;
			}

			var $this = this;
			var $section = {handler: handler, next: false, prev: false};

			// Try to use label from container in the HTML layout
			var hc = handler.elements['container'];
			var element = $this.container ? $this.container.find(hc) : $(hc);
			title = element ? element.find('legend,.legend').eq(0).text() || title : title;

			handler.setup($this, options);

			$this.sections[handler.name] = $section;
		},

		navSection: function (name, options) {
			var $this = this;

			if (typeof $this.sections[name] != 'object') {
				console.log('The selected checkout section: "' + name + '" is not configured.');
				return;
			}

			if(options == undefined) {
				options = {};
			}


			$this.sections[name].handler.sectionIn(options);
		},

		renderMessages: function (messages, $sectionContainer, timeOut) {
			var $this = this, type, $msgBox, i;
			var timer;

			var $container = $('#system-message-container');

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

			if (timer = $sectionContainer.data('message-timeout')) clearTimeout(timer);

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
					$this.removeMessages($sectionContainer);
				}, timeOut);
				$sectionContainer.data('message-timeout', timer);
			}
		},

		removeMessages: function ($sectionContainer) {
			if ($sectionContainer.is('.trace')) throw 'Tracing callback';

			$sectionContainer.find('.opc-message-container').fadeOut(3000, function () {
				$sectionContainer.find('.opc-message-container').empty();
			});

			$('#system-message-container').empty();
		},

		overlay: function ($section, hide) {
			if (typeof $section == 'undefined' || typeof $section.container == 'undefined') return;
			var $overlay = $section.container.find('.ajax-overlay');
			if (!$overlay.length) {
				$overlay = $('<div>', {'class': 'ajax-overlay'});
				$section.container.append($overlay);
			}
			$section.container.toggleClass('ajax-running', !hide);
		},

		customOverlay: function (element, hide) {
			if (!$(element).length) return;
			var $overlay = $(element).find('> .ajax-overlay');
			if (!$overlay.length) {
				$overlay = $('<div>', {'class': 'ajax-overlay'});
				$(element).append($overlay);
			}
			$(element).toggleClass('ajax-running', !hide);
		},

		focusSection: function(event, section) {
			var $this = $(this);

			if (event.which != undefined) {
				var sectionContainer = section.container;

				if (Object.keys(section.opc.last_focus).length) {
					if (section.opc.last_focus.section.name == section.name) {
						return;
					}

					section.opc.last_focus.section.saveSection(section.opc.last_focus.event);
					section.opc.next_focus = {section: section, event: event};
				}

				section.opc.last_focus = {section: section, event: event};
				var allSections = section.opc.sections;

				for (var s in allSections) {
					var container = allSections[s].handler.container;
					var $overlay = container.find('> .section-overlay');
					if (!$overlay.length) {
						$overlay = $('<div>', {'class': 'section-overlay'});
						container.append($overlay);
					}

					if (s !== section.name) {
						container.toggleClass('hide-section', true);
						container.find("button, select, a").attr("disabled", true);
					} else {
						container.toggleClass('hide-section', false);
						container.find("button, select, a").attr("disabled", false);
					}
				}
			}
		},

		setToken: function (token) {
			this.token = token;
			$(document).trigger('updateToken', [token]);
		}
	}
});

jQuery(document).ready(function ($) {

	$(document).on('cartOpcInit', function (event, $opc, container, section, modal, sopc) {
		if (typeof $opc == 'undefined') return;
		$opc.addSection($.extend({}, section), 'Cart', {elements: {container: container}, modal: modal, sopc: sopc});
	});
});
