/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {

	var $toolbar = $('#toolbar');
	var $button = $toolbar.find('#toolbar-upload');

	if (!$button.length) return;

	var label = $button.find('button').html();
	var $btnDiv = $('<div>', {'class': 'btn-upload btn-group btn-wrapper', id: 'btn-keyboard'});
	var $btnEl = $('<button class="btn btn-sm btn-default bnt-toolbar-upload">' + label + '</button>');
	$btnDiv.append($btnEl);
	$button.replaceWith($btnDiv);

	$btnEl.click(function () {
		$('.uploadform').toggleClass('active');
	});

	// Inject keyboard shortcut button into toolbar. Injecting here because this is only coupled here.
	$('.uploadform-close').click(function () {
		$('.uploadform').toggleClass('active');
	});

	var $wrapper = $('#upload_wrapper');

	$wrapper.on('click', '.jff-fileplus-add', function (e) {
		e.preventDefault();
		$('#upload_wrapper').find('input[type="file"]').click();
	});

	$wrapper.find('input[type="file"]').change(function (event) {
		var files = event.target.files;
		if (files && files.length > 0) {
			$wrapper.find('.upload-input').addClass('hidden');
			$wrapper.find('.upload-process').removeClass('hidden');
			document.getElementById('import-form').submit();
		}
	});
});
