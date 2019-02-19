/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


var JFormFieldProductPrices = function () {
	this.options = {
		id : 'jform',
		rowTemplate : {
			html : '',
			replacement : ''
		},
		rowIndex : 0
	};
};

(function ($) {
	JFormFieldProductPrices.prototype = {

		setup : function (options) {
			$.extend(this.options, options);

			var that = this;
			var $id = '#' + that.options.id;

			$($id).on('click', '.sfpprow-add', function () {
				that.addRow();
			});

			$($id).on('click', '.sfpprow-remove', function () {
				var index = this.id.match(/\d+$/);
				var $this = $(this);

				if ($this.data('confirm')) {
					$this.data('confirm', false);
					$this.html('<i class="fa fa-lg fa-times"></i> ');
					that.removeRow(index);
				} else {
					$this.data('confirm', true);
					$this.html('<i class="fa fa-lg fa-question-circle"></i> ');
					setTimeout(function () {
						$this.data('confirm', false);
						$this.html('<i class="fa fa-lg fa-times"></i> ');
					}, 5000);
				}
			});

			$($id).on('keyup', '.sfpprow-copy', function (e) {
				if (!e.altKey && !e.shiftKey && e.ctrlKey && (e.keyCode == 38 || e.keyCode == 40)) {
					var dir = e.keyCode == 38 ? 'up' : 'down';
					that.loopyCopy(this, dir);
				}
			});

			$($id).on('change', '.sfpprow-qmax,.sfpprow-qmin,.sfpprow-cp,.sfpprow-lp,.sfpprow-margin,.sfpprow-fp,.sfpprow-ovrprice', function () {
				var index = this.id.match(/\d+$/);
				that.recalculate(index);
			});

			$($id).on('click', '.sfpprow-margin-type', function () {
				var index = this.id.match(/\d+$/);
				that.recalculate(index);
			});
		},

		addRow : function () {
			var id = this.options.id;
			var $id = '#' + this.options.id;
			var index = ++this.options.rowIndex;
			var template = this.options.rowTemplate.html;
			var replacement = this.options.rowTemplate.replacement;
			var html = template.replace(new RegExp(replacement, "ig"), index + "");
			$(html).insertBefore($($id).find('.sfpp-blankrow'));

			// initialize select2 on new elements
			$($id + '_cat_id_' + index).css('width', '100%').select2();
			$($id + '_discnt_' + index).css('width', '100%').select2();

			// Initialize calendars
			$(document).ready(function() {
				Calendar.setup({
					inputField: id + '_sdate_' + index,
					ifFormat: "%Y-%m-%d",
					align: "Tl",
					singleClick: true,
					firstDay: 0
				});
				Calendar.setup({
					inputField: id + '_edate_' + index,
					ifFormat: "%Y-%m-%d",
					align: "Tl",
					singleClick: true,
					firstDay: 0
				});
			});

			// redisplay hidden buttons
			$($id + ' .sfpprow-remove').removeAttr('disabled');
		},

		removeRow : function (index) {
			$('#' + this.options.id + '_sfpprow_' + index).remove();
		},

		recalculate : function (index) {
			var zero = 0;
			var $id = '#' + this.options.id;

			var $cost_price = $($id + '_cost_price_' + index);
			var $list_price = $($id + '_list_price_' + index);
			var $margin = $($id + '_margin_' + index);
			var $margin_type = $($id + '_margin_type_' + index);
			var $calculated_price = $($id + '_calculated_price_' + index);
			var $override_price = $($id + '_ovr_price_' + index);
			var $min_qty = $($id + '_qty_min_' + index);
			var $max_qty = $($id + '_qty_max_' + index);

			var cost_price = parseFloat($cost_price.val());
			var list_price = parseFloat($list_price.val());
			var margin = parseFloat($margin.val());
			var override_price = parseFloat($override_price.val());
			var min_qty = parseInt($min_qty.val());
			var max_qty = parseInt($max_qty.val());
			var margin_type = $margin_type.is(':checked');

			cost_price = isNaN(cost_price) ? zero : cost_price;
			list_price = isNaN(list_price) ? zero : list_price;
			margin = isNaN(margin) ? zero : margin;
			override_price = isNaN(override_price) ? zero : override_price;
			min_qty = isNaN(min_qty) ? zero : min_qty;
			max_qty = isNaN(max_qty) ? zero : max_qty;

			var calculated_price = margin_type ? cost_price * (1 + margin / 100) : cost_price + margin;

			$cost_price.val(cost_price.toFixed(2));
			$list_price.val(list_price.toFixed(2));
			$margin.val(margin.toFixed(2));
			$calculated_price.val(calculated_price.toFixed(2));
			$override_price.val(override_price.toFixed(2));
			$min_qty.val(min_qty.toFixed(0));
			$max_qty.val(max_qty.toFixed(0));
		},

		loopyCopy : function (el, dir, chk) {
			var $id = '#' + this.options.id;
			var index = el.id.match(/\d+$/);
			var ctx = $(el).data('ctx');
			var value = el.value;
			var els = $($id).find('[data-ctx="'+ctx+'"]');
			var found = false;

			$.each(els, function (i, elem) {
				found = found ? found : elem == el;
				if (dir == 'up') {
					if (!found) {
						elem.value = value;
						$(elem).trigger('change');
					}
				} else if (dir == 'down') {
					if (found) {
						elem.value = value;
						$(elem).trigger('change');
					}
				}
			});

			// loopyCopy the included append field too, if any
/*			var include = $(el).data('include');

			if (include) {
				this.loopyCopy(document.getElementById(include), dir, 1);
			}
*/
		}
	}
})(jQuery);
