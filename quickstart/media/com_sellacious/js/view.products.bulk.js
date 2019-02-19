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
		var btn = $('<div/>', {'class': 'btn-wrapper pull-right btn-group', id: 'btn-keyboard'});
		var $button = $('<button class="btn btn-sm btn-primary"><i class="fa fa-keyboard-o"></i> ' + Joomla.JText._('COM_SELLACIOUS_LABEL_KEYBOARD_SHORTCUTS') + '</button>');
		btn.append($button);
		$toolbar.prepend(btn);

		// Move the help tip to dom root
		$('body') // .append($('.keyboard-hint'))
			.keydown(function (e) {
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
		var loopyCopy = function (el, dir) {
			var rows = $('.product-row').length;
			var offset = dir == 'up' ? -1 : 1;
			var index = $(el).closest('.product-row').data('row');
			var suffix = $(el).attr('id').replace('jform_' + index, '');

			for (var i = parseInt(index) + offset; 0 <= i && i < rows; i += offset) {
				$('#jform_' + i + suffix).not('[readonly]').val($(el).val()).trigger('change');
			}
		};

		var navRows = function (el, dir) {
			var rows = $('.product-row').length;
			var offset = dir == 'up' ? -1 : 1;
			var index = $(el).closest('.product-row').data('row');

			var i = parseInt(index) + offset;
			var suffix = $(el).attr('id').replace('jform_' + index, '');

			if (0 <= i && i < rows) {
				$('#jform_' + i + suffix).focus();
			}
		};

		var $productList = $('#productList');

		$productList.on('change', 'input[id^="jform_"]', function (e) {
			// Should we revert too on un-check of cbN? Not yet!
			var $row = $(this).closest('.product-row');
			var index = $row.data('row');

			var changed = false;

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

			if (changed) {
				$row.addClass('row-modified');
				$('#cb' + index).prop('checked', true).triggerHandler('click');
			} else {
				$row.removeClass('row-modified');
				$('#cb' + index).prop('checked', false).triggerHandler('click');
			}
		});

		$productList.on('keyup', 'input', function (e) {
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
			} else if (e.keyCode == 13) return false;
		});
	});
})(jQuery);
