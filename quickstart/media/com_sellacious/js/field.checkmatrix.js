/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('.jff-checkmatrix').each(function () {
		var $matrix = $(this);
		var $input = $matrix.find('.jff-checkmatrix-input');
		var value = $.trim($input.val());
		try {
			var $values = value === '' ? {} : JSON.parse(value);
		} catch (e) {
			$values = {};
		}
		$matrix.on('change', 'input[type="checkbox"]', function () {
			var values = {};
			$matrix.find('input[type="checkbox"]').each(function () {
				var x = $(this).data('column');
				var y = $(this).data('row');
				var v = $(this).prop('checked');
				if (v) {
					typeof values[x] === 'undefined' && (values[x] = {});
					values[x][y] = 1;
				}
			});
			$input.val(JSON.stringify(values));
			console.log(JSON.stringify(values));
		}).find('input[type="checkbox"]').each(function () {
			var x = $(this).data('column');
			var y = $(this).data('row');
			if (typeof $values[x] === 'object' && typeof $values[x][y] !== 'undefined' && $values[x][y] === 1) {
				$(this).prop('checked', true);
			}
		});
	})
});
