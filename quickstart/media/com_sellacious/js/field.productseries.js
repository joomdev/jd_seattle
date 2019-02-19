/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


var JFormFieldProductseries = function () {
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
	JFormFieldProductseries.prototype = {

		setup : function (options) {
			$.extend(this.options, options);

			var that = this;
			var $id = '#' + that.options.id;

			$($id).on('click', '.sfpseriesrow-add', function () {
				that.addRow();
			});

			$($id).on('click', '.sfpseriesrow-remove', function () {
				var index = this.id.match(/\d+$/);
				that.removeRow(index);
			});

			$($id).on('keyup', '.sfpseriesrow-copy', function (e) {
				if (!e.altKey && !e.shiftKey && e.ctrlKey && (e.keyCode == 38 || e.keyCode == 40)) {
					var dir = e.keyCode == 38 ? 'up' : 'down';
					that.loopyCopy(this, dir);
				}
			});

			$($id).on('change', '.sfpseriesrow-qmax, .sfpseriesrow-qmin, .sfpseriesrow-cp, .sfpseriesrow-margin, .sfpseriesrow-fp, .sfpseriesrow-ovrprice', function () {
				var index = this.id.match(/\d+$/);
				that.recalculate(index);
			});
		},

		addRow : function () {
			var $id = '#' + this.options.id;
			var index = ++this.options.rowIndex;
			var template = this.options.rowTemplate.html;
			var replacement = this.options.rowTemplate.replacement;
			var html = template.replace(new RegExp(replacement, "ig"), index);
			$(html).insertBefore($($id + ' .sfpseries-blankrow'));

			// initialize select2 on new elements
			$($id + '_state_' + index).css('width', '100%').select2();
		},

		removeRow : function (index) {
			var $id = '#' + this.options.id;
			$($id + '_sfpseriesrow_' + index).remove();
		}
	}
})(jQuery);
