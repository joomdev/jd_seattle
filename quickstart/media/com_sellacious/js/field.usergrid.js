/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var JFormFieldUserGrid = function () {
	this.ajax = null;
};

(function ($) {
	JFormFieldUserGrid.prototype = {
		setup : function (element) {
			var $this = this;

			$this.wrapper = $(element);

			$this.wrapper.on('change blur', '.jff-ug-input-email,.jff-ug-input-name,.jff-ug-input-cl', function () {
				$this.validate();
			});

			$this.wrapper.on('blur', '.jff-ug-input-email', function () {
				$this.loadUser();
			});

			$this.wrapper.on('click', '.jff-ug-add', function () {
				if ($this.validate()) $this.addRow();
			});

			$this.wrapper.on('click', '.jff-ug-remove', function () {
				$this.removeRow(this);
			});
		},

		getToken: function () {
			var token = '';
			$('input[type="hidden"]').each(function () {
				var val = $(this).val();
				var name = $(this).attr('name') || '';
				if (val == 1 && name.length == 32) {
					token = name;
					return false;
				}
			});
			return token;
		},

		validate: function () {
			var $email = this.wrapper.find('.jff-ug-input-email');
			var $name = this.wrapper.find('.jff-ug-input-name');
			var email = $email.val();
			var name = $name.val();
			var exists = this.exists(email);
			var regex = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;

			$email.toggleClass('jff-invalid', (!regex.test(email) || exists) && email != '');
			$name.toggleClass('jff-invalid', (name.length <= 3) && name != '');

			var valid = regex.test(email) && !exists && name.length > 3;

			this.wrapper.find('.jff-ug-add').toggleClass('disabled', !valid);

			return valid;
		},

		loadUser: function () {
			var $this = this;
			var email = $this.wrapper.find('.jff-ug-input-email').val();
			var data = {key: 'email', value: email};
			var token = $this.getToken();

			data[token] = 1;

			if ($this.ajax) $this.ajax.abort();

			$this.wrapper.find('.jff-ug-input-id').val('');
			// $this.wrapper.find('.jff-ug-input-name').val('');
			$this.validate();

			$this.ajax = $.ajax({
				url: 'index.php?option=com_sellacious&task=user.getUserAjax',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data
			}).done(function (response) {
				var email = $this.wrapper.find('.jff-ug-input-email').val();
				if (response.state == 1 && response.data.email == email) {
					$this.wrapper.find('.jff-ug-input-id').val(response.data.id);
					$this.wrapper.find('.jff-ug-input-name').val(response.data.name);
					$this.wrapper.find('.jff-ug-input-email').val(response.data.email);
					$this.validate();
				}
			}).fail(function () {
				// Ignore
			});
		},

		exists: function (email) {
			var exists = false;
			this.wrapper.find('.jff-ug-items').find('tr').each(function () {
				var cInput = $(this).find('input[name*="[email]"]');
				if (cInput.val() == email) {
					exists = true;
					cInput.closest('td').css('font-weight', 'bold');
				}
				else cInput.closest('td').css('font-weight', '');
			});
			return exists;
		},

		addRow : function () {
			var $this = this;
			var email = $this.wrapper.find('.jff-ug-input-email').val();

			if (!$this.exists(email)) {
				var elName = $this.wrapper.data('name');
				var id = $this.wrapper.find('.jff-ug-input-id').val();
				var name = $this.wrapper.find('.jff-ug-input-name').val();
				var cl = $this.wrapper.find('.jff-ug-input-cl').val();

				var elI = $('<input>', {type: 'hidden', name: elName + '[id][]'}).val(id);
				var elN = $('<input>', {type: 'hidden', name: elName + '[name][]'}).val(name);
				var elE = $('<input>', {type: 'hidden', name: elName + '[email][]'}).val(email);
				var elL = $('<input>', {type: 'text', name: elName + '[credit_limit][]', 'class': 'inputbox form-control', 'data-float': 2}).val(cl);
				var dvN = $('<div>', {'class': 'input'}).text(name);
				var dvE = $('<div>', {'class': 'input'}).text(email);
				var btn = '<a class="btn btn-danger jff-ug-remove"><i class="fa fa-minus"></i> </a>';

				var $row = $('<tr>')
					.append($('<td>').append(elE).append(dvE))
					.append($('<td>').append(elN).append(dvN))
					.append($('<td>').append(elL))
					.append($('<td>').append(elI).append(btn));

				$this.wrapper.find('.jff-ug-items').append($row);
				$this.wrapper.find('.jff-ug-input-id').val('');
				$this.wrapper.find('.jff-ug-input-name').val('');
				$this.wrapper.find('.jff-ug-input-email').val('').focus();
				$this.wrapper.find('.jff-ug-input-cl').val('');
			}
		},

		removeRow : function (element) {
			var $el = $(element);
			if ($el.data('confirm')) {
				$el.data('confirm', false);
				$el.html('<i class="fa fa-minus"></i> ');
				$el.closest('tr').remove();
			} else {
				$el.data('confirm', true);
				$el.html('<i class="fa fa-question-circle"></i> ');
				setTimeout(function () {
					$el.data('confirm', false);
					$el.html('<i class="fa fa-minus"></i> ');
				}, 5000);
			}
		}
	};

	$(document).ready(function () {
		$('.jff-ug-wrapper').each(function () {
			var o = new JFormFieldUserGrid;
			o.setup(this);
		});
	});
})(jQuery);
