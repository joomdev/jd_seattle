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

/**
 * Currencies list controller class.
 */
class SellaciousControllerCurrencies extends SellaciousControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_CURRENCIES';

	/**
	 * Proxy for getModel.
	 *
	 * @param string $name
	 * @param string $prefix
	 * @param null   $config
	 *
	 * @return object
	 */
	public function getModel($name = 'Currency', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Save entered forex rates in list
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function save()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel();

		$cid  = $this->input->post->get('cid', array(), 'array');
		$data = $this->input->post->get('jform', array(), 'array');

		$this->setRedirect($this->getRedirectURL());

		try
		{
			if (!$this->helper->access->check('currency.edit.forex'))
			{
				$this->setMessage(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'error');

				return false;
			}

			$i = 0;

			foreach ($data as $record)
			{
				if (isset($record['id']) && in_array($record['id'], $cid))
				{
					$i += $model->saveForex($record);
				}
			}

			$this->setMessage(JText::plural($this->text_prefix . '_FOREX_SAVE_SUCCESS_N', $i));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Trigger live forex update instantly irrespective of set schedule
	 *
	 * @return  bool
	 */
	public function updateForex()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect($this->getRedirectURL());

		try
		{
			$this->helper->currency->updateForex(true);

			$this->setMessage(JText::_($this->text_prefix . '_FOREX_LIVE_UPDATE_SUCCESS'));
		}
		catch (Exception $e)
		{
			// $this->setMessage(JText::_('Live forex update is not implemented yet. This will be done later.'));
			$this->setMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}
}
