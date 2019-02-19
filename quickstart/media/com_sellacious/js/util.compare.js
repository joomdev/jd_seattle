/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(function ($) {

	// For compare bar position override just create a div#compare-bar on the page anywhere, it will be used instead.
	$(document).ready(function () {

		var paths = Joomla.getOptions('system.paths', {});
		var base = paths.base || paths.root || '';

		var updateCheckboxes = function (selected) {
			$('.btn-compare').each(function () {
				var value = $(this).data('item');
				if (selected && selected.indexOf(value) >= 0) {
					$(this).prop('checked', true);
					$(this).addClass('checked');
					$(this).closest('label').addClass('active');
				} else {
					$(this).prop('checked', false);
					$(this).removeClass('checked');
					$(this).closest('label').removeClass('active');
				}
			});
		};

		var buildBar = function (data) {
			if (data && data.html) {
				var bar = '#compare-bar';
				if ($(bar).length == 0)
					$('<div id="compare-bar" class="w100p"></div>').insertAfter($('#system-message-container'));
				$(bar).html(data.html).append('<div class="clearfix"></div>');
			}
		};

		$('body').on('click', '.compare-remove', function () {
			var code = $(this).data('item');

			$.ajax({
				url: base + '/index.php?option=com_sellacious&task=compare.removeAjax',
				type: 'POST',
				data: {p: code},
				cache: false,
				dataType: 'json',
				success: function (response) {
					if (response.state == 1) {
						updateCheckboxes(response.data.output);
						buildBar(response.data);
					}
				},
				error: function (jqXHR) {
					Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
					console.log(jqXHR.responseText);
				}
			});
		});

		// Bind compare click handler
		$('.btn-compare').click(function () {
			var $this = $(this);

			// Since data is not automatically toggle like prop-checked, we do it manually
			$this.toggleClass('checked');

			var checked = $this.is(':checked') || $this.is('.checked');
			var code = $this.data('item');
			var lbl = $this.closest('label');

			checked ? lbl.addClass('active') : lbl.removeClass('active');

			$.ajax({
				url: base + '/index.php?option=com_sellacious&task=compare.' + (checked ? 'addAjax' : 'removeAjax'),
				type: 'POST',
				data: {p: code},
				cache: false,
				dataType: 'json',
				success: function (response) {
					if (response.state == 1) {
						Joomla.renderMessages({success: [response.message]});
						buildBar(response.data);
					} else {
						Joomla.renderMessages({error: [response.message]});
						$this.prop('checked', !checked);
						$this.toggleClass('checked');
						checked ? lbl.removeClass('active') : lbl.addClass('active');
					}
				},
				error: function (jqXHR) {
					Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
					console.log(jqXHR.responseText);
					$this.prop('checked', !checked);
					$this.toggleClass('checked');
					checked ? lbl.removeClass('active') : lbl.addClass('active');
				}
			});
		});

		// Check current/initial compare list on page load
		$.ajax({
			url: base + '/index.php?option=com_sellacious&task=compare.addAjax',
			type: 'POST',
			data: {p: ''},
			cache: false,
			dataType: 'json',
			success: function (response) {
				// We did not send any thing. So just use received data
				if (response.state == 0 || response.state == 1) {
					updateCheckboxes(response.data.output);
					buildBar(response.data);
				}
			},
			error: function (jqXHR) {
				console.log(jqXHR.responseText);
			}
		});
	});
});
