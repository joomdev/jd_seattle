<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Sellacious model.
 */
class SellaciousModelOrder extends SellaciousModelAdmin
{
	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   12.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		return false;
	}

	/**
	 * Method to return a single record. Joomla model doesn't use caching, we use.
	 *
	 * @param   int  $pk  (optional) The record id of desired item.
	 *
	 * @return  JObject
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);
		$now  = JFactory::getDate();

		if ($item)
		{
			$order_id = $item->get('id');

			$oItems = $this->helper->order->getOrderItems($order_id);

			$return_status   = $this->helper->order->getStatusId('return_placed', true, 'order.physical');
			$exchange_status = $this->helper->order->getStatusId('exchange_placed', true, 'order.physical');

			foreach ($oItems as $oi)
			{
				$oi->return_available   = false;
				$oi->exchange_available = false;

				$i_status = $this->helper->order->getStatus($oi->order_id, $oi->item_uid);

				// Return and exchange is only available after certain status such as 'delivered'. We need to check! Not just last updated!!
				if ($last_updated = $i_status->created)
				{
					$statuses = $this->helper->order->getStatuses('order.' . $oi->product_type, $i_status->status, true);

					if (in_array($return_status, $statuses))
					{
						$o_date               = JFactory::getDate($last_updated);
						$return_date          = $o_date->add(new DateInterval('P' . (int) $oi->return_days . 'D'));
						$oi->return_date      = $return_date->format('Y-m-d H:i:s');
						$oi->return_available = strtotime($return_date) > strtotime($now);
					}

					if (in_array($exchange_status, $statuses))
					{
						$o_date                 = JFactory::getDate($last_updated);
						$exchange_date          = $o_date->add(new DateInterval('P' . (int) $oi->exchange_days . 'D'));
						$oi->exchange_date      = $exchange_date->format('Y-m-d H:i:s');
						$oi->exchange_available = strtotime($exchange_date) > strtotime($now);
					}
				}
			}

			$item->set('items', $oItems);
			$item->set('status', $this->helper->order->getStatus($order_id));
			$item->set('coupon', $this->helper->order->getCoupon($order_id));

			$keys = array(
				'context'    => 'order',
				'order_id'   => $order_id,
				'list.where' => 'a.state > 0',
			);

			$item->set('payment', $this->helper->payment->loadObject($keys));

			$item->set('eproduct_delivery', $this->helper->order->getEproductDelivery($order_id));
		}

		return $item;
	}
}
