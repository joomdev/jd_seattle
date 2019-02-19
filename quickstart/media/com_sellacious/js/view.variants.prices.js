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

		// Inject keyboard shortcut button into toolbar. Injecting here because this is only coupled here.
		var $toolbar = $('#toolbar');
		var btn = $('<div>', {'class': 'btn-wrapper btn-group pull-right', id: 'btn-keyboard'});
		var $button = $('<button class="btn btn-sm btn-primary"><i class="fa fa-keyboard-o"></i> ' + Joomla.JText._('COM_SELLACIOUS_LABEL_KEYBOARD_SHORTCUTS') + '</button>');
		btn.append($button);
		$toolbar.prepend(btn);

		// Move the help tip to dom root
		$('body').keydown(function (e) {
			if (e.ctrlKey && e.keyCode == 191) {
				$('.keyboard-hint').toggleClass('active');
				return false;
			}
		});

		$('.keyboard-close').click(function () {
			$('.keyboard-hint').toggleClass('active');
		});

		$button.click(function () {
			$('.keyboard-hint').toggleClass('active');
		});

		// Now implement the actions
		var navigate = function (el, dir, callback) {
			var $c = $(el).closest('.product-row,.variant-row');
			var idx = $c.data('row');
			// This assumes all the navigated items to be siblings
			while (true) {
				$c = dir == 'up' ? $c.prev() : $c.next();
				if ($c.index() < 0) break;
				var cid = $c.data('row');
				if (typeof cid != 'undefined' && cid == idx) {
					if (callback.apply($c) === false) break;
				}
			}
		};

		var loopyCopy = function (el, dir) {
			var value = $(el).val();
			var field = $(el).data('field');
			navigate(el, dir, function () {
				var $fld;
				if ($fld = $(this).find('.' + field)) {
					$fld.not('[readonly]').val(value).trigger('change');
				}
			});
		};

		var navRows = function (el, dir) {
			var field = $(el).data('field');
			navigate(el, dir, function () {
				var $fld;
				if ($fld = $(this).find('.' + field)) {
					$fld.focus();
					return false;
				}
			});
		};

		var recalculate = function ($row) {
			// recalculate
			var $b = $row.find('.basic-price');
			var p = $row.find('.product-price').val();
			var m = $row.find('.margin').val();
			var t = $row.find('.margin-type').prop('checked');

			p = parseFloat(p);
			m = parseFloat(m);

			p = isNaN(p) ? 0 : p;
			m = isNaN(m) ? 0 : m;

			var c = t ? p * m / 100.0 : m;
			$b.val((p + c).toFixed(2));
		};

		$('#productList')
			.on('change', 'input[id^="jform_"]', function (e) {
				var changed = false;
				var $row = $(this).closest('.product-row,.variant-row');
				$row.find('input[id^="jform_"]').each(function () {
					var $el = $(this), o, n;
					if ($el.is('[type="checkbox"]')) {
						o = $el.prop('defaultChecked');
						n = $el.prop('checked');
					} else {
						o = $el.prop('defaultValue');
						n = $el.val();
					}
					if (o == n) {
						$el.removeClass('input-modified');
					} else {
						changed = true;
						$el.addClass('input-modified');
					}
				});
				changed ? $row.addClass('row-modified') : $row.removeClass('row-modified');
				recalculate($row);
			})
			.on('change', 'input.margin', function () {
				var amt = $(this).val();
				var $row = $(this).closest('.variant-row');
				var $marginType = $row.find('input.margin-type');

				var value = parseFloat(amt.replace(/%|\$|#/g, ''));
				$(this).val(isNaN(value) ? '0.00' : value.toFixed(2));

				if (/\$|#/.test(amt)) $marginType.prop('checked', true).trigger('click');
				else if (/%/.test(amt)) $marginType.prop('checked', false).trigger('click');
			})
			.on('keyup', 'input[data-field]', function (e) {
				if (e.keyCode == 38 || e.keyCode == 40) {
					var dir = e.keyCode == 38 ? 'up' : 'down';
					if (!e.ctrlKey) {
						if (e.shiftKey && e.altKey) {
							loopyCopy(this, dir);
						} else if (!e.shiftKey && !e.altKey) {
							navRows(this, dir);
						}
					}
					return false;
				}
				else if (e.keyCode === 13) {
					return false;
				}
			})
			.on('change', '.product-row .product-price', function () {
				var value = $(this).val();
				navigate(this, 'down', function () {
					var $fld;
					if ($fld = $(this).find('.product-price')) {
						$fld.val(value).trigger('change');
						recalculate($(this));
					}
				});
			});
	});
})(jQuery);
