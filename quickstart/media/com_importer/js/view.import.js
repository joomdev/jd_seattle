/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
Joomla = window.Joomla || {};

Joomla.getToken = function () {
	var token = '';
	jQuery('input[type="hidden"][name]').each(function () {
		var val = jQuery(this).val();
		var name = jQuery(this).attr('name') || '';
		if (parseInt(val) === 1 && name.length === 32) {
			token = name;
			return false;
		}
	});
	return token;
};

jQuery(document).ready(function ($) {

	function formatBytes(bytes, decimals) {
		bytes = parseInt(bytes);
		if (isNaN(bytes) || bytes === 0) return '0 B';
		var k = 1024,
			dm = decimals || 2,
			sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
			i = Math.floor(Math.log(bytes) / Math.log(k));
		return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
	}

	var filesSelected = function (target) {
		var files = target.files;
		var $this = $(this);
		if (files && files.length > 0) {
			var info = '&nbsp; &mdash; &nbsp;' + files[0].name + ' (' + formatBytes(files[0].size) + ')';
			if ($(target).is('.no-upload')) {
				$this.find('.file-info').removeClass('hidden').html(info);
			} else {
				$this.find('.upload-input').addClass('hidden');
				$this.find('.upload-process').removeClass('hidden');
				$this.closest('form').submit();
			}
		} else {
			$this.find('.file-info').addClass('hidden').html('');
		}
	};

	var $importTabs = $('.import-tab');

	var $activeSection = $importTabs.find('.importer-block.is-active');

	$activeSection.addClass('section-open');

	var $uploadWrappers = $importTabs.find('.jff-fileplus-wrapper');

	$uploadWrappers.each(function () {
		var $wrapper = $(this);
		$wrapper.on('click', '.jff-fileplus-add', function (event) {
			event.preventDefault();
			$wrapper.find('input[type="file"]').click();
		});

		$wrapper.find('input[type="file"]').change(function (event) {
			filesSelected.call($wrapper[0], event.target);
		});

		$wrapper
			.on('dragover', '.jff-fileplus-add', function (event) {
				event.preventDefault();
				event.stopPropagation();
				$(this).addClass('dragover');
			})
			.on('dragleave', '.jff-fileplus-add', function (event) {
				event.preventDefault();
				event.stopPropagation();
				$(this).removeClass('dragover');
			})
			.on('drop', '.jff-fileplus-add', function (event) {
				event.preventDefault();
				event.stopPropagation();
				$(this).removeClass('dragover');
				var event2 = event.originalEvent || event;
				if (event2.dataTransfer) {
					var fileInput = $wrapper.find('input[type="file"]')[0];
					fileInput.files = event2.dataTransfer.files;
					filesSelected.call($wrapper[0], fileInput);
				}
			});
	});

	var $sectionToolbars = $importTabs.find('.template-title');

	$sectionToolbars.click(function () {
		var $currentTab = $(this).closest('.import-tab');
		var $currentBlock = $(this).closest('.importer-block');
		$currentTab.find('.importer-block.section-open').not($currentBlock).removeClass('section-open');
		$currentBlock.toggleClass('section-open');
	});

	$sectionToolbars.find('.btn-edit').click(function () {
		var $currentSection = $(this).closest('.importer-block');
		var templateId = $.trim($currentSection.find('span.title-text').data('id'));
		var templateTitle = $.trim($currentSection.find('span.title-text').text());
		var message = Joomla.JText._('COM_IMPORTER_IMPORT_TEMPLATE_RENAME_MESSAGE', 'Please enter a new title for this import template:');
		var newTitle = prompt(message, templateTitle);

		if (newTitle == null || $.trim(newTitle) === '') {
			return false;
		}

		var $token = Joomla.getToken();
		var data = {};
		data[$token] = 1;

		$.ajax({
			url: 'index.php?option=com_importer&task=template.renameAjax',
			data: $.extend({}, data, {id: templateId, title: newTitle}),
			type: 'post',
			dataType: 'json',
			timeout: 15000
		}).done(function (response) {
			if (response.state === 1) {
				$currentSection.find('span.title-text').text(newTitle);
				Joomla.renderMessages({success: [response.message]});
			} else {
				Joomla.renderMessages({warning: [response.message]});
			}
		}).fail(function (xhr) {
			console.log(xhr.responseText);
			Joomla.renderMessages({warning: ['Rename failed. Unknown response from server.']});
		});

		return false;
	});

	$sectionToolbars.find('.btn-delete').click(function () {
		var $currentSection = $(this).closest('.importer-block');
		var templateId = $.trim($currentSection.find('span.title-text').data('id'));
		var templateTitle = $.trim($currentSection.find('span.title-text').text());
		var message = Joomla.JText._('COM_IMPORTER_IMPORT_TEMPLATE_DELETE_WARNING', 'Are you sure you want to delete this template?');

		if (!confirm(message + "\n\n• " + templateTitle)) {
			return false;
		}

		var $token = Joomla.getToken();
		var data = {};
		data[$token] = 1;

		$.ajax({
			url: 'index.php?option=com_importer&task=template.removeAjax',
			data: $.extend({}, data, {id: templateId}),
			type: 'post',
			dataType: 'json',
			timeout: 15000
		}).done(function (response) {
			if (response.state === 1) {
				$currentSection.remove();
				Joomla.renderMessages({success: [response.message]});
			} else {
				Joomla.renderMessages({warning: [response.message]});
			}
		}).fail(function (xhr) {
			console.log(xhr.responseText);
			Joomla.renderMessages({warning: ['Delete failed. Unknown response from server.']});
		});

		return false;
	});

	var $sortableAreas = $importTabs.find('.sortable-area');

	$sortableAreas.each(function () {
		var $sortArea = $(this);
		var $sortableGroup = $sortArea.find('.sortable-group');
		$sortableGroup.disableSelection();
		$sortableGroup.sortable({
			connectWith: $sortableGroup,
			placeholder: 'placeholder'
		});
		$sortArea.find('ul.alias-drop').on('sortreceive', function (event, ui) {
			var children = $(this).children();
			var multiple = $(this).data('multiple') || false;

			if (multiple === false) {
				children.not(ui.item).appendTo($sortArea.find('.headerList'));
			} else if (multiple !== true && multiple < children.length) {
				ui.item.appendTo($sortArea.find('.headerList'));
			}
		});
		$sortArea.find('.headers-container').draggable({axis: 'y', containment: '.headers-cell'});
	});

	$importTabs.find('.btn-save-mapping').click(function () {
		var $btn = $(this);
		var $currentSection = $(this).closest('.importer-block');

		var alias = {};
		$currentSection.find('ul.alias-drop').each(function () {
			var k = $(this).data('column');
			var item = $(this).find('.sortable-item');
			if (item.length) alias[k] = item.data('alias');
		});

		if (!Object.keys(alias).length) {
			alert('Please map some columns to create a mapping template.');
			return;
		}

		var name = $.trim($currentSection.find('.txt-save-mapping').val());
		if (name === '') {
			alert('Please enter a name for the new template from current mapping.');
			return;
		}

		var source = $currentSection.find('input[name="source"]').val();
		var $token = Joomla.getToken();

		var data = $.extend({}, {jform: {source: source, name: name, alias: alias}});
		data[$token] = 1;

		$.ajax({
			url: 'index.php?option=com_importer&task=template.saveAjax',
			data: data,
			type: 'post',
			dataType: 'json',
			timeout: 10000,
			beforeSend: function () {
				// Using toggle is not a great idea here
				$btn.find('i.fa').toggleClass('hidden');
				$btn.addClass('disabled');
			}
		}).done(function (response) {
			if (response.state === 1) {
				$currentSection.find('.txt-save-mapping').val('');
				alert(response.message);
			} else {
				alert(response.message);
			}
		}).fail(function (xhr) {
			console.log(xhr.responseText);
			alert('Template save failed. Unknown response from server.');
		}).always(function () {
			// Using toggle is not a great idea here
			$btn.find('i.fa').toggleClass('hidden');
			$btn.removeClass('disabled');
		});
	});
});
