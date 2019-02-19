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
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View to edit
 *
 * @property int counter
 */
class SellaciousViewOrder extends SellaciousView
{
	/** @var  JObject */
	protected $state;

	/** @var  Registry */
	protected $item;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl
	 *
	 * @return  mixed
	 *
	 * @since   1.2.0
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		$allowed = true;
		$me      = JFactory::getUser();
		$orderId = $this->item->get('id');
		$secret  = $this->app->input->getString('secret');
		$pks     = $this->app->getUserState('com_sellacious.order.view.authorised', array());

		if (strlen($secret))
		{
			// Password must be at least 6 characters long
			$password = substr($this->item->get('cart_hash'), 0, max(6, strlen($secret)));

			if ($secret != $password)
			{
				// New password entered incorrectly, un-authorise this order now
				$pks = array_diff($pks, array($orderId));
			}
			else
			{
				// New password entered correctly, authorise this order now
				$pks[] = $orderId;
			}

			$this->app->setUserState('com_sellacious.order.view.authorised', array_unique($pks));
		}

		if (!in_array($orderId, $pks) && ($me->guest || $me->id != $this->item->get('customer_uid')))
		{
			$allowed = false;
		}

		$isPaid = $this->helper->order->isPaid($orderId);

		if (!$allowed)
		{
			$this->setLayout('password');
		}
		elseif (!$isPaid)
		{
			$this->setLayout('payment');
		}
		elseif ($this->_layout == 'payment')
		{
			$this->setLayout('default');
		}

		$doc = JFactory::getDocument();
		$doc->setTitle('Order - ' . $this->item->get('order_number'));

		return parent::display($tpl);
	}
}
