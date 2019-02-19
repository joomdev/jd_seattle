/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
var ModSellaciousFiltersLocation = function () {
	this.options = {
	};
};

(function ($) {
	ModSellaciousFiltersLocation.prototype = {
		setup: function (options) {

			$.extend(this.options, options);

			var $this = this;
			var $id = '#' + $this.options.id;
			var $hid = '#' + $this.options.hid;

			$($id).autocomplete({
				source: function( request, response ) {
					var pData = {
						option: 'com_ajax',
						module: 'sellacious_filters',
						method: 'getAutoCompleteSearch',
						format: 'json',
						term: request.term,
						parent_id: 1,
						types: $this.options.types,
						list_start: 0,
						list_limit: 5,
						Itemid: $this.options.itemId
					};
					$.ajax({
						url: "index.php",
						dataType: "json",
						data: pData,
						success: function(data) {
							response(data);
						}
					});
				},
				select: function(event, ui) {
					$($id).val(ui.item.value);
					$($hid).val(ui.item.id);

					return false;
				},
				minLength: 3
			});
		}
	}
})(jQuery);
