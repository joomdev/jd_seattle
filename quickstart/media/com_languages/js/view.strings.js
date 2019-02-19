/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(function ($) {

	// Plugin Definition
	var Translator = function (el, siblings) {
		this.element = $(el);
		this.siblings = siblings;
		this.initialize();
	};

	Translator.prototype = {

		initialize: function () {
			var $this = this;
			this.input = this.element.find('.override-input');
			this.editor = this.element.find('.lang-editor');
			this.flagHtml = this.element.find('.lang-html');
			$this.cache();
			$this.display();
			this.editor.click(function () {
				if (!$this.editor.is('.active')) {
					$this.edit();
				}
			});
			this.flagHtml.click(function () {
				if ($this.editor.is('.active')) {
					$this.edit();
				} else {
					$this.display();
				}
			});
			this.element.on('blur keydown', '.lang-textarea', function (e) {
				const KEY_ENTER = 13;
				const KEY_ESCAPE = 27;
				if (e.type === 'blur' || (e.type === 'keydown' && !e.shiftKey && e.keyCode === KEY_ENTER)) {
					e.type === 'keydown' && e.preventDefault();
					$this.close(true);
					$this.display();
				} else if (e.type === 'keydown' && !e.ctrlKey && e.keyCode === KEY_ESCAPE) {
					$this.close(false);
					$this.display();
				}
			});
			this.siblings.on('translator:edit', function (e, el) {
				if (el !== $this.element && $this.editor.is('.active')) {
					$this.close(true);
					$this.display();
				}
			});
			this.siblings.on('click', '.lang-key,.lang-string', function () {
				if ($this.editor.is('.active')) {
					$this.close(true);
					$this.display();
				}
			});
		},

		cache: function () {
			this.input.data('override', this.input.val());
		},

		reset: function () {
			this.input.val(this.input.data('override'));
		},

		display: function () {
			var isHtml = this.flagHtml.prop('checked');
			isHtml ? this.editor.html(this.input.val()) : this.editor.text(this.input.val());
		},

		edit: function () {
			var editable, value;
			this.element.trigger('translator:edit', [this.element]);
			if (this.editor.is('.active')) {
				var t = this.element.find('.lang-textarea');
				value = t.is('textarea') ? t.val() : t.html();
				this.close();
			}
			if (this.flagHtml.prop('checked')) {
				editable = $('<div/>', {'contenteditable': 'true'}).html(value || this.input.val());
			} else {
				editable = $('<textarea/>').val(value || this.input.val());
			}
			this.editor.addClass('active').html(editable.addClass('lang-textarea').attr('autocomplete', 'off'));
			editable.focus();
		},

		close: function (save) {
			if (this.editor.is('.active')) {
				if (save === true || save === false) {
					var strId = this.input.data('id');
					var isHtml = this.flagHtml.prop('checked');
					var value = isHtml ? this.element.find('.lang-textarea').html() : this.element.find('.lang-textarea').val();
					if (save === true && this.input.val() !== value) {
						this.input.val(value);
						this.element.trigger('translator:save', [this.element, strId, value, isHtml]);
					} else {
						this.element.trigger('translator:cancel-edit', [this.element, strId, value, isHtml]);
					}
				}
				this.editor.find('.lang-textarea').remove();
				this.editor.removeClass('active')
			}
		}
	};

	$.fn.extend({
		translator: function () {
			var $elements = $(this);
			$elements.each(function () {
				$(this).data('translator') || $(this).data('translator', new Translator(this, $elements));
			});
			return $elements;
		},

		getFormToken: function (nv) {
			var c = '';
			$('input[type="hidden"]').each(function () {
				var b = $(this).attr('name') || '';
				if ($(this).val() === '1' && b.length === 32) {
					c = b;
					return !1;
				}
			});
			if (!nv) return c;
			var d = {};
			d[c] = 1;
			return d;
		}
	});

	// Page events
	$(document).ready(function ($) {

		var lang = $('#list_language').val();

		var $translatable = $('.overriderrow').translator();

		var $token = $(document).getFormToken(true);

		if (/[a-z]{2,3}-[A-Z]{2}/.test(lang)) {
			$translatable.on('translator:save', function (e, row, strId, value, isHtml) {
				$(this).data('translator-draft', value).addClass('hasDraft');
				$.ajax({
					url: 'index.php?option=com_languages&task=string.saveAjax&format=json',
					type: 'POST',
					data: $.extend({}, $token, {language: lang, id: strId, value: value, html: isHtml}),
					cache: false,
					dataType: 'json'
				}).done(function (response) {
					if (response.state === 1) {
						$(this).data('translator-draft', null).removeClass('hasDraft').removeClass('hasDraft');
						$.smallBox({
							title: "Saved!",
							content: response.message || 'Translation updated&hellip;',
							color: "#599e51",
							iconSmall: "fa fa-times bounce animated",
							timeout: 2000,
							sound: false
						});
					} else {
						$(this).addClass('hasError');
						$.smallBox({
							title: "Error!",
							content: response.message || 'Translation update failed&hellip;',
							color: "#df481d",
							iconSmall: "fa fa-times bounce animated",
							timeout: 4000,
							sound: false
						});
					}
				}).fail(function (jqXHR) {
					$(this).addClass('hasError');
					console.log(jqXHR.responseText);
				});
			});
		}

		$.ajax({
			url: 'index.php?option=com_languages&task=string.reindexAjax&format=json',
			type: 'POST',
			data: $.extend({}, $token),
			cache: false,
			dataType: 'json'
		}).done(function (response) {
			if (response.state === 1) {
				window.location.href = window.location.href + '';
			} else if (response.state === -1) {
				// Skipped
			} else {
				Joomla.renderMessages({error: [response.message]});
			}
		}).fail(function (jqXHR) {
			console.log('Indexing request error');
		});
	});

});

