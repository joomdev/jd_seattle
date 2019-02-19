/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
SellaciousViewAddresses = function () {
	this.token = '__INVALID_TOKEN__';
	this.hash = '';
	this.baseUrl = '';
	this.ajax = null;

	return this;
};

jQuery(function ($) {

	$.fn.hScroll = function (amount) {
		amount = amount || 120;
		$(this).bind('DOMMouseScroll mousewheel', function (event) {
			var origEvent = event.originalEvent, direction = origEvent.detail ? origEvent.detail * -amount : origEvent.wheelDelta, position = $(this).scrollLeft();
			position += direction > 0 ? -amount : amount;
			$(this).scrollLeft(position);
			event.preventDefault();
		});
	};

	SellaciousViewAddresses.prototype = {
		formFields: {},

		elements: {
			container: '#addresses',
			editor: '#address-editor',
			viewer: '#address-viewer',
			boxes_container: '#address-items',
			modals_container: '#address-modals',
			box_single_class: '.address-item',
			modal_form: '.address-form-content'
		},

		element: function (name, selectorOnly) {
			return selectorOnly ? this.elements[name] : this.container.find(this.elements[name]);
		},

		init: function (elements) {
			var $this = this;

			$.extend($this.elements, elements);
			$this.container = $($this.elements.container);

			var paths = Joomla.getOptions('system.paths', {});
			$this.baseUrl = (paths.base || paths.root || '') + '/index.php';

			$this.container

				.on('click', '.remove-address', function () {
					var address_id = $(this).data('id');
					if (address_id && confirm(Joomla.JText._('COM_SELLACIOUS_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE')))
						$this.remove(address_id);
				})

				.on('click', '.btn-save-address', function () {
					var formKey = $this.element('modal_form', true);
					var $form = $(this).closest('.modal').find(formKey);
					var data = $this.validate($form);

					if (data) $this.save(data, function () {
						// Todo: Reset the form filled values in add new address
					});
				});

			this.loadEditor();
		},

		loadEditor: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();

			$this.element('boxes_container').html('<br><h3>Please wait&hellip;</h3>').removeClass('hidden');
			$this.element('modals_container').html('');

			var data = {
				option: 'com_sellacious',
				task: 'user.getAddressesHtmlAjax'
			};
			data[$this.token] = 1;
			$this.ajax = $.ajax({
				url: $this.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data
			}).done(function (response) {
				if (response.status === 1031) { // Not logged in
					Joomla.renderMessages({warning: [response.message]});
				} else if (response.status === 1032) {
					$this.element('boxes_container').html(response.data[0]);
					$this.element('modals_container').html(response.data[1]);

					$this.element('boxes_container').find('.hasTooltip').tooltip();
					$this.element('boxes_container').find('.hasSelect2').select2();
					$this.element('boxes_container').popover({trigger: 'hover'});

					$this.element('modals_container').find('.hasTooltip').tooltip();
					$this.element('modals_container').find('.hasSelect2').select2();
					$this.element('modals_container').popover({trigger: 'hover'});
				} else {
					$this.element('boxes_container').html('<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
						'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>');
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				$this.element('boxes_container').html('<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
					'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>');
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				console.log(jqXHR.responseText);
			});
		},

		remove: function (address_id) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			var data = {
				option: 'com_sellacious',
				task: 'user.removeAddressAjax',
				id: address_id
			};
			data[$this.token] = 1;
			$this.ajax = $.ajax({
				url: $this.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data
			}).done(function (response) {
				if (response.status === 1031) {
					// Not logged in
					Joomla.renderMessages({warning: [response.message]});
				} else if (response.status === 1033) {
					// Removed
					Joomla.renderMessages({success: [response.message]});
					$this.loadEditor();
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				console.log(jqXHR.responseText);
			});
		},

		getFormFields: function () {
			var $this = this;
			// Optimised to calculate only once
			if (Object.keys($this.formFields).length === 0) {
				var forms = $this.element('modal_form');
				if (forms.length) {
					forms.eq(0).find('[class*=" address-"],[class^="address-"]').each(function () {
						var field = $(this).attr('class').match(/address-([\w]+)/i);
						if (field) $this.formFields[field[1]] = $(this).is('.required') ? 1 : 0;
					});
				}
			}
			return $this.formFields;
		},

		validate: function ($form) {
			var $this = this;
			var data = {};
			var invalid = {};
			var valid = true;

			var fields = $this.getFormFields();

			$.each(fields, function (fieldKey, required) {
				var field = $form.find('.address-' + fieldKey);
				var field_input = field.is('fieldset') ? field.find('input:checked') : field.filter(':input');
				var labels = $(field).closest('tr').find('label');
				var value = $.trim(field_input.val());
				if (required && value === '') {
					field_input.addClass('invalid');
					labels.addClass('invalid');
					valid = false;
					invalid[fieldKey] = value;
				} else {
					field_input.removeClass('invalid');
					labels.removeClass('invalid');
					data[fieldKey] = value;
				}
			});
			if (valid)
				return data;
			Joomla.renderMessages({warning: ['Invalid or incomplete address form.']});
			console.log('Invalid form:', data, invalid);
			return false;
		},

		save: function (address, callback) {
			var $this = this;

			if ($this.ajax) $this.ajax.abort();

			var data = {
				option: 'com_sellacious',
				task: 'user.saveAddressAjax',
				address: address,
				id: address.id
			};
			data[$this.token] = 1;

			$this.ajax = $.ajax({
				url: $this.baseUrl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data
			}).done(function (response) {
				if (response.status === 1031) { // Not logged in
					$this.container.find('#address-form-' + address.id).modal('hide');
					Joomla.renderMessages({warning: [response.message]});
				} else if (response.status === 1035) { // Saved
					$this.container.find('#address-form-' + address.id).modal('hide');
					Joomla.renderMessages({success: [response.message]});
					$this.loadEditor();
					if (typeof callback === 'function') callback();
				} else {
					alert(response.message);
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				console.log(jqXHR.responseText);
			});
		}
	};

	$(document).ready(function () {
		var o = new SellaciousViewAddresses;
		o.token = $('#formToken').attr('name');
		o.init();
		$('#address-items').hScroll(60);
		$('#addresses').find('select').select2();
	});
});

