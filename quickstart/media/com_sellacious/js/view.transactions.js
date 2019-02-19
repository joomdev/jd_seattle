/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($) {
	"use strict";
	var t = function (e) {
		this.init("txn_status", e, t.defaults)
	};
	$.fn.editableutils.inherit(t, $.fn.editabletypes.abstractinput);
	$.extend(t.prototype, {
		render: function () {
			this.$input = this.$tpl.find(":input");
		},
		value2html: function (t, n) {
			if (!t) {
				$(n).empty();
				return
			}
			var r = $("<div>").text(t.status).html();
			$(n).html(r)
		},
		html2value: function (e) {
			return null
		},
		value2str: function (e) {
			var t = "";
			if (e)
				for (var n in e)
					if (e.hasOwnProperty(n))
						t = t + n + ":" + e[n] + ";";
			return t
		},
		str2value: function (e) {
			return e
		},
		value2input: function (e) {
			if (!e)
				return;
			this.$input.filter('[name="status"]').val(e.status);
			this.$input.filter('[name="user_notes"]').val('');
		},
		input2value: function () {
			return {
				status: this.$input.filter('[name="status"]').val(),
				user_notes: this.$input.filter('[name="user_notes"]').val()
			}
		},
		activate: function () {
			this.$input.filter('[name="status"]').focus()
		},
		autosubmit: function () {
			this.$input.keydown(function (t) {
				t.which === 13 && $(this).closest("form").submit()
			})
		}
	});
	t.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
		tpl: '<div class="editable-txn_status padding-10">' +
				'<div class="row">' +
					'<div class="col-md-3"><label><span>Status <span class="star">*</span></span></label></div>' +
					'<div class="col-md-9">' +
						'<select name="status" class="form-control">' +
							'<option value=""></option>' +
							'<option value="-1">Disapproved</option>' +
							'<option value="1">Approved</option>' +
						'</select>' +
					'</div>' +
				'</div>' +
				'<div class="row padding-top-10">' +
					'<div class="col-md-3"><label><span>Notes <span class="star">*</span></span></label></div>' +
					'<div class="col-md-9">' +
						'<textarea name="user_notes" class="form-control" rows="2"></textarea>' +
					'</div>' +
				'</div>' +
			'</div>',
		inputclass: ""
	});
	$.fn.editabletypes.txn_status = t
})(window.jQuery);

(function($) {
	$(document).ready(function ($) {
		$('.txn-state-2').each(function () {
			var el = $(this);
			el.editable({
				url: 'index.php?option=com_sellacious&task=transaction.setApproveAjax',
				title: 'Select new status:',
				placement: 'left',
				value: {
					status: 2,
					user_notes: ''
				},
				ajaxOptions: {
					dataType: 'json'
				},
				validate: function (value) {
					if (value.status != 1 && value.status != -1)
						return 'Select either Approved or Disapproved status.';
					if ($.trim(value.user_notes).length < 1)
						return 'Enter some description/notes for this update.';
				},
				display: function (value) {
					if (!value) {
						$(this).empty();
						return;
					}
					var html = $('<span>').html(Joomla.JText._('COM_SELLACIOUS_TRANSACTION_HEADING_STATE_X_' + value.status));
					$(this).html(html);

					value.status == 2 || el.editable('destroy');
				},
				success: function (response) {
					if (response.state != 1) {
						return response.message || response.state + ': Update failed.';
					}
					return true;
				}
			});
		});
	});
})(window.jQuery);
