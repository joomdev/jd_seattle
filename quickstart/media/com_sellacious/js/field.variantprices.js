/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


var JFormFieldVariantPrices = function () {
	this.options = {
		id         : null,
		rowTemplate: {
			html        : '',
			replacements: ''
		}
	};
	this.element = null;
};

(function ($) {
	JFormFieldVariantPrices.prototype = {

		setup: function (options) {
			$.extend(this.options, options);

			var $that = this;
			$that.element = $('#' + $that.options.id);

			$that.rebuild();

			$(document).on('addVariant', function (event, data) {
				$that.addRow(data);
				$that.rebuild();
			});

			$(document).on('removeVariant', function (event, id) {
				$that.removeRow(id);
				$that.rebuild();
			});
		},

		rebuild: function () {
			var $that = this;
			if ($that.element.find('tbody').find('tr').length == 0) {
				$that.element.find('thead').addClass('hidden');
				$that.element.find('tfoot').removeClass('hidden');
			} else {
				$that.element.find('thead').removeClass('hidden');
				$that.element.find('tfoot').addClass('hidden');
			}
		},

		replaceCodes: function (data) {
			data = data || {};
			var $that = this;
			var html = $that.options.rowTemplate.html;
			var codes = $that.options.rowTemplate.replacements;

			$.each(codes, function (key, code) {
				if (key != 'fields') {
					html = html.replace(new RegExp(code, 'g'), data[key] || '');
				}
			});

			var fields = [];
			var $html = $(html);
			var $specs = $html.find('.variant-specs');

			$.each(data.fields.slice(0, 3), function (fdi, field_data) {
				var field_html = $specs.html();
				$.each(codes.fields[0] || {}, function (key, code) {
					field_html = field_html.replace(code, field_data[key] || '');
				});
				fields.push(field_html);
			});

			$specs.html(fields.join('')).append('&hellip;');

			return $html;
		},

		addRow: function (data) {
			var $that = this;
			var $new_row = $that.replaceCodes(data);
			var $row = $that.element.find('#' + $that.options.id + '_variant-row-' + data.id);

			if ($row.length) {
				// This is a quick fix (and appropriate too) to retain the editable (e.g. price mod, stock) value for existing variant
				var $summary = $new_row.find('.variant-summary');
				$row.find('.variant-summary').replaceWith($summary);
			} else {
				$that.element.find('tbody').append($new_row);
			}
		},

		removeRow: function (id) {
			var $that = this;
			$that.element.find('#' + $that.options.id + '_variant-row-' + id).fadeOut('slow').remove();
		}
	}
})(jQuery);
