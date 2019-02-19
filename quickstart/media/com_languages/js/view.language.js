/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	var $jformImage = $('#jform_image');
	var paths = Joomla.getOptions('system.paths', {});
	var base = paths.root || '..';
	var flagFormatter = function (opt) {
		if (opt.id == '') return '&nbsp;';
		var uri = base + '/media/mod_languages/images/' + opt.id + '.gif';
		var split = opt.id.split('_');
		var label = split[0] + (split[1] ? '-' + split[1].toUpperCase() : '');
		return '<img src="' + uri + '"/> &nbsp; ' + label + (label === opt.id ? '' : ' (' + opt.id + ')');
	};
	$jformImage.select2('destroy');
	$jformImage.select2({
		formatResult: flagFormatter,
		formatSelection: flagFormatter
	});
});
