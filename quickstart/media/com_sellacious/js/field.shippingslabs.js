/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var JFormFieldShippingSlabs = function () {
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
	JFormFieldShippingSlabs.prototype = {
		setup : function (options) {
			$.extend(this.options, options);
			var that = this;
			that.wrapper = $('#' + that.options.id + '_wrapper');
			that.wrapper.on('click', '.sfssrow-add', function () {
				that.addRow();
			});
			that.wrapper.on('click', '.sfssrow-remove', function () {
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
			that.wrapper.on('change', 'input[data-input-name]', function () {
				that.evaluate();
			});
			that.wrapper.find('input[type="file"]').change(function (event) {
				var files = event.target.files;
				if (files && files.length > 0) {
					that.parseFile(files[0], $(this));
				}
			});
			that.wrapper.on('click', '.btn-clear-slabs', function () {
				var $this = $(this);
				if ($this.data('confirm')) {
					$this.data('confirm', false);
					$this.find('i.fa').replaceWith('<i class="fa fa-times"></i>');
					$('#' + that.options.id).val('[]');
					var rows = that.wrapper.find('.sfssrow');
					console.log(rows.length);
					rows.remove();
				} else {
					$this.data('confirm', true);
					$this.find('i.fa').replaceWith('<i class="fa fa-question-circle"></i> ');
					setTimeout(function () {
						$this.data('confirm', false);
						$this.find('i.fa').replaceWith('<i class="fa fa-times"></i> ');
					}, 5000);
				}
			});
		},

		addRow : function () {
			var that = this;
			var index = ++this.options.rowIndex;
			var template = this.options.rowTemplate.html;
			var replacement = this.options.rowTemplate.replacement;
			var html = template.replace(new RegExp(replacement, "ig"), index + "");
			$(html).insertBefore(that.wrapper.find('.sfss-blankrow'));
			this.evaluate();
		},

		removeRow : function (index) {
			$('#' + this.options.id + '_sfssrow_' + index).remove();
			this.evaluate();
		},

		evaluate: function () {
			var that = this;
			var records = [];
			var rows = that.wrapper.find('.sfssrow');
			rows.each(function () {
				var record = {};
				$(this).find('input[data-input-name]').each(function () {
					var k = $(this).data('input-name');
					var v = $(this).val();
					record[k] = $(this).is('[type="checkbox"]') ? ($(this).prop('checked') ? v : 0) : v;
				});
				records.push(record);
			});
			$('#' + this.options.id).val(JSON.stringify(records));
		},

		parseFile: function (file, input) {
			var that = this;
			var data = new FormData();
			data.append('jform[file]', file);
			data.append(that.options.token, '1');

			var vars = $.extend({
				option: 'com_sellacious',
				task: 'shippingrule.loadCsvSlabsAjax',
				control: 'jform.file'
			}, that.options.target);

			var url = 'index.php?' + $.param(vars);
			var $controls = that.wrapper.find('.jff-slabs-file-add-controls');
			var $wait = that.wrapper.find('.jff-fileplus-progress');

			$.ajax({
				url: url,
				type: 'POST',
				data: data,
				cache: false,
				dataType: 'json',
				processData: false, // Don't process the files
				contentType: false, // Set content type to false as jQuery will tell the server its a query string request
				beforeSend: function () {
					$controls.hide();
					$wait.show();
				}
			}).done(function (response) {
				that.uploadResponse(response);
			}).fail(function (xhr) {
				console.log(xhr.responseText);
			}).always(function (xhr) {
				input.val('');
				$wait.hide();
				$controls.show();
			});
		},

		uploadResponse: function (response) {
			if (response.state === 1) {
				Joomla.removeMessages();
				$('#' + this.options.id).val(JSON.stringify(response.data));
				Joomla.submitform('');
			} else {
				Joomla.renderMessages({warning: [response.message]})
			}
		}
	}
})(jQuery);
