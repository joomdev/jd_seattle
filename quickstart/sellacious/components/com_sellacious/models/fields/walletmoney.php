<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Money form field class for the Sellacious.
 *
 */
class JFormFieldWalletMoney extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'WalletMoney';

	/**
	 * Method to get the field options.
	 *
	 * @return  string  The field option objects.
	 * @since   1.6
	 */
	protected function getInput()
	{
		$helper   = SellaciousHelper::getInstance();
		$required = $this->required ? 'required ' : '';
		$value    = $this->value;

		$value  = is_array($value) ? $value : array();
		$value  = array_merge($value, array('amount' => 0, 'currency' => ''));
		$userId = $this->form->getValue('user_id');

		$optsHtml = '';
		$balances = array();

		if ($userId)
		{
			$balances = $helper->transaction->getBalance($userId);
			$optsHtml = JHtmlSelect::options($balances, 'currency', 'currency', $value['currency']);
		}

		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellacious/util.float-val.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$bal_assoc = ArrayHelper::pivot($balances, 'currency');
		$bal_json  = json_encode($bal_assoc);

		$balance_limited_msg = JText::_('COM_SELLACIOUS_WALLET_BALANCE_LOW_VALUE_UPDATED');
		$placeholder = JText::_('COM_SELLACIOUS_INPUT_PLACEHOLDER_AMOUNT', true);

		$html = <<<HTML
			<div class="input-group">
				<input type="text" name="{$this->name}[amount]" value="{$value['amount']}" id="{$this->id}_amount"
						class="form-control w100px {$required}" data-float="2" placeholder="<{$placeholder}"/>
				<select name="{$this->name}[currency]" id="{$this->id}_currency" class="w100px">{$optsHtml}</select>
				<label class="btn btn-default strong" id="{$this->id}_balance"> Current balance: <span class="color-red">0.00</span></label>
			</div>
			<label class="strong w100p" id="{$this->id}_after" 
						style="padding-top: 6px;">Remaining after withdrawal: <span class="color-red">0.00</span></label>
			<script>
				jQuery(document).ready(function($) {
					var balances = {$bal_json};
					balances.length || $('#{$this->id}_balance,#{$this->id}_after').addClass('hidden');
					$('#{$this->id}_currency,#{$this->id}_amount').on('change keyup', function(e) {
						var ela = $('#{$this->id}_amount');
						var elc = $('#{$this->id}_currency');
						var elb = $('#{$this->id}_balance');
						var elr = $('#{$this->id}_after');

						balances.length ? elr.addClass('hidden') && elb.addClass('hidden') : elr.removeClass('hidden') && elb.removeClass('hidden');

						var currency = elc.val() || '';
						var amount = ela.val();
						var balance = balances[currency] ? balances[currency]['display'] || '0.00' : '0.00';
						var bal_v = balances[currency] ? balances[currency]['amount'] || 0 : 0;
						var c_bal = Math.max(0, bal_v);

						if (e.type == 'change' && (amount > c_bal || amount < 0)) {
							ela.val(amount = c_bal).trigger('change');
							Joomla.renderMessages({info: ['{$balance_limited_msg}']});
						} else {
							Joomla.removeMessages();
						}

						var rem = bal_v - amount;
						elb.find('span').text(balance);
						elr.find('span').text(rem.toFixed(2) + ' ' + currency);
					}).triggerHandler('change');
				});
			</script>
HTML;

		return $html;
	}
}
