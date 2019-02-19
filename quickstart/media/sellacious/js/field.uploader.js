/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var UploaderFormField = function (element, options) {
	this.element = null;
	this.params = {
		extensions: [],
		maxSize: 0
	};
	this.index = 0;
	this.template = '';
	this.init(element, options);
};

jQuery(function ($) {

	UploaderFormField.prototype = {

		init: function (element, options) {
			var uploader = this;
			uploader.params = $.extend({}, uploader.params, options);
			uploader.element = $(element);
			uploader.index = uploader.element.find('.jff-uploader-row').length;
			uploader.rebuild();

			uploader.element
				.on('change', 'input[type="file"]', function (event) {
					if (event.target.files) {
						if (uploader.fileValidate(event.target)) {
							uploader.fileSelect(event.target);
						}
						uploader.rebuild();
					}
				})
				.on('click', '.btn-remove', function (event) {
					uploader.fileRemove($(event.target).closest('.jff-uploader-row'));
					uploader.rebuild();
				});
			var $template = $('#' + uploader.params.id + '_script');
			uploader.template = $template.html();
			$template.remove();
		},

		fileValidate: function (input) {
			var uploader = this;
			if (!input.files || input.files.length === 0) {
				return false;
			}
			var fileObj = input.files[0];
			if (uploader.params.extensions && uploader.params.extensions.length > 0) {
				var vRegex = new RegExp('\\.(' + uploader.params.extensions.join('|') + ')$', 'i');
				if (!vRegex.test(fileObj.name)) {
					alert(Joomla.JText._('LIB_SELLACIOUS_ERROR_FILE_UPLOAD_TYPE_NOT_ALLOWED', 'Upload not allow for this file type.'));
					return false;
				}
			}
			if (!isNaN(uploader.params.maxSize)) {
				if (uploader.params.maxSize > 0 && uploader.params.maxSize < fileObj.size) {
					alert(Joomla.JText._('LIB_SELLACIOUS_ERROR_FILE_UPLOAD_SIZE_EXCEEDED', 'File size exceeded the allowed limit.'));
					return false;
				}
			}
			return true;
		},

		fileSelect: function (input) {
			var uploader = this;
			if (input.files) {
				uploader.index++;
				var fileObj = input.files[0];
				var inputName = uploader.params.name + '[' + uploader.index + '][file]';
				var inputId = uploader.params.id + '_' + uploader.index + '_file';
				var cInput = $(input).attr('name', inputName).attr('id', inputId);

				var $newRow = $(uploader.template.replace(/XXX/g, uploader.index));
				$newRow.find('input[type="file"]').replaceWith(cInput);
				$newRow.find('.jff-uploader-filename').val(fileObj.name);
				uploader.element.find('.jff-uploader-list').append($newRow);

				var emptyInput = $('<input/>', {type: 'file', id: uploader.params.id + '_picker'});
				uploader.element.find('.jff-uploader-input-wrapper').empty().append(emptyInput);

				if (/\.(jpg|jpeg|png|gif)$/i.test(fileObj.name)) {
					uploader.renderPreview(fileObj, function (previewUri) {
						var imgSM = $('<img' + '/>', {src: previewUri, 'class': 'jff-uploader-preview-sm'});
						var imgLG = $('<img' + '/>', {src: previewUri, 'class': 'jff-uploader-preview-lg'});
						$newRow.find('.jff-uploader-preview-box').append(imgSM).append(imgLG);
					});
				}
			}
		},

		fileRemove: function (row) {
			if (row.data('id')) {
				row.addClass('hidden').find('.jff-uploader-remove').prop('checked', true);
			} else {
				row.remove();
			}
		},

		rebuild: function () {
			var uploader = this;
			var numRows = uploader.element.find('.jff-uploader-row').not('.hidden');
			var controls = uploader.element.find('.jff-uploader-controls');
			controls.toggleClass('hidden', uploader.params.limit > 0 && numRows.length >= uploader.params.limit);
		},

		renderPreview: function (file, callback) {
			var reader = new FileReader();
			reader.onload = function (e) {
				callback(e.target.result);
			};
			reader.readAsDataURL(file);
		}

	};

	$.fn.extend({
		uploader: function () {
			$(this).each(function () {
				var uploader = $(this).data('uploader-instance');
				if (uploader) {
					uploader.rebuild();
				} else {
					var options = $(this).data('uploader');
					uploader = new UploaderFormField(this, options);
					$(this).data('uploader-instance', uploader);
				}
			});
		}
	});

	$('[data-uploader]').uploader();
});
