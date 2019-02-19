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
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   1.6.0
	 */
	protected function canDelete($record)
	{
		return $this->helper->access->check('order.delete');
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array  $data      Data for the form.
	 * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
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
	 * Method to return a single record. Joomla model does not use caching, we use.
	 *
	 * @param   int  $pk  (optional) The record id of desired item.
	 *
	 * @return  JObject
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ($item)
		{
			$oid   = $item->get('id');
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.*')
				  ->from($db->qn('#__sellacious_order_items', 'a'))
				  ->where('a.order_id = ' . $db->q($oid));

			try
			{
				$db->setQuery($query);
				$products = $db->loadObjectList();

				/** @var  SellaciousTableOrderItem  $table */
				$table = $this->getTable('OrderItem');

				foreach ($products as $product)
				{
					$table->parseJson($product);
				}

				$item->set('items', $products);
			}
			catch (Exception $e)
			{
				$item->set('items', array());

				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}

			$coupon = $this->helper->order->getCoupon($item->get('id'));
			$item->set('coupon', $coupon);

			$keys = array(
				'context'    => 'order',
				'order_id'   => $oid,
				'list.where' => 'a.state > 0',
			);
			$payment = $this->helper->payment->loadObject($keys);

			$item->set('payment', $payment);
		}

		return $item;
	}

	/**
	 * Get status history of the order/items.
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 */
	public function getHistory()
	{
		$order_id = (int) $this->getState('order.id');
		$log      = $this->helper->order->getStatusLog($order_id);

		// $html = JLayoutHelper::render('com_sellacious/order/item/status_log', $data);
		return $log;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 * @param   integer  $all  Whether to delete all or without transactions.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   1.6.0
	 */
	public function delete(&$pks, $all = 0)
	{
		if ($delete = parent::delete($pks))
		{
			foreach ($pks as $pk)
			{
				$pk    = (int) $pk;

				$tables = array(
					array('#__sellacious_order_items', 'order_id'),
					array('#__sellacious_order_package_items', 'order_id'),
					array('#__sellacious_order_shiprates', 'order_id'),
					array('#__sellacious_order_shipments', 'order_id'),
					array('#__sellacious_order_status', 'order_id'),
				);

				if($all)
				{
					$tables[] = array('#__sellacious_transactions', 'order_id', array('reason LIKE ' . $this->_db->q('order%', false)));
					$tables[] = array('#__sellacious_payments', 'order_id', array('context = ' . $this->_db->q('order')));
				}

				$queries = array();

				$query = $this->_db->getQuery(true);

				foreach ($tables as $table)
				{
					$query->clear()->delete($table[0])->where($table[1] . ' = ' . $this->_db->q($pk));

					if (isset($table[2]))
					{
						$query->where($table[2]);
					}

					$queries[] = (string) $query;
				}

				// Execute all queries
				foreach ($queries as $query)
				{
					try
					{
						$this->_db->setQuery($query)->execute();
					}
					catch (Exception $e)
					{
						// Ignore as of now
					}
				}

			}
		}

		return $delete;
	}
}
