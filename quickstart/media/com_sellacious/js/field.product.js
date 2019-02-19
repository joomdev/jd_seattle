/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

var JFormFieldProduct = function () {
	this.options = {
		id: 'jform',
		name: 'jform'
	};
};

(function ($) {
	JFormFieldProduct.prototype = {
		setup: function (options) {
			$.extend(this.options, options);
			this.select2();
			return this;
		},

		select2: function () {
			var $this = this;
			var $id = '#' + this.options.id;

			$($id).select2({
				allowClear: true,
				multiple: $this.options.multiple,
				minimumInputLength: 1,
				placeholder: $this.options.hint || ' ',
				ajax: {
					url: 'index.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							option: 'com_sellacious',
							task: 'product.autocomplete',
							query: term,
							type: $this.options.type,
							context: $this.options.context,
							list_start: 10 * (page - 1),
							list_limit: 10
						};
					},
					results: function (response, page) {
						// Parse the results into the format expected by Select2.
						// If we are using custom formatting functions we do not need to alter remote JSON data
						var results = [];
						if (response.status == 1) {
							$.each(response.data, function (i, v) {
								results.push({id: v.id, text: (v.full_title || v.title)});
							});
						}
						return {results: results};
					}
				},
				initSelection: function (element, callback) {
					// The input tag has a value attribute preloaded that points to a preselected items id
					// This function resolves that id attribute to an object that select2 can render
					// using its formatResult renderer - that way the item title is shown preselected
					var values = $(element).val();

					if (values == '' || values == '0') return;

					$.ajax({
						url: 'index.php',
						dataType: 'json',
						data: {
							option: 'com_sellacious',
							task: 'product.getInfoAjax',
							id: values.split(','),
							type: $this.options.type,
							context: $this.options.context
						}
					}).done(function (response) {
						var results = [];
						var new_values = [];
						if (response.status == 1) {
							$.each(response.data, function (i, v) {
								new_values.push(v.id);
								results.push({id: v.id, text: (v.full_title || v.title)});
							});
						}
						$(element).val(new_values.join(','));
						// {id, text} for single-select, [{id, text},{id, text}] for multi-select
						callback($this.options.multiple ? results : results[0]);
					}).fail(function (response) {
						console.log(response.responseText);
					});
				}
			});
		}
	}
})(jQuery);
