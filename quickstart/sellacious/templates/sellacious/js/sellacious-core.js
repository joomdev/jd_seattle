/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($) {

	var $navbar_height = 0;   // Note: You will also need to change this variable in the "variable.less" file.

	// so far this is covering most hand held devices
	var isMobile = (/iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));

	/**
	 * NAV or #LEFT-BAR resize detect
	 *
	 * Description: changes the page min-width of #CONTENT and NAV when navigation is resized.
	 *              This is to counter bugs for min page width on many desktop and mobile devices.
	 *
	 * Note:        This script uses JSthrottle technique so don't worry about memory/CPU usage
	 */
	function nav_page_height() {
		var setHeight = $('#main').height();

		var windowHeight = $(window).height() - $navbar_height;

		// if content height exceedes actual window height and menuHeight
		var hMenu = (setHeight > windowHeight) ? setHeight : windowHeight;
		var hBody = (setHeight > windowHeight) ? setHeight + $navbar_height : windowHeight;

		$('#left-panel').css('min-height', hMenu + 'px');
		$('body').css('min-height', hBody + 'px');

	}

	/*
	 * FULL SCREEN FUNCTION
	 */

	// Find the right method, call on correct element
	function launchFullscreen(element) {
		var $body = $('body');

		if (!$body.hasClass("full-screen")) {
			$body.addClass("full-screen");
			if (element.requestFullscreen) {
				element.requestFullscreen();
			} else if (element.mozRequestFullScreen) {
				element.mozRequestFullScreen();
			} else if (element.webkitRequestFullscreen) {
				element.webkitRequestFullscreen();
			} else if (element.msRequestFullscreen) {
				element.msRequestFullscreen();
			}
		} else {
			$body.removeClass("full-screen");
			if (document.exitFullscreen) {
				document.exitFullscreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
			}
		}
	}

	/*
	 * END: FULL SCREEN FUNCTION
	 */

	$(document).ready(function () {

		var $body = $('body');

		if (!isMobile) {
			// Desktop
			$body.addClass('desktop-detected');
		} else {
			// Mobile
			$body.addClass('mobile-detected');
			FastClick.attach(document.body);    		// Removes the tap delay in idevices - dependency: js/plugin/fastclick/fastclick.js
		}

		nav_page_height();

		$('#main').resize(function () {
			nav_page_height();

			if ($(window).width() < 979) {
				if (!$body.is('.mobile-view-activated')) {
					// Its important to hide this only first time,
					// else any height change would also make this happen
					$('#myTab3').hide();
				}
				$body.addClass('mobile-view-activated')
					.removeClass('minified');
				$.cookie('collapsedmenu', 0);
			} else if ($body.hasClass('mobile-view-activated')) {
				$body.removeClass('mobile-view-activated');
				$body.removeClass('hidden-menu');
				// Let's reset mobile view state of the tab bar already
				$('.tabbar-toggler').toggleClass('tabs-close', false).data('open', false);
				$('#myTab3').show();
			}
		});

		$('nav').resize(function () {
			nav_page_height();
		});

		// Collapse Left NAV
		$('.minifyme').click(function (e) {
			var c = $body.toggleClass('minified').is('.minified');
			$.cookie('collapsedmenu', c ? 1 : 0);
			e.preventDefault();
		});

		$('[data-menu="hidemenu"]').click(function () {
			$body.toggleClass('hidden-menu');
		});

		var $cacheBtn = $('[data-action="rebuild-cache"]');

		if ($cacheBtn.data('state') === 1) {
			var i;
			var f = function () {
				$.ajax({
					url: 'index.php?option=com_sellacious&task=checkCacheAjax',
					type: 'get',
					cache: false,
					dataType: 'json'
				}).done(function (response) {
					// Not running = 1, Running = 2
					if (response.state === 1) {
						clearInterval(i);
						$cacheBtn.removeClass('btn-disabled bg-color-white txt-color-red')
							.data('state', 0).find('i.fa').removeClass('fa-spin');
						/view=products/.test(window.location.href)
							? window.location.href = window.location.href + '' : null;
					}
				});
			};
			i = setInterval(f, 3000);
		}

		$body.on("click", '[data-action="launchFullscreen"]', function (b) {
			b.preventDefault();
			launchFullscreen(document.documentElement);
		}).on("click", '[data-action="sync-media"]', function (b) {
			b.preventDefault();
			var $this = $(this);
			$this.find('.fa').addClass('fa-spin');
			$.ajax({
				url: 'index.php?option=com_sellacious&task=media.syncAjax',
				type: 'POST',
				cache: false,
				dataType: 'json'
			}).done(function (response) {
				if (response.state === 1) {
					Joomla.renderMessages({success: [response.message]});
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (xhr) {
				console.log(xhr.responseText);
				Joomla.renderMessages({error: ['Sync failed due to a system error.']});
			}).always(function () {
				$this.find('.fa').removeClass('fa-spin')
			});
		}).on("click", '[data-action="rebuild-cache"]', function (b) {
			b.preventDefault();
			var $this = $(this);

			if (parseInt($this.data('state')) === 1) { return false; }

			$this.find('.fa').addClass('fa-spin');
			var $form = $('<form/>', {action: window.location.href, method: 'post'});

			$('<input>', {type: 'hidden', name: 'option', value: 'com_sellacious'}).appendTo($form);
			$('<input>', {type: 'hidden', name: 'task', value: 'products.refreshCache'}).appendTo($form);
			$('<input>', {type: 'hidden', name: $(this).data('token'), value: 1}).appendTo($form);
			$form.appendTo($body).submit();
		}).on("click", '[data-action="system-autofix"]', function (b) {
			b.preventDefault();
			var $this = $(this);
			$this.find('.fa').addClass('fa-spin');
			var $form = $('<form/>', {action: window.location.href, method: 'post'});

			$('<input>', {type: 'hidden', name: 'option', value: 'com_sellacious'}).appendTo($form);
			$('<input>', {type: 'hidden', name: 'task', value: 'systemAutofix'}).appendTo($form);
			$('<input>', {type: 'hidden', name: $(this).data('token'), value: 1}).appendTo($form);
			$form.appendTo($body).submit();
		}).on("click", '[data-action="switchLanguage"][data-lang]', function (b) {
			b.preventDefault();
			var $this = $(this);
			var lang = $this.data('lang');
			$this.find('.fa').addClass('fa-spin');
			var $form = $('<form/>', {action: window.location.href, method: 'post'});
			$('<input>', {type: 'hidden', name: 'lang', value: lang}).appendTo($form);
			$form.appendTo($body).submit();
		}).on("click", '[data-action="support"][data-href]', function (b) {
			b.preventDefault();
			var $this = $(this);
			var href = $this.data('href');
			$this.find('.fa').addClass('fa-spin');
			$.ajax({
				url: 'index.php?option=com_sellacious&task=activation.supportPINAjax',
				type: 'POST',
				cache: false,
				dataType: 'json'
			}).done(function (response) {
				var link;
				if (response.status === 1) {
					var token = response.data.pin + ':' + response.data.key;
					link = 'https://www.sellacious.com/index.php?option=com_jarvis&task=site.login&token=' + token + '&next=' + encodeURIComponent(href);
					// Update on page token
					$body.find('[data-action="support-pin"]').find('span').text(response.data.pin).addClass('hasPin');
				} else {
					link = 'https://www.sellacious.com/' + href;
				}
				window.open(link, '_blank');
			}).fail(function (xhr) {
				console.log(xhr.responseText);
				window.open('https://www.sellacious.com/' + href, '_blank');
			}).always(function () {
				$this.find('.fa').removeClass('fa-spin')
			});
		}).on("click", '[data-action="support-pin"]', function (b) {
			b.preventDefault();
			var $this = $(this);
			if ($this.is('.hasPin')) return;
			$this.find('.fa').addClass('fa-spin');
			$.ajax({
				url: 'index.php?option=com_sellacious&task=activation.supportPINAjax',
				type: 'POST',
				cache: false,
				dataType: 'json'
			}).done(function (response) {
				if (response.status === 1) {
					$this.addClass('hasPin').find('span').text(response.data.pin);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (xhr) {
				console.log(xhr.responseText);
				Joomla.renderMessages({error: ['Sync failed due to a system error.']});
			}).always(function () {
				$this.find('.fa').removeClass('fa-spin')
			});
		});

		// Keep only 1 active popover per trigger -
		// also check and hide active popover if user clicks on document
		$body.on('click', function (e) {
			$('[rel="popover"]').each(function () {
				// the 'is' for buttons that trigger popups
				// the 'has' for icons within a button that triggers a popup
				if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
					$(this).popover('hide');
				}
			});
		});

		// css class workaround for sellacious template
		$('input.inputbox,.textarea').addClass('form-control');

		$('.premium-input').each(function () {
			var closest = $(this).closest('.input-container');
			if (closest.length === 0) closest = $(this).closest('div.controls');
			var e = $('<span class="premium-input-pre"></span>');
			closest.prepend(e);
		});

		if (typeof $.fn.select2 === 'function') {
			$('.hasSelect2').select2();
		}

		$('.tabbar-toggler').click(function (e) {
			var open = $(this).data('open') || false;
			var $myTab3 = $('#myTab3');
			open ? $myTab3.slideUp(300) : $myTab3.slideDown(300);
			$(this).toggleClass('tabs-close', !open).data('open', !open);
		});

		$(document).on('show.bs.tab', 'a[data-toggle="tab"]', function (e) {
			const $tabbar = $('.tabbar-toggler');
			$tabbar.find('.active-tab').text($(this).text());
			if ($body.is('.mobile-view-activated')) $tabbar.triggerHandler('click');
		});

		$("#smartymenu").mCustomScrollbar({theme: "light-thick"});
	});

})(jQuery);

/**
 * Resizer with throttle
 * Source: http://benalman.com/code/projects/jquery-resize/examples/resize/
 */
(function ($, window, undefined) {

	var $throttle_delay = 350;  // Impacts the responce rate of some of the responsive elements (lower value affects CPU but improves speed)

	var elems = $([]), jq_resize = $.resize = $.extend($.resize, {}), timeout_id, str_setTimeout = 'setTimeout', str_resize = 'resize', str_data = str_resize + '-special-event', str_delay = 'delay', str_throttle = 'throttleWindow';

	jq_resize[str_delay] = $throttle_delay;

	jq_resize[str_throttle] = true;

	$.event.special[str_resize] = {

		setup: function () {
			if (!jq_resize[str_throttle] && this[str_setTimeout]) {
				return false;
			}

			var elem = $(this);
			elems = elems.add(elem);
			$.data(this, str_data, {
				w: elem.width(),
				h: elem.height()
			});
			if (elems.length === 1) {
				loopy();
			}
		},

		teardown: function () {
			if (!jq_resize[str_throttle] && this[str_setTimeout]) {
				return false;
			}

			var elem = $(this);
			elems = elems.not(elem);
			elem.removeData(str_data);
			if (!elems.length) {
				clearTimeout(timeout_id);
			}
		},

		add: function (handleObj) {
			if (!jq_resize[str_throttle] && this[str_setTimeout]) {
				return false;
			}
			var old_handler;

			function new_handler(e, w, h) {
				var elem = $(this), data = $.data(this, str_data);
				data.w = w !== undefined ? w : elem.width();
				data.h = h !== undefined ? h : elem.height();

				try {
					old_handler.apply(this, arguments);
				} catch (e) {
					// ignore the undefined tip error
				}
			}

			if ($.isFunction(handleObj)) {
				old_handler = handleObj;
				return new_handler;
			} else {
				old_handler = handleObj.handler;
				handleObj.handler = new_handler;
			}
		}
	};

	function loopy() {
		timeout_id = window[str_setTimeout](function () {
			elems.each(function () {
				var elem = $(this), width = elem.width(), height = elem.height(), data = $.data(this, str_data);
				if (width !== data.w || height !== data.h) {
					elem.trigger(str_resize, [data.w = width, data.h = height]);
				}

			});
			loopy();
		}, jq_resize[str_delay]);
	}
})(jQuery, window);
