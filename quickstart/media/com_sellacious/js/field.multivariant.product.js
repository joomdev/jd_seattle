/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

var JFormFieldMultiVariantProduct = function () {
	this.options = {
		id: 'jform',
		name: 'jform'
	};
};

(function ($) {
	JFormFieldMultiVariantProduct.prototype = {
		setup: function (selector, token) {
			var $that = this;
			$that.input = $(selector);
			$that.preview = $(selector + '_preview');
			$that.token = token;

			if ($that.input.length == 0) return false;

			$that.input.select2({
				tags: [],
				multiple: true,
				minimumInputLength: 3,
				ajax: {
					url: 'index.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							option: 'com_sellacious',
							task: 'variants.autocomplete',
							query: term,
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
								results.push({id: v.code, text: v['item_title'], sku: v['item_sku']});
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
							task: 'variants.getInfoAjax',
							code: values.split(',')
						}
					}).done(function (response) {
						var results = [];
						var new_values = [];
						if (response.status == 1) {
							$.each(response.data, function (i, v) {
								new_values.push(v.code);
								results.push({id: v.code, text: v['item_title'], sku: v['item_sku']});
							});
						}
						$(element).val(new_values.join(','));
						// {id, text} for single-select, [{id, text},{id, text}] for multi-select
						callback(results);

						// Now add UI Preview
						$.each(results, function (i, v) {
							$that.addRowPreview(v);
						});
					}).fail(function (response) {
						console.log(response.responseText);
					});
				}
			});

			$that.input.on('select2-selecting', function (e) {
				if (typeof e.choice == 'object' && typeof e.choice.text != 'undefined') {
					$that.addRowPreview(e.choice);
				}
			});

			// We do not allow removal from select2, use provided separate button for it in the preview section
			$that.input.on('select2-removing', function () {
				return false;
			});

			$that.preview.on('click', '.del-package-item', function (e) {
				var $btnDel = $(e.target).is('.del-package-item') ? $(e.target) : $(e.target).closest('.del-package-item');
				$that.deleteRowPreview($btnDel);
			});
		},

		addRowPreview: function (item) {
			var $that = this;
			var $row = $('<tr/>');
			$row.append(
				'<td>\
					<table class="package-item table table-stripped table-noborder w100p">\
					<thead><tr style="background: #deefc9">\
						<td colspan="2">' + item.text + ' <strong>' + (item.sku ? '(' + item.sku + ')' : '') + '</strong></td>\
					</tr></thead></table>\
				</td>\
				<td style="vertical-align: top !important; width: 50px; text-align: right;">\
					<button type="button" class="btn btn-xs btn-danger del-package-item" data-id="' + item.id + '">\
					<i class="fa fa-times"></i> Remove</button>\
				</td>'
			);
			$that.preview.first('tbody').append($row).find('.del-package-item').attr('data-item', item);
		},

		deleteRowPreview: function ($btnDel) {
			var $that = this;
			var id = $btnDel.data('id');
			var item = $btnDel.data('item');
			var data = $that.input.select2('data');

			// Delete/Confirm behaviour
			if ($btnDel.data('confirm')) {
				$btnDel.data('confirm', false);
				// Update select2 value
				$that.input.select2('data', $.grep(data, function (value) {
					return value.id != id;
				}));
				$btnDel.closest('tr').remove();
			} else {
				$btnDel.data('confirm', true);
				$btnDel.html('<i class="fa fa-question-circle"></i> Sure');
				setTimeout(function () {
					if ($btnDel.data('confirm')) {
						$btnDel.data('confirm', false);
						$btnDel.removeClass('btn-success').addClass('btn-danger')
							.html('<i class="fa fa-times"></i> Remove');
					}
				}, 5000);
			}
		}
	};
})(jQuery);
