/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var JFormFieldText2d = function () {
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
	JFormFieldText2d.prototype = {

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
		},

		addRow : function () {
			var id = this.options.id;
			var $id = '#' + this.options.id;
			var index = ++this.options.rowIndex;
			var template = this.options.rowTemplate.html;
			var replacement = this.options.rowTemplate.replacement;
			var html = template.replace(new RegExp(replacement, "ig"), index + "");
			$(html).insertBefore($($id).find('.sfpp-blankrow'));

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
			var $id = '#' + this.options.id;
			$($id + '_sfpprow_' + index).remove();
		}
	}
})(jQuery);
