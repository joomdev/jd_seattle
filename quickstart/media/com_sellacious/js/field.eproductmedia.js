/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var JFormFieldEProductMedia = function () {
	this.options = {
		wrapper: '',
		siteRoot: '/',
		target: {},
		token: '',
		types: {},
		rowTemplate: '',
		product_id: 0,
		variant_id: null,
		seller_uid: 0
	};

	this.template = {
		li: '<li data-id="##ID##" class="hasTooltip" title="##TIP##" data-placement="right" data-html="true"></li>',
		link: '<a href="#" onclick="return false;" class="jff-eproductmedia-download hasTooltip">' +
		'<i class="fa fa-file-text"></i>##PREVIEW##&nbsp;##NAME##&nbsp;</a>',
		state: [
			'<a href="#" onclick="return false;" class="jff-eproductmedia-enable hasTooltip" title="Unhide" data-placement="left">' +
			'<i class="fa fa-eye-slash txt-color-red state-btn"></i></a>',
			'<a href="#" onclick="return false;" class="jff-eproductmedia-disable hasTooltip" title="Hide" data-placement="left">' +
			'<i class="fa fa-eye txt-color-red state-btn"></i></a>'
		],
		remove: '<a href="#" onclick="return false;" class="jff-eproductmedia-remove hasTooltip" title="Remove" data-placement="right">' +
		'<i class="fa fa-times-circle txt-color-red"></i></a>',
		hotlink: '<a href="#" onclick="return false;" class="jff-eproductmedia-hotlink hasTooltip" title="Copy HotLink" data-placement="right">' +
		'<i class="fa fa-link txt-color-blueDark"></i></a>'
	};

	this.timer = 0;
};

