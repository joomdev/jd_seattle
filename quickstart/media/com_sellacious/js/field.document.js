/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var JFormFieldDocument = function () {
	this.options = {
		wrapper: '',
		siteRoot: '/',
		target: {},
		token: ''
	};

	this.template = {
		li: '<li data-id="##ID##" class="hasTooltip" title="##TIP##" data-placement="right" data-html="true"></li>',
		link: '<a href="#" onclick="return false;" class="jff-document-download hasTooltip">' +
		'<i class="fa fa-file-text"></i>##PREVIEW##&nbsp;<em>##DOC_TYPE##</em>##DOC_REF##&nbsp;</a>',
		state: [
			'<a href="#" onclick="return false;" class="jff-document-enable hasTooltip" title="Unhide" data-placement="left">' +
			'<i class="fa fa-eye-slash txt-color-red state-btn"></i></a>',
			'<a href="#" onclick="return false;" class="jff-document-disable hasTooltip" title="Hide" data-placement="left">' +
			'<i class="fa fa-eye txt-color-red state-btn"></i></a>'
		],
		remove: '<a href="#" onclick="return false;" class="jff-document-remove hasTooltip" title="Remove" data-placement="right">' +
		'<i class="fa fa-times-circle txt-color-red"></i></a>'
	};

	this.timer = 0;
};

