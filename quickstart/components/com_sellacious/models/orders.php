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

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of Sellacious records.
 *
 * @since  3.0
 */
class SellaciousModelOrders extends SellaciousModelList
{
	/** @var  array */
	protected $items;

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$me    = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->qn('#__sellacious_orders') . ' AS a')
			->where($db->qn('a.customer_uid') . ' = ' . $db->q($me->get('id')))
			->order('a.created DESC');

		return $query;
	}

	/**
	 * Process list to add items in order
	 *
	 * @param   array  $items
	 *
	 * @return  array
	 */
	protected function processList($items)
	{
		if (is_array($items))
		{
			$now   = JFactory::getDate();
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$oid   = ArrayHelper::getColumn($items, 'id');

			$query->select('a.*')
				->from($db->qn('#__sellacious_order_items', 'a'))
				->where('a.order_id IN ' . '(' . (count($oid) ? implode(', ', $db->q($oid)) : '0') . ')');

			try
			{
				$db->setQuery($query);

				$products = $db->loadObjectList();
				$o_items  = ArrayHelper::pivot($products, 'order_id');

				$return_status   = $this->helper->order->getStatusId('return_placed', true, 'order.physical');
				$exchange_status = $this->helper->order->getStatusId('exchange_placed', true, 'order.physical');

				foreach ($items as &$item)
				{
					$value       = ArrayHelper::getValue($o_items, $item->id, array());
					$item->items = is_object($value) ? array($value) : $value;

					foreach ($item->items as $oi)
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
				}
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}
		}

		return parent::processList($items);
	}
}