(function ($) {
	JFormFieldEProductMedia.prototype = {

		setup: function (options) {
			$.extend(this.options, options);

			var that = this;
			var $id = '#' + that.options.wrapper;

			// Get the html template from dom for injecting new rows and remove it from dom to prevent inclusion in submitted form data.
			var template = $($id).find('.jff-eproductmedia-rowtemplate');
			that.options.rowTemplate = template.html().replace(/[\r\n\t]+/g, '');
			template.remove();

			$($id).data('plugin.eproductmedia', this);

			$($id).on('click change', 'input[type="file"]', function (e) {
				// The cancel event does not exist so the 'target' data is persisted when user cancels file dialog.
				var clicked = $(this).data('target');
				if (!clicked || $(clicked).is('.disabled')) return false;

				var $cell = $(clicked).closest('.jff-eproductmedia-media-file');

				if (!$cell.data('id') || !$cell.data('context')) return false;

				if (e.type == 'change') {
					$(this).data('target', null);
					if (e.target.files && e.target.files.length > 0) {
						that.upload(e.target.files, $cell);
					}
				}
			});

			$($id).on('click', '.jff-eproductmedia-add', function (e) {
				e.preventDefault();
				// Assign this as a target to allow reuse of the file input.
				$($id).find('input[type="file"]').data('target', this).click();
			});

			$($id).on('click', '.jff-eproductmedia-remove', function () {
				var img_id = $(this).parent('li').data('id');
				that.removeAjax(img_id);
			});

			$($id).on('click', '.jff-eproductmedia-enable', function () {
				var img_id = $(this).parent('li').data('id');
				that.publishAjax(img_id, 1);
			});

			$($id).on('click', '.jff-eproductmedia-disable', function () {
				var img_id = $(this).parent('li').data('id');
				that.publishAjax(img_id, 0);
			});

			$($id).on('click', '.jff-eproductmedia-hotlink', function (e) {
				e.preventDefault();
				var paths = Joomla.getOptions('system.paths', {});
				var base = paths.root || '';
				var mId = $(this).closest('.jff-eproductmedia-media-file').data('id');
				var link = '/index.php?option=com_sellacious&task=product.downloadFile&id=' + mId;
				link = window.location.protocol + '//' + window.location.hostname + base + link;
				prompt("Copy the below URL: (Press Ctrl+C or ⌘+C)\n\nPlease note that you need to enable 'Hotlink' for this URL to work.", link);
			});

			$($id).on('click', '.jff-eproductmedia-download', function () {
				var img_id = $(this).parent('li').data('id');
				that.download(img_id);
			});

			$($id).on('click', '.jff-eproductmedia-addrow', function () {
				that.addRow();
			});

			$($id).on('click', '.jff-eproductmedia-removerow', function () {
				var $this = $(this);

				if ($this.data('confirm')) {
					$this.data('confirm', false);
					$this.html('<i class="fa fa-lg fa-times"></i> ');
					that.removeRow($this.closest('.jff-eproductmedia-media'));
				} else {
					$this.data('confirm', true);
					$this.html('<i class="fa fa-lg fa-question-circle"></i> ');
					setTimeout(function () {
						$this.data('confirm', false);
						$this.html('<i class="fa fa-lg fa-times"></i> ');
					}, 5000);
				}
			});

			that.rebuild();
		},

		upload: function (files, $cell) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			var data = new FormData();
			data.append('jform[file]', files[0]);

			var vars = $.extend({
				option: 'com_sellacious',
				task: 'product.uploadEProductAjax',
				control: 'jform.file'
			}, that.options.target, {
				record_id: $cell.data('id'),
				context: $cell.data('context'),
				type: that.options.types[$cell.data('context')]
			});

			$.ajax({
				url: 'index.php?' + $.param(vars) + '&' + that.options.token,
				type: 'POST',
				data: data,
				cache: false,
				dataType: 'json',
				processData: false, // Don't process the files
				contentType: false, // Set content type to false as jQuery will tell the server its a query string request
				beforeSend: function () {
					that.messageWait({info: ['Uploading file, please wait&hellip;']});
					$('<div class="jff-eproductmedia-progress">Uploading&hellip;</div>').insertAfter($cell.find('.jff-eproductmedia-add'));
					$($cell).find('.jff-eproductmedia-add').addClass('hidden');
				},
				success: function (response) {
					if (response.status == 1) {
						var file = response.data.file;
						that.updateList([file], $cell);
						that.rebuild();
						that.messageWait({success: ['File uploaded successfully.']}, 8000);
					} else {
						that.messageWait({warning: [response.message]});
					}
				},
				error: function (jqXHR) {
					that.messageWait({error: ['An unknown error was encountered when trying to upload the file.']}, 12000);
					console.log(jqXHR.responseText);
				}
			}).always(function () {
				$($id).find('input[type="file"]').val('').data('target', null);
				$($id).find('.jff-eproductmedia-progress').remove();
				$($id).find('.jff-eproductmedia-add').removeClass('hidden');
			});
		},

		updateList: function (files, $cell, clear) {
			var that = this;

			if (clear) $($cell).find('ul').empty();

			$.each(files || [], function (i, file) {
				var tooltip = file.path.split('.').pop().toLowerCase();
				var preview = '', src = '';
				if (/jpg|jpeg|png|gif/.test(tooltip)) {
					src = that.options.siteRoot + '/' + file.path;
					preview = '<img class="jff-eproductmedia-preview" src="' + src + '">';
				}
				var li = that.template.li
					.replace(/##ID##/g, file.id)
					.replace(/##TIP##/g, tooltip.toUpperCase());

				var fileName = file['original_name'] || file['name'];
				fileName = fileName.length <= 30 ? fileName : fileName.substring(0, 14) + '&hellip;' +
					fileName.substring(fileName.length - 13, fileName.length);

				var html = ((that.template.state[file.state] || '') + that.template.link + that.template.remove + that.template.hotlink)
					.replace(/##ID##/g, file.id)
					.replace(/##NAME##/g, fileName)
					.replace(/##SRC##/g, src)
					.replace(/##PREVIEW##/g, preview)
					.replace(/##TOKEN##/g, that.options.token);

				var $li = $(li).html(html);
				$($cell).find('ul').append($li);

				$($cell).find('.hasTooltip').tooltip('destroy');
				$($cell).find('.hasTooltip').tooltip({
					html: true,
					container: 'body'
				});
			});
		},

		download: function (img_id) {
			var that = this;

			that.messageWait({info: ['Initiating your download&hellip;']});

			$.ajax({
				url: 'index.php?option=com_sellacious&task=media.downloadAjax&id=' + img_id + '&' + that.options.token,
				type: 'POST',
				dataType: 'json',
				cache: false,
				success: function (response) {
					var messages;
					if (response.status == 1) {
						messages = {success: ['File found. Initiating download&hellip;']};
						window.location.href = 'index.php?option=com_sellacious&task=media.download&id=' + img_id + '&' + that.options.token;
					} else {
						messages = {warning: [response.message]};
					}
					that.messageWait(messages, 12000);
				},
				error: function (jqXHR) {
					that.messageWait({error: ['An unknown error was encountered when trying to download the file.']}, 12000);
					console.log(jqXHR.responseText);
				}
			});
		},

		removeAjax: function (pk) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			$.ajax({
				url: 'index.php?option=com_sellacious&task=media.removeAjax&' + that.options.token,
				type: 'POST',
				data: {
					img: pk
				},
				cache: false,
				dataType: 'json',
				success: function (response) {
					if (response.status) {
						// This id is globally unique so its ok to search in root node
						var item = $($id).find('li[data-id="' + pk + '"]');

						$($id).find('.hasTooltip').tooltip('destroy');
						item.remove();
						$($id).find('.hasTooltip').tooltip({
							html: true,
							container: 'body'
						});
						that.rebuild();
						that.messageWait({success: ['Selected file removed successfully.']}, 8000);
					} else {
						that.messageWait({error: [response.message]}, 8000);
					}
				},
				error: function (jqXHR) {
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
						// This id is globally unique so its ok to search in root node
						var i = $($id).find('li[data-id="' + pk + '"]').find('i.state-btn');

						i.toggleClass('fa-eye', value).toggleClass('fa-eye-slash', !value)
							.parent('a').toggleClass('jff-eproductmedia-disable', value).toggleClass('jff-eproductmedia-enable', !value)
							.attr('title', value ? 'Hide' : 'Unhide');

						$($id).find('.hasTooltip')
							.tooltip('destroy')
							.tooltip({
								html: true,
								container: 'body'
							});
					} else
						that.messageWait({error: [response.message]}, 8000);
				},
				error: function (jqXHR) {
					console.log(jqXHR.responseText);
				}
			});
		},

		rebuild: function () {

			// Rebuild the eproductmedia appearance based on whether the record has a 'id' reference, count limit is always 1 for each record
			var that = this;
			var $id = '#' + that.options.wrapper;
			var t = that.options;

			var active = t.product_id > 0 && t.seller_uid > 0 && t.variant_id !== null;

			$($id).find('.jff-eproductmedia-active').toggleClass('hidden', !active);
			$($id).find('.jff-eproductmedia-inactive').toggleClass('hidden', active);

			$($id).find('table.table>tbody').find('.jff-eproductmedia-media-file').each(function () {
				var record = $(this).data('id');
				var length = $(this).find('.jff-eproductmedia-list').find('li').length;
				var active = length == 0 && record > 0;

				$(this).find('.jff-eproductmedia-add').toggleClass('disabled', !active).toggle(active);
			});
		},

		addRow: function () {
			// Currently we are using draft so as to allow quick reference id for the media
			var that = this;

			$.ajax({
				url: 'index.php?option=com_sellacious&task=product.addEProductAjax&' + that.options.token,
				type: 'POST',
				data: {
					product_id: that.options.product_id,
					variant_id: that.options.variant_id,
					seller_uid: that.options.seller_uid
				},
				cache: false,
				dataType: 'json',
				success: function (response) {
					if (response.status == 1) {
						that.updateRecords([response.data], false);
					} else
						that.messageWait({error: [response.message]}, 8000);
				},
				error: function (jqXHR) {
					console.log(jqXHR.responseText);
				}
			});
		},

		removeRow: function ($row) {
			// Currently we are using draft so as to allow quick reference id for the media
			var $id = '#' + this.options.wrapper;
			var that = this;

			$.ajax({
				url: 'index.php?option=com_sellacious&task=product.removeEProductAjax&' + that.options.token,
				type: 'POST',
				data: {
					product_id: that.options.product_id,
					id: $row.find('.jff-eproductmedia-media-file').data('id')
				},
				cache: false,
				dataType: 'json',
				success: function (response) {
					if (response.status == 1) {
						$($id).find('.hasTooltip').tooltip('destroy');
						$row.remove();
						$($id).find('.hasTooltip').tooltip({
							html: true,
							container: 'body'
						});
					} else
						that.messageWait({error: [response.message]}, 8000);
				},
				error: function (jqXHR) {
					console.log(jqXHR.responseText);
				}
			});
		},

		updateRecords: function (records, clear) {
			var that = this;
			var $id = '#' + that.options.wrapper;

			if (clear) $($id).find('table.table>tbody').empty();

			$.each(records || [], function (i, record) {
				var html = $.trim(that.options.rowTemplate);
				var keys = ['id', 'product_id', 'variant_id', 'seller_uid', 'tags', 'version', 'released', 'is_latest', 'hotlink', 'state'];

				$.each(keys, function (ki, key) {
					html = html.replace(new RegExp('#' + key.toUpperCase() + '#', 'g'), record[key] || '');
				});

				var $html = $(html);

				$html.find('.jff-eproductmedia-media-file').each(function () {
					var context = $(this).data('context');
					if (context && record[context]) {
						// context => 'media' or 'sample'
						that.updateList([record[context]], this, false);
					}
				});

				// Fix the broken select2 on the tags input
				$html.find('.bootstrap-tagsinput').remove();
				$html.find('input.select2-tags').css('display', '').select2({tags: [], tokenSeparators: [',']});

				$($id).find('table.table').find('tbody').append($html);
				$($id).find('.hasTooltip')
					.tooltip('destroy')
					.tooltip({
						html: true,
						container: 'body'
					});

				that.rebuild();
			});
		},

		messageWait: function (messages, wait) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			// Use custom container with fallback to default system message container
			var $container = $($id).find('.messages-container');
			if ($container.length == 0) $container = $('#system-message-container');
			$container.empty();

			var type;

			for (type in messages) {
				if (!messages.hasOwnProperty(type)) continue;

				var title = Joomla.JText._(type);

				var $box = $('<div/>', {class: 'alert alert-' + type + ' fade in'});

				$box.append('<button class="close" data-dismiss="alert">×</button>');

				if (typeof title != 'undefined') $box.append($('<h4>', {class: 'alert-heading'}).html(title));

				for (var i = messages[type].length - 1; i >= 0; i--) $box.append($('<p>').html(messages[type][i]));

				$container.append($box);
			}

			// Clear any pending timeOut otherwise it may conflict
			if (typeof wait == 'undefined' || wait > 0) {
				if (that.timer) clearTimeout(that.timer);

				that.timer = setTimeout(function () {
					$container.empty();
				}, wait || 8000);
			}
		}
	}
})(jQuery);
