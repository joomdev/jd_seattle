/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

var JFormFieldLocation = function () {
	this.options = {
		id: 'jform',
		name: 'jform'
	};
};

(function ($) {
	JFormFieldLocation.prototype = {

		setup: function (options) {
			$.extend(this.options, options);

			var $this = this;
			var $id = '#' + $this.options.id;

			if ($this.options.rel) {
				$this.options.rel = $.map($this.options.rel, function (rel) {
					return '#' + ($this.options.fieldset ? $this.options.fieldset + '_' : '') + rel;
				});
				$($this.options.rel.join(',')).change(function () {
					$($id).select2('data', null).trigger('change');
				});
			}

			$this.select2();

			return this;
		},

		getParentId: function () {
			var parent = 1;
			var $this = this;

			if ($this.options.rel) {
				$.each($this.options.rel, function (i, rel) {
					var val = $(rel).val();
					if (val) {
						parent = val;
						return false;
					}
				});
			}

			return parent;
		},

		select2: function () {
			var $this = this;
			var $id = '#' + this.options.id;

			var label = $this.options.types.length > 1 ? '' : $this.options.types[0];

			var paths = Joomla.getOptions('system.paths', {});
			var base = paths.base || paths.root || '';

			$($id).select2({
				allowClear: true,
				placeholder: $this.options.hint || '- select ' + label + ' -',
				multiple: $this.options.multiple,
				minimumInputLength: 2,
				ajax: {
					url: base + '/index.php',
					dataType: 'json',
					data: function (term, page) {
						var parents = $this.getParentId();
						return {
							option: 'com_sellacious',
							task: 'location.autocomplete',
							query: term,
							parent_id: parents || 1,
							types: $this.options.types,
							address_type: $this.options.address_type,
							list_start: 10 * (page - 1),
							list_limit: 10
						};
					},
					results: function (response, page) {
						// Parse the results into the format expected by Select2.
						// If we are using custom formatting functions we do not need to alter remote JSON data
						var results = [];
						if (response.status === 1) {
							$.each(response.data, function (i, v) {
								var id = v.type === 'zip' ? v.title : v.id;
								results.push({id: id, text: (v['full_title'] || v.title)});
							});
						}
						return {results: results};
					}
				},
				formatAjaxError: function (xhr) {
					console.log(xhr.responseText);
				},
				initSelection: function (element, callback) {
					// The input tag has a value attribute preloaded that points to a preselected items id
					// This function resolves that id attribute to an object that select2 can render
					// using its formatResult renderer - that way the item title is shown preselected
					var values = $(element).val();
					if (values == '' || values == '0') return;

					var paths = Joomla.getOptions('system.paths', {});
					var base = paths.base || paths.root || '';

					var parents = $this.getParentId();
					$.ajax({
						url: base + '/index.php',
						type: 'post',
						dataType: 'json',
						data: {
							option: 'com_sellacious',
							task: 'location.getInfoAjax',
							id: values.split(','),
							parent_id: parents || 1,
							types: $this.options.types
						}
					}).done(function (response) {
						var results = [];
						var new_values = [];
						if (response.status === 1) {
							$.each(response.data, function (i, v) {
								var id = v.type === 'zip' ? v.title : v.id;
								new_values.push(id);
								results.push({id: id, text: (v['full_title'] || v.title)});
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
