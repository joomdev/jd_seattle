<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$data = array(
	'name'  => $this->getName(),
	'state' => $this->state,
	'item'  => $this->item,
	'form'  => $this->form,
);

$options = array(
	'client' => 2,
	'debug'  => 0,
);

$g_currency = $this->helper->currency->getGlobal('code_3');
$this->form->bind(array('currency' => $g_currency));

echo JLayoutHelper::render('com_sellacious.view.edit', $data, '', $options);

JText::script('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_BUY_NOW');
JText::script('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_ADD_TO_CART');
?>
<div class="clearfix"></div>
<fieldset class="bordered padding-10 w100p">
	<legend class="strong padding-horizontal-10" style="width: auto; border: 0; margin-left: 10px"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUTTON_LEGEND_CLICK_TO_COPY') ?></legend>
	<pre id="html-code" class="padding-5 w100p hasTooltip" style="white-space: normal"><?php echo htmlspecialchars('<a href="default.php">Buy Now</a>') ?></pre>
</fieldset>
<script>
	jQuery(document).ready(function ($) {
		var pid = null;
		var values = {};
		var title = $('#jform_title').val();
		var $fields = $('.button-param');
		var randomString = function (len) {
			var text = "";
			var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			for(var i=0; i < len; i++) text += possible.charAt(Math.floor(Math.random() * possible.length));
			return text;
		};
		var buildCode = function () {
			var checkout = $fields.filter('.field-checkout').find(':checked').val();
			var $markup = $('<a></a>', {href: '#', 'class': 'btn btn-primary btn-cart-add-external'});
			if (checkout === 'true') {
				$markup.text(Joomla.JText._('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_BUY_NOW', 'Buy'));
			} else {
				$markup.text(Joomla.JText._('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_ADD_TO_CART', 'Add'));
			}
			values = {};
			$fields.each(function () {
				var $el = $(this), v, k;
				k = $el.attr('id').replace(/^jform_params_/, '');
				v = $el.is(':input') ? $el.val() : $el.find('input:checked').val();
				if (v !== '') {
					$markup.attr('data-' + k, v);
					values[k] = v;
				}
				// Custom defaults
				if (k === 'currency' && v.length !== 3) $markup.attr('data-' + k, '<?php echo $g_currency ?>');
			});
			if (typeof values['product_id'] === 'undefined' || values['product_id'] === '') {
				values['product_id'] = pid || (pid = randomString(6));
				$markup.attr('data-product_id', values['product_id']);
			}
			$('#html-code').text($markup.prop('outerHTML'));
		};
		$fields.on('change blur click', buildCode);
		buildCode();

		$('#html-code').click(function (e) {
			if (!document.formvalidator.isValid(document.getElementById('adminForm'))) {
				e.preventDefault();
				alert(Joomla.JText._('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_INVALID_FORM', 'Please complete the required fields.'));
				return false;
			}
			var range, success;
			if (document.selection) { // IE
				range = document.body.createTextRange();
				range.moveToElementText(document.getElementById('html-code'));
				range.select();
				success = document.execCommand('Copy');
			} else if (window.getSelection) {
				range = document.createRange();
				range.selectNode(document.getElementById('html-code'));
				window.getSelection().removeAllRanges();
				window.getSelection().addRange(range);
				success = document.execCommand('Copy');
			}
			if (success) {
				var $element = $(this);
				$element.tooltip('destroy').attr('title', 'Code copied to clipboard!').tooltip().tooltip('show');
				setTimeout(function () {
					$element.tooltip('hide').tooltip('destroy').attr('title', '');
				}, 1000);
			}
		});

		var getToken = function () {
			var token = '';
			$('input[type="hidden"][name]').each(function () {
				var name = $(this).attr('name');
				var value = $(this).val();
				if (value === '1' && name.length === 32) token = name;
			});
			return token;
		};

		$('#toolbar-save').click(function (e) {
			if (!document.formvalidator.isValid(document.getElementById('adminForm'))) {
				e.preventDefault();
				alert(Joomla.JText._('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_INVALID_FORM', 'Please complete the required fields.'));
				return false;
			}

			var id = $('#jform_id').val();
			var $title = $('#jform_title');
			var title = $title.val();
			if (!title) {
				title = window.prompt(Joomla.JText._('COM_SELLACIOUS_PRODUCT_BUTTON_TITLE_PROMPT', 'Choose a name for this button:'), title + '');
				if (title === null || title === "") return;
				// Force string type, else it will go into infinite recursion
				$title.val(title + '');
			}

			var paths = Joomla.getOptions('system.paths', {});
			var base = paths.base || '';
			var token = getToken();
			var data = {
				option: 'com_sellacious',
				task: 'productbutton.saveAjax',
				jform: {
					id: id,
					title: title,
					params: values
				}
			};
			data[token] = 1;
			console.log(data);
			$.ajax({
				url: base + '/index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data
			}).done(function (response) {
				if (response.status === 1) {
					$('#jform_id').val(response.data.id || id);
					Joomla.renderMessages({message: [response.message]});
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				console.log(jqXHR.responseText);
				Joomla.renderMessages({error: ['An unknown error occurred while trying to save. Please try again.']});
			});
		});

		// '    data-source_id="SRC99" ' +
		// '    data-transaction_id="TRN01" ' +
		// '    data-product_id="999" ' +
		// '    data-product_title="My Custom Product to sell via Sellacious" ' +
		// '    data-product_sku="CUSTOM-01" ' +
		// '    data-link_url="http://bhartiy.com" ' +
		// '    data-image_url="http://localhost/~izhar/istore/sellacious/templates/sellacious/images/logo.png" ' +
		// '    data-product_type="physical" ' +
		// '    data-seller_company="" ' +
		// '    data-seller_email="" ' +
		// '    data-currency="GBP" ' +
		// '    data-price_margin="10" ' +
		// '    data-margin_percent="1" ' +
		// '    data-cost_price="100" ' +
		// '    data-list_price="300" ' +
		// '    data-calculated_price="110" ' +
		// '    data-flat_price="150" ' +
		// '    data-flat_shipping="1" ' +
		// '    data-shipping_fee="25" ' +
		// '    data-length="3" ' +
		// '    data-width="2" ' +
		// '    data-height="4" ' +
		// '    data-size_unit="FT" ' +
		// '    data-weight="0.75" ' +
		// '    data-weight_unit="KG" ' +
		// '    data-checkout="true"' +
	});
</script>
