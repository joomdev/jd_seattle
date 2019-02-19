/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


var JFormFieldProductSeriesList = function () {
	this.options = {
		id : '',
		catfield : '',
		mfrfield : ''
	};
};

(function ($) {
	JFormFieldProductSeriesList.prototype = {

		setup : function (options) {
			$.extend(this.options, options);

			var that = this;
			var $id = '#' + that.options.id;
			var $cf = '#' + that.options.catfield;
			var $mf = '#' + that.options.mfrfield;

			$($cf + ',' + $mf).on('change', function () {
				that.clearList();
				that.loadData();
			});
		},

		clearList : function () {
			var that = this;

			var $id = '#' + that.options.id;

			$($id + ' > option').each(function () {
				$(this).remove();
			});

			var option = $("<option />").val('').text('');
			$($id).append(option);

			$($id).val('').trigger('change');
		},

		loadData : function () {
			var that = this;

			var $cf = '#' + that.options.catfield;
			var $mf = '#' + that.options.mfrfield;

			var data = {
				option : 'com_sellacious',
				task : 'product.getseriesajax',
				manufacturer_id : $($mf).val(),
				category_id : $($cf).val()
			};

			$.getJSON('index.php', data).done(function (data) {
				that.populateList(data);
			}).fail(function () {
				alert('Failed to load series list.');
			});
		},

		populateList : function (data) {
			var $id = '#' + this.options.id;

			$.each(data, function (i, value) {
				var option = $("<option />").val(value.id).text(value.series_name);
				$($id).append(option);
			});
		}
	}
})(jQuery);