(function ($) {
	JFormFieldDocument.prototype = {

		setup: function (options) {
			$.extend(this.options, options);

			var $id = '#' + this.options.wrapper;
			var that = this;

			$($id).find('input[type="file"]').click(function () {
				return !($($id).find('.jff-document-add').hasClass('disabled'));
			});

			$($id).data('plugin.fileplus', that);

			$($id).on('click', '.jff-document-add', function (e) {
				e.preventDefault();
				$($id).find('input[type="file"]').click();
			});

			$($id).change(function (event) {
				var files = event.target.files;
				if (files && files.length > 0) {
					that.upload(files);
				}
			});

			$($id).on('change keyup', '.jff-document-add-ref,select.jff-document-add-type', function (e) {
				var $add = $($id).find('.jff-document-add');
				var e1 = $($id).find('.jff-document-add-ref');
				var e2 = $($id).find('select.jff-document-add-type');
				var ok = ($.trim(e1.val()) != '' && e1.val() != '0' && (e2.length == 0 || $.trim(e2.val()) != ''));
				ok ? $add.removeClass('disabled') : $add.addClass('disabled');
			});

			$($id).on('click', '.jff-document-remove', function () {
				var img_id = $(this).parent('li').data('id');
				that.removeAjax(img_id);
			});

			$($id).on('click', '.jff-document-enable', function () {
				var img_id = $(this).parent('li').data('id');
				that.publishAjax(img_id, 1);
			});

			$($id).on('click', '.jff-document-disable', function () {
				var img_id = $(this).parent('li').data('id');
				that.publishAjax(img_id, 0);
			});

			$($id).on('click', '.jff-document-download', function () {
				var img_id = $(this).parent('li').data('id');
				that.download(img_id);
			});

			that.rebuild();
		},

		upload: function (files) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			var data = new FormData();
			data.append('jform[file]', files[0]);

			var d_ref = $($id).find('.jff-document-add-ref').val();
			var d_type = $($id).find('select.jff-document-add-type').val();

			var vars = $.extend({
				option: 'com_sellacious',
				task: 'media.uploadAjax',
				control: 'jform.file',
				jform: {
					data: {
						file: {
							doc_type: d_type,
							doc_reference: d_ref
						}
					}
				}
			}, that.options.target);

			var url = 'index.php?' + $.param(vars) + '&' + that.options.token;

			$.ajax({
				url: url,
				type: 'POST',
				data: data,
				cache: false,
				dataType: 'json',
				processData: false, // Don't process the files
				contentType: false, // Set content type to false as jQuery will tell the server its a query string request
				beforeSend: function () {
					that.messageWait({info: ['Uploading file, please wait&hellip;']});
					$('<div class="jff-document-progress">Uploading&hellip;</div>').insertAfter($($id).find('.jff-document-add'));
					$($id).find('.jff-document-add').hide();
				},
				success: function (response) {
					that.uploadResponse(response)
				},
				error: function (jqXHR, textStatus, errorThrown) {
					that.messageWait({error: ['An unknown error was encountered when trying to upload the file.']}, 12000);
					console.log(jqXHR.responseText);
				}
			}).always(function (r) {
				$($id).find('input[type="file"]').val('');
				$($id).find('.jff-document-progress').remove();
				$($id).find('.jff-document-add').show();
			});
		},

		updateList: function (files, clear) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			if (clear) {
				// $($id).find('ul').empty();
			}

			$.each(files || [], function (i, file) {
				var tooltip = file.path.split('.').pop();
				var src = that.options.siteRoot + '/' + file.path;
				var preview;
				if (that.options.target.type == 'image') {
					preview = '<img class="jff-document-preview" src="' + src + '">';
				}
				var li = that.template.li
					.replace(/##ID##/g, file.id)
					.replace(/##TIP##/g, tooltip);

				var html = (that.template.state[file.state] + that.template.link + that.template.remove)
					.replace(/##ID##/g, file.id)
					.replace(/##NAME##/g, file['original_name'])
					.replace(/##PREVIEW##/g, preview)
					.replace(/##DOC_TYPE##/g, Joomla.JText._('COM_SELLACIOUS_MEDIA_DOCUMENT_USER_DOC_' + (file['doc_type'] || 'OTHER'), file['doc_type']) + ': ')
					.replace(/##DOC_REF##/g, file['doc_reference'])
					.replace(/##TOKEN##/g, that.options.token);
				var $li = $(li).html(html);
				$($id).find('ul').append($li);
				$($id).find('.hasTooltip').tooltip('destroy');
				$($id).find('.hasTooltip').tooltip({
					"html": true,
					"container": "body"
				});
			});
		},

		uploadResponse: function (response) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			if (response.status == 1) {
				// Clear inputs, file input is already cleared to avoid user annoyances
				$($id).find('.jff-document-add-ref').val('').trigger('change');
				$($id).find('select.jff-document-add-type').val('').trigger('change');

				var file = response.data.file;
				this.updateList([file]);
				that.rebuild();
				that.messageWait({success: ['File uploaded successfully.']}, 8000);
			} else {
				that.messageWait({warning: [response.message]});
			}
		},

		download: function (img_id) {
			var that = this;

			that.messageWait({info: ['Initiating your download&hellip;']});

			$.ajax({
				url: 'index.php?option=com_sellacious&task=media.downloadAjax&' + that.options.token + '&id=' + img_id,
				type: 'POST',
				dataType: 'json',
				cache: false,
				success: function (response) {
					var messages;
					if (response.status == 1) {
						messages = {success: ['File found. Initiating download&hellip;']};
						window.location.href = 'index.php?option=com_sellacious&task=media.download&' + that.options.token + '&id=' + img_id;
					} else {
						messages = {warning: [response.message]};
					}
					that.messageWait(messages, 12000);
				},
				error: function (jqXHR, textStatus, errorThrown) {
					that.messageWait({error: ['An unknown error was encountered when trying to download the file.']}, 12000);
					console.log(jqXHR.responseText);
				}
			});
		},

		removeAjax: function (pk) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			$.ajax({
				url: 'index.php?option=com_sellacious&task=media.removeAjax',
				type: 'POST',
				data: {
					img: pk
				},
				cache: false,
				dataType: 'json',
				success: function (response) {
					if (response.status) {
						$($id).find('.hasTooltip').tooltip('destroy');
						var item = $($id).find('li[data-id="' + pk + '"]');
						item.remove();
						$($id).find('.hasTooltip').tooltip({
							"html": true,
							"container": "body"
						});
						that.rebuild();
						that.messageWait({success: ['Selected file removed successfully.']}, 8000);
					} else {
						that.messageWait({error: [response.message]}, 8000);
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					console.log(jqXHR.responseText);
				}
			});
		},

		publishAjax: function (pk, value) {
			var task = value ? 'publishAjax' : 'unpublishAjax';
			var $id = '#' + this.options.wrapper;
			var that = this;

			$.ajax({
				url: 'index.php?option=com_sellacious&task=media.' + task + '&' + that.options.token,
				type: 'POST',
				data: {
					cid: pk
				},
				cache: false,
				dataType: 'json',
				success: function (response) {
					if (response.status == 1) {
						var i = $($id).find('li[data-id="' + pk + '"]').find('i.state-btn');
						if (value) {
							i.removeClass('fa-eye-slash').addClass('fa-eye');
							i.parent('a').removeClass('jff-document-enable').addClass('jff-document-disable').attr('title', 'Hide');
						} else {
							i.removeClass('fa-eye').addClass('fa-eye-slash');
							i.parent('a').removeClass('jff-document-disable').addClass('jff-document-enable').attr('title', 'Unhide');
						}
						$($id).find('.hasTooltip').tooltip('destroy');
						$($id).find('.hasTooltip').tooltip({
							"html": true,
							"container": "body"
						});
					} else {
						that.messageWait({success: [response.message]}, 8000);
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					console.log(jqXHR.responseText);
				}
			});
		},

		rebuild: function () {
			// Rebuild the fileplus appearance based on whether the record has a 'id' reference, also take care of count limit
			var that = this;
			var $id = '#' + that.options.wrapper;
			var t = that.options.target;

			if (typeof t.limit != 'undefined') {
				var limit = t.limit;
				if (limit) {
					var item_count = $($id).find('.jff-document-list').find('li').length;
					var controls = $($id).find('.jff-document-add-controls');
					(item_count >= limit) ? controls.hide() : controls.show();
				}
			}

			if (t.record_id && t.table && t.context) {
				$($id).find('.jff-document-active').removeClass('hidden');
				$($id).find('.jff-document-inactive').addClass('hidden');
			} else {
				$($id).find('.jff-document-active').addClass('hidden');
				$($id).find('.jff-document-inactive').removeClass('hidden');
			}
		},

		messageWait: function (messages, wait) {
			var that = this;
			Joomla.renderMessages(messages);

			// Clear any pending timeOut otherwise it may conflict
			if (that.timer) clearTimeout(that.timer);

			that.timer = setTimeout(function () {
				Joomla.removeMessages();
			}, wait || 8000);
		}
	}
})(jQuery);
