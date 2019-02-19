/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

var JFormFieldGeoState = function () {
	this.options = {
		id: 'jform',
		name: 'jform'
	};
};

(function ($) {
	JFormFieldGeoState.prototype = {

		ajaxCall: function (parentId, $this, $id, data) {
			var paths = Joomla.getOptions('system.paths', {});
			var base = paths.base || paths.root || '';
			$.ajax({
				url: base + '/index.php',
				type: 'post',
				dataType: 'json',
				data: {
					option: 'com_sellacious',
					task: 'location.autocomplete',
					query: '',
					parent_id: parentId,
					types: ['state'],
					address_type: $this.options.address_type,
					list_start: 0,
					list_limit: 0
				}
			}).done(function (response) {
				if (response.status === 1) {
					$.each(response.data, function (index, state) {
						$($id).append($('<option>').val(state.id).text(state.title));
					});
					$($id).trigger('select2:updated').select2('val', data);
				} else {
					console.log(response);
				}
			}).fail(function (xhr) {
				console.log(xhr.responseText);
			});
		},

		loadOptions: function ($id, $this, parentId) {
			var value = $($id).select2('val');
			$($id).empty();
			$this.ajaxCall(parentId, $this, $id, value);
		},

		setup: function (options) {
			$.extend(this.options, options);

			var $this = this;
			var $id = '#' + $this.options.id;
			$($id).select2();

			// Variable 'rel' is an array
			if ($this.options.rel) {
				$this.options.rel = $.map($this.options.rel, function (rel) {
					return '#' + ($this.options.fieldset ? $this.options.fieldset + '_' : '') + rel;
				});
				// This is state so only country can be rel so only first index in array is needed
				if ($($this.options.rel[0]).length) {
					$($this.options.rel[0]).change(function () {
						var parentId = $(this).val() || 1;
						$this.loadOptions($id, $this, parentId);
					}).trigger('change');
				} else {
					$this.loadOptions($id, $this, 1);
				}
			} else {
				$this.loadOptions($id, $this, 1);
			}

			return this;
		}
	}
})(jQuery);
