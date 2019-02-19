/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


jQuery(function ($) {
	$(document).ready(function () {

		var Clazz = function () {
			return this;
		};

		Clazz.prototype = {
			init: function () {
				var that = this;
				$('.spcat_toggle').change(function () {
					that.setActive($(this));
				}).trigger('change');
				$('.spcat_updown').change('change', function () {
					that.getCost($(this));
				});
			},

			getCost: function ($el) {
				var that = this;

				var recurrence = $el.data('recurrence');
				var price = $el.data('price');
				var days = $el.val();

				recurrence = parseInt(recurrence);
				price = parseFloat(price);
				days = parseInt(days);

				recurrence = isNaN(recurrence) ? 0 : recurrence;
				price = isNaN(price) ? 0 : price;
				days = isNaN(days) ? 1 : Math.abs(days);
				// $el.val(slots * recurrence);

				var choice = $el.attr('id').replace(/_days$/, '_cat_id');
				var dest = $el.attr('id').replace(/_days$/, '_cost');

				var cost = recurrence ? (Math.ceil(days / recurrence) * price) : ($('#' + choice).is(':checked') ? price : 0);
				$('#' + dest).val(cost.toFixed(2));

				that.getTotal($el);
			},

			setActive: function ($el) {
				var that = this;
				var days = $el.attr('id').replace(/_cat_id$/, '_days');
				var cst_id = $el.attr('id').replace(/_cat_id$/, '_cost');

				var ctrl = $('#' + days);
				var cst_el = $('#' + cst_id);

				var term = ctrl.data('recurrence');
				term = parseInt(term);
				term = isNaN(term) ? 1 : term;

				if (term == 0) ctrl.hide(); // why?

				if (!$el.is(':checked')) {
					ctrl.val(0);
					ctrl.attr('disabled', 'disabled');
					cst_el.attr('disabled', 'disabled')
				} else {
					var ov = parseInt(ctrl.val());
					ov = isNaN(ov) ? 0 : ov;
					ctrl.val(ov || term);
					ctrl.removeAttr('disabled');
					cst_el.removeAttr('disabled')
				}

				that.getCost(ctrl);
			},

			getTotal: function ($el) {
				var $table = $el.closest('table');
				var $elements = $table.find('input[id$="_cost"]');
				var tot = 0.00;

				$elements.each(function (i, element) {
					var v = $(element).val();
					tot += isNaN(v) ? 0 : parseFloat(v);
				});

				$table.find('input[id$="_total"]').val(tot.toFixed(2)).trigger('change');
			}
		};

		var o = new Clazz;
		o.init();
	});
});
