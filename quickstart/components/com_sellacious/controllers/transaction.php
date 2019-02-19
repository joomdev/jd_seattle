<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Transaction\TransactionHelper;

defined('_JEXEC') or die;

/**
 * Transaction controller class copied from backend for the Wallet related function support in cart.
 *
 * @since   1.2.0
 */
class SellaciousControllerTransaction extends SellaciousControllerForm
{
	/**
	 * @var string
	 *
	 * @since   1.2.0
	 */
	protected $view_list = 'transactions';

	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_TRANSACTION';

	/**
	 * Get wallet balance of the selected user id via ajax
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function getWalletBalanceAjax()
	{
		// fixme: Access check
		$user_id = $this->input->post->getInt('user_id');

		try
		{
			if (!$user_id)
			{
				throw new Exception(JText::_($this->text_prefix . '_NO_USER_SPECIFIED'));
			}

			$currency = $this->helper->currency->getGlobal('code_3');
			$balances = $this->helper->transaction->getBalance($user_id);

			$balances = array_filter($balances, function ($value)
			{
				return $value->amount > 0;
			});

			foreach ($balances as &$balance)
			{
				$balance->convert_currency = $currency;
				$balance->convert_amount   = $this->helper->currency->convert($balance->amount, $balance->currency, $currency);
				$balance->convert_display  = $this->helper->currency->display($balance->amount, $balance->currency, $currency);
			}

			$response = array(
				'state'   => 1,
				'message' => '',
				'data'    => array_values($balances),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);

		jexit();
	}

	/**
	 * Convert wallet balance in a selected currency of the selected seller uid to shop currency; via ajax
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function convertBalanceAjax()
	{
		// fixme: access check
		$userId   = $this->input->post->getInt('user_id');
		$currency = $this->input->post->getString('currency');

		try
		{
			if (!$userId)
			{
				throw new Exception(JText::_($this->text_prefix . '_NO_USER_SPECIFIED'));
			}

			// TODO: Allow conversion to any amount and currency by parameter
			list($balAmt) = TransactionHelper::getUserBalance($userId, $currency);
			$g_currency   = $this->helper->currency->getGlobal('code_3');

			if ($balAmt < 0.01)
			{
				throw new Exception(JText::_($this->text_prefix . '_INVALID_FOREX_PARAMS'));
			}

			$done = TransactionHelper::forexConvert($userId, $balAmt, $currency, $g_currency);

			$response = array(
				'state'   => $done,
				'message' => JText::_($this->text_prefix . ($done ? '_FOREX_SUCCESS' : '_FOREX_FAILED')),
				'data'    => null,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);

		jexit();
	}
}
