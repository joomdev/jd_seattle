/**
 * @version     1.6.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


var JFormFieldFilePlus = function () {
	this.options = {
		wrapper: '',
		siteRoot: '/',
		target: {},
		token: ''
	};

	this.template = {
		li: '<li data-id="##ID##" class="hasTooltip" title="##TIP##" data-placement="right" data-html="true"></li>',
		link: '<a href="#" onclick="return false;" class="jff-fileplus-download hasTooltip">' +
		'<i class="fa fa-file-text"></i>##PREVIEW##&nbsp;##NAME##&nbsp;</a>',
		state: [
			'<a href="#" onclick="return false;" class="jff-fileplus-enable hasTooltip" title="Unhide" data-placement="left">' +
			'<i class="fa fa-eye-slash txt-color-red state-btn"></i></a>',
			'<a href="#" onclick="return false;" class="jff-fileplus-disable hasTooltip" title="Hide" data-placement="left">' +
			'<i class="fa fa-eye txt-color-red state-btn"></i></a>'
		],
		remove: '<a href="#" onclick="return false;" class="jff-fileplus-remove hasTooltip" title="Remove" data-placement="right">' +
		'<i class="fa fa-times-circle txt-color-red"></i></a>'
	};

	this.timer = 0;
};

(function ($) {
	JFormFieldFilePlus.prototype = {

		setup: function (options) {
			$.extend(this.options, options);

			var $id = '#' + this.options.wrapper;
			var that = this;

			$($id).on('click change', 'input[type="file"]', function () {
				return !($($id).find('.jff-fileplus-add').hasClass('disabled'));
			});

			$($id).data('plugin.fileplus', that);

			$($id).on('click', '.jff-fileplus-add', function (e) {
				e.preventDefault();
				$($id).find('input[type="file"]').click();
			});

			$($id).change(function (event) {
				var files = event.target.files;
				if (files && files.length > 0) {
					that.upload(files);
				}
			});

			$($id).on('click', '.jff-fileplus-remove', function () {
				var img_id = $(this).parent('li').data('id');
				that.removeAjax(img_id);
			});

			$($id).on('click', '.jff-fileplus-enable', function () {
				var img_id = $(this).parent('li').data('id');
				that.publishAjax(img_id, 1);
			});

			$($id).on('click', '.jff-fileplus-disable', function () {
				var img_id = $(this).parent('li').data('id');
				that.publishAjax(img_id, 0);
			});

			$($id).on('click', '.jff-fileplus-download', function () {
				var img_id = $(this).parent('li').data('id');
				that.download(img_id);
			});

			that.rebuild();

			// Todo: implement full disabled state for existing item rows as well
			if ($($id).find('input[type="file"]').is(':disabled')) {
				$($id).find('.jff-fileplus-add').addClass('disabled');
			}
		},

		upload: function (files) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			var data = new FormData();
			data.append('jform[file]', files[0]);

			var vars = $.extend({
				option: 'com_sellacious',
				task: 'media.uploadAjax',
				control: 'jform.file'
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
					$('<div class="jff-fileplus-progress">Uploading&hellip;</div>').insertAfter($($id).find('.jff-fileplus-add'));
					$($id).find('.jff-fileplus-add').hide();
				},
				success: function (response) {
					that.uploadResponse(response)
				},
				error: function (jqXHR) {
					that.messageWait({error: ['An unknown error was encountered when trying to upload the file.']}, 12000);
					console.log(jqXHR.responseText);
				}
			}).always(function (r) {
				$($id).find('input[type="file"]').val('');
				$($id).find('.jff-fileplus-progress').remove();
				$($id).find('.jff-fileplus-add').show();
			});
		},

		updateList: function (files, clear) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			if (clear) {
				$($id).find('ul').empty();
			}

			$.each(files || [], function (i, file) {
				var tooltip = file.path.split('.').pop().toLowerCase();
				var src = that.options.siteRoot + '/' + file.path;
				var preview = '';
				if (/jpg|jpeg|png|gif/.test(tooltip)) {
					preview = '<img class="jff-fileplus-preview" src="' + src + '">';
				}
				var li = that.template.li
					.replace(/##ID##/g, file.id)
					.replace(/##TIP##/g, tooltip.toUpperCase());
				var html = (that.template.state[file.state] + that.template.link + that.template.remove)
					.replace(/##ID##/g, file.id)
					.replace(/##NAME##/g, file['original_name'])
					.replace(/##SRC##/g, src)
					.replace(/##PREVIEW##/g, preview)
					.replace(/##TOKEN##/g, that.options.token);
				var $li = $(li).html(html);
				$($id).find('ul').append($li);
				$($id).find('.hasTooltip').tooltip('dispose');
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
				success: function (response, textStatus, jqXHR) {
					if (response.status) {
						$($id).find('.hasTooltip').tooltip('dispose');
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
				success: function (response, textStatus, jqXHR) {
					if (response.status == 1) {
						var i = $($id).find('li[data-id="' + pk + '"]').find('i.state-btn');
						if (value) {
							i.removeClass('fa-eye-slash').addClass('fa-eye');
							i.parent('a').removeClass('jff-fileplus-enable').addClass('jff-fileplus-disable').attr('title', 'Hide');
						} else {
							i.removeClass('fa-eye').addClass('fa-eye-slash');
							i.parent('a').removeClass('jff-fileplus-disable').addClass('jff-fileplus-enable').attr('title', 'Unhide');
						}
						$($id).find('.hasTooltip').tooltip('dispose');
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
					var item_count = $($id).find('.jff-fileplus-list').find('li').length;
					if (item_count >= limit) {
						$($id).find('.jff-fileplus-add-controls').hide();
						$($id).find('.jff-fileplus-add').addClass('disabled');
					} else {
						$($id).find('.jff-fileplus-add-controls').show();
						$($id).find('.jff-fileplus-add').removeClass('disabled');
					}
				}
			}

			if (t.record_id && t.table && t.context) {
				$($id).find('.jff-fileplus-active').removeClass('hidden');
				$($id).find('.jff-fileplus-inactive').addClass('hidden');
			} else {
				$($id).find('.jff-fileplus-active').addClass('hidden');
				$($id).find('.jff-fileplus-inactive').removeClass('hidden');
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
