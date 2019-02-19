/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


var JFormFieldCropimage = function () {
	this.options = {
		wrapper: 'jform',
		cropobject: false,
		insertAt: false,
		siteRoot: '/'
	};

	this.crop = false;
};

(function ($) {
	JFormFieldCropimage.prototype = {

		setup: function (options) {
			$.extend(this.options, options);

			var $id = '#' + this.options.wrapper;
			var that = this;

			$($id).on('click', '.jffci-add', function () {
				$('img').imgAreaSelect({
					remove: true
				});
				that.crop = false;

				$($id).find('.superbox-close').click();
				$($id).find('input[type="file"]').click();
			});

			$($id).find('input[type="file"]').change(function (event) {
				var files = event.target.files;
				if (files.length > 0) {
					that.upload(files);
				}
			});

			$($id).find('.superbox').on('shown', '.superbox-current-img', function () {
				that.crop = false;

				var ias = $(this).imgAreaSelect({
					instance: true,
					handles: true,
					onSelectEnd: function (img, selection) {
						that.crop = {
							img: $(img).data('id'),
							selection: selection
						};
					}
				});
			});

			$($id).find('.superbox').on('hidden', '.superbox-current-img', function () {
				$(this).imgAreaSelect({
					remove: true
				});
				that.crop = false;
			});

			$($id).find('.superbox').on('click', '.jffci-savecrop', function () {
				that.applyCrop();
			});

			$($id).find('.superbox').on('click', '.jffci-delete', function () {
				var img_id = $($id).find('.superbox-current-img').data('id');
				that.removeAjax(img_id);
			});

			that.doSuperBox();
		},

		upload: function (files) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			var data = new FormData();
			data.append('jform[image]', files[0]);

			$.ajax({
				url: 'index.php?option=com_sellacious&task=media.uploadAjax&control=jform.image&temp=0&type=image&' + that.options.target,
				type: 'POST',
				data: data,
				cache: false,
				dataType: 'json',
				processData: false, // Don't process the files
				contentType: false, // Set content type to false as jQuery will tell the server its a query string request
				success: function (response, textStatus, jqXHR) {
					that.uploadResponse(response)
				},
				error: function (jqXHR, textStatus, errorThrown) {
					alert(jqXHR.responseText);
				}
			});
		},

		uploadResponse: function (response) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			if (response.status) {
				var item = $('<div class="superbox-list"><img src="" class="superbox-img" /></div>');
				var path = that.options.siteRoot + '/' + response.data.image.path;
				item.insertBefore($($id).find(that.options.insertAt));
				item.find('img.superbox-img').attr('src', path).data('img', path).data('id', response.data.image.id);
				that.doSuperBox();
			} else {
				alert(response.message);
			}
		},

		applyCrop: function () {
			var $id = '#' + this.options.wrapper;
			var that = this;

			if (!that.crop) return;

			var data = that.crop;
			var img = $($id).find('.superbox-current-img');

			data = {
				img: data.img,
				selection: {
					x: data.selection.x1,
					y: data.selection.y1,
					w: data.selection.width,
					h: data.selection.height,
					sw: img.width(),
					sh: img.height()
				}
			};

			$.ajax({
				url: 'index.php?option=com_sellacious&task=media.cropAjax',
				type: 'POST',
				data: data,
				cache: false,
				dataType: 'json',
				success: function (response, textStatus, jqXHR) {
					that.cropResponse(response, data.img);
				},
				error: function (jqXHR, textStatus, errorThrown) {
					alert(jqXHR.responseText);
				}
			});
		},

		cropResponse: function (response, imgid) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			if (response.status) {
				$($id).find('.superbox-close').click();

				var item = $($id).find('.superbox-img').filter(function () {
					return $(this).data('id') == imgid;
				});

				var path = that.options.siteRoot + '/' + response.data + '?cache=' + Math.random();

				item.removeAttr('src');
				item.attr('src', path);
				item.data('img', path);
			} else {
				alert(response.message);
			}
		},

		removeAjax: function (img) {
			var $id = '#' + this.options.wrapper;
			var that = this;

			$.ajax({
				url: 'index.php?option=com_sellacious&task=media.removeAjax',
				type: 'POST',
				data: {
					img: img
				},
				cache: false,
				dataType: 'json',
				success: function (response, textStatus, jqXHR) {
					$($id).find('.superbox-close').click();
					if (response.status) {
						var item = $($id).find('.superbox-img').filter(function () {
							return $(this).data('id') == img;
						});
						item.parent().remove();
					} else {
						alert(response.message);
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					alert(jqXHR.responseText);
				}
			});
		},

		doSuperBox: function () {
			var $id = '#' + this.options.wrapper;
			var that = this;

			$($id).find('.superbox').SuperBox({
				buttons: '<div class="btn-group">' +
				'<a href="javascript:void(0);" class="btn btn-primary btn-sm jffci-savecrop">CROP &amp; SAVE</a>' +
				'<a href="javascript:void(0);" class="btn btn-danger btn-sm jffci-delete">DELETE IMAGE</a>' +
				'</div>'
			});
		},
	}
})(jQuery);
