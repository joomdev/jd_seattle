/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

jQuery(function($) {
	$(document).ready(function () {
		var customTagPrefix = '#new#';
		$('#jform_tagids_chzn input').keyup(function(event) {

			if (this.value && this.value.length >= 3 && (event.which === 13 || event.which === 188)) {
				var highlighted = $('#jform_tagids_chzn').find('li.active-result.highlighted').first();
				if (event.which === 13 && highlighted.text() !== '') {
					var customOptionValue = customTagPrefix + highlighted.text();
					$('#jform_tagids option').filter(function () { return $(this).val() == customOptionValue; }).remove();
					var tagOption = $('#jform_tagids option').filter(function () { return $(this).html() == highlighted.text(); });
					tagOption.attr('selected', 'selected');
				} else {
					var customTag = this.value;
					var tagOption = $('#jform_tagids option').filter(function () { return $(this).html() == customTag; });
					if (tagOption.text() !== '') {
						tagOption.attr('selected', 'selected');
					} else {
						var option = $('<option>');
						option.text(this.value).val(customTagPrefix + this.value);
						option.attr('selected','selected');
						$('#jform_tagids').append(option);
					}
				}
				this.value = '';
				$('#jform_tagids').trigger('liszt:updated');
				event.preventDefault();
			}
		});
	});
});
