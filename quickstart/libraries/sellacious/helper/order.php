<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Transaction\TransactionBatch;
use Sellacious\Transaction\TransactionRecord;

/**
 * Sellacious order helper.
 *
 * @since  1.0.0
 */
class SellaciousHelperOrder extends SellaciousHelperBase
{
	/**
	 * Short hand method to set order/item status
	 *
	 * @param   string  $context
	 * @param   string  $statusType
	 * @param   int     $order_id
	 * @param   string  $item_uid
	 * @param   bool    $recursive
	 * @param   bool    $core
	 * @param   string  $notes
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @see     setStatus()
	 *
	 * @since   1.0.0
	 */
	public function setStatusByType($context, $statusType, $order_id, $item_uid, $recursive = true, $core = false, $notes = '')
	{
		$statusId = $this->getStatusId($statusType, $core, $context);

		if ($statusId)
		{
			// ['order_id', 'item_uid', 'status', 'notes', 'shipment', 'params']
			$arr = array(
				'order_id' => $order_id,
				'item_uid' => $item_uid,
				'status'   => $statusId,
				'notes'    => $notes,
			);

			return $this->setStatus($arr, $recursive);
		}

		return false;
	}

	/**
	 * Update order status
	 *
	 * @param   array  $record     Array('order_id', 'item_uid', 'status', 'notes', 'customer_notes', 'shipment', 'params')
	 * @param   bool   $recursive  Whether to update the Order status as well if the items are all in same status after this change
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setStatus(array $record, $recursive = true)
	{
		$order_id = ArrayHelper::getValue($record, 'order_id');
		$item_uid = ArrayHelper::getValue($record, 'item_uid');

		$old = $this->getStatus($order_id, $item_uid);

		// If not setting a new status value and we have a previous one, retain it here.
		if (empty($record['status']))
		{
			if (!$old->status)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ORDER_STATUS_INVALID'));
			}

			$record['status'] = $old->status;
		}

		$status_ids = $this->getStatuses(null, $old->status, true);

		if ($record['status'] == $old->status)
		{
			// Carry over old status properties but only if status is unchanged.
			// @20151121: No need to carry over attributes
		}
		elseif (!in_array($record['status'], $status_ids))
		{
			// New status must be the old one or any of the next allowed statuses.
			throw new Exception(JText::sprintf('COM_SELLACIOUS_ORDER_STATUS_CHANGE_SWITCH_DISALLOWED_TO_FROM', $record['status'], $old->status));
		}

		// Do not overwrite ever!
		$table = $this->getTable('OrderStatus');
		$table->bind($record);
		$table->set('id', 0);
		$table->check();
		$saved = $table->store();

		if ($saved)
		{
			// Disable old status entries, and enable latest one
			$query = $this->db->getQuery(true);

			$query->update($table->getTableName())
				->set('state = (id = ' . (int) $table->get('id') . ')')
				->where('order_id = ' . (int) $order_id)
				->where('item_uid = ' . $this->db->q($item_uid));

			$this->db->setQuery($query)->execute();
		}
		else
		{
			return false;
		}

		$new = $this->getStatus($order_id, $item_uid);

		// Nothing has actually changed after all, so just exit.
		if ($new->status == $old->status)
		{
			return true;
		}

		// If this is a whole order status change / and has changed indeed, we need to update items' statuses if required so.
		if ($item_uid)
		{
			// We must do the stock handling if required.
			$this->handleStock($order_id, $item_uid);
		}

		// Early exit if nothing more to do
		if ($recursive)
		{
			if ($item_uid)
			{
				// Sync with order also if all items are in same status type
				$ois_filter = array(
					'list.select' => 'DISTINCT s.type',
					'list.from'   => '#__sellacious_order_status',
					'order_id'    => $order_id,
					'state'       => 1,
					'list.where'  => 'a.item_uid != ' . $this->db->q(''),
					'list.join'   => array(array('left', '#__sellacious_statuses s ON s.id = a.status')),
				);
				$ois_types  = $this->loadColumn($ois_filter);

				// Items are in same status type,
				if (count($ois_types) == 1)
				{
					// Set status for order
					$this->setStatusByType('order', $ois_types[0], $order_id, '', false, null);
				}
			}
			else
			{
				// If we are on recursive mode, set all items status to match orders status type.
				$o_items  = $this->getOrderItems($order_id);
				$io_types = array('physical', 'electronic', 'package');

				foreach ($o_items as $oi)
				{
					if (in_array($oi->product_type, $io_types))
					{
						$this->setStatusByType('order.' . $oi->product_type, $new->s_type, $oi->order_id, $oi->item_uid, false, null);
					}
				}
			}
		}

		// Handle e-product approved payment to delivered jump
		if ($new->s_type == 'approved')
		{
			if ($new->s_context == 'order')
			{
				// Execute all transactions
				$this->helper->order->executeTransactions($order_id);
			}

			if ($new->s_context == 'order.electronic')
			{
				$this->deliverEProduct($order_id, $item_uid);
			}
		}

		return true;
	}

	/**
	 * Get the current order status
	 *
	 * @param   int     $order_id  Order Id
	 * @param   string  $item_uid  Item UID within order
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getStatus($order_id, $item_uid = null)
	{
		$query = $this->getStatusQuery($order_id, $item_uid);

		try
		{
			$this->db->setQuery($query, 0, 1);

			$status = $this->db->loadObject();

			if ($status)
			{
				/** @var  \SellaciousTableOrderStatus  $table */
				$table = $this->getTable('OrderStatus');
				$table->parseJson($status);
			}
		}
		catch (Exception $e)
		{
			$status = null;

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		if (!$status)
		{
			// Set default empty values
			$status = new stdClass;
			$props  = array('id', 'order_id', 'item_uid', 'status', 'notes', 'customer_notes', 'shipment', 'created',
				's_id', 's_title', 's_type', 's_context', 's_notes_required', 's_allow_change_to', 's_alert', 's_stock');

			foreach ($props as $prop)
			{
				$status->$prop = null;
			}
		}

		return $status;
	}

	/**
	 * Get a sql query for the order / order-item status
	 *
	 * @param   int     $order_id
	 * @param   string  $item_uid
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.2.0
	 */
	protected function getStatusQuery($order_id, $item_uid = null)
	{
		$query = $this->db->getQuery(true);

		$query->select('a.*')
			->from($this->db->qn('#__sellacious_order_status', 'a'))
			->where('a.order_id = ' . (int) $order_id)
			->order('a.item_uid ASC, a.created DESC, a.id DESC');

		// We accept blank or zero $item_uid, so that order only status can be queried
		if (isset($item_uid))
		{
			$query->where('a.item_uid = ' . $this->db->q($item_uid));
		}

		$f_names = array('s.id', 's.title', 's.type', 's.context', 's.notes_required', 's.allow_change_to', 's.alert', 's.stock');
		$f_alias = array('s_id', 's_title', 's_type', 's_context', 's_notes_required', 's_allow_change_to', 's_alert', 's_stock');

		// $f_names = array('s.id', 's.title', 's.type');
		// $f_alias = array('s_id', 's_title', 's_type');

		$query->select($this->db->qn($f_names, $f_alias))
			->join('LEFT', $this->db->qn('#__sellacious_statuses', 's') . ' ON a.status = s.id');

		$query->select($this->db->qn('product_title', 'product_title'))
			->join('LEFT', $this->db->qn('#__sellacious_order_items', 'oi') . ' ON oi.item_uid = a.item_uid AND oi.order_id = a.order_id');

		return $query;
	}

	/**
	 * Get a list of valid status types for orders.
	 * We identify custom statuses with these types for internal logic.
	 *
	 * @param   bool  $assoc  Whether to return an associative array of key and label (true, default) or just the keys
	 *
	 * @return  array
	 *
	 * @since   1.2.0
	 */
	public function getStatusTypes($assoc = true)
	{
		$types = array(
			'general'          => 'COM_SELLACIOUS_STATUS_TYPE_GENERAL',
			'order_placed'     => 'COM_SELLACIOUS_STATUS_TYPE_ORDER_PLACED',
			'authorized'       => 'COM_SELLACIOUS_STATUS_TYPE_AUTHORIZED',
			'paid'             => 'COM_SELLACIOUS_STATUS_TYPE_PAID',
			'payment_failed'   => 'COM_SELLACIOUS_STATUS_TYPE_PAYMENT_FAILED',
			'approved'         => 'COM_SELLACIOUS_STATUS_TYPE_APPROVED',
			'completed'        => 'COM_SELLACIOUS_STATUS_TYPE_COMPLETED',
			'cancellation'     => 'COM_SELLACIOUS_STATUS_TYPE_CANCELLATION',
			'undelivered'      => 'COM_SELLACIOUS_STATUS_TYPE_UNDELIVERED',
			'delivered'        => 'COM_SELLACIOUS_STATUS_TYPE_DELIVERED',
			'packaged'         => 'COM_SELLACIOUS_STATUS_TYPE_PACKAGED',
			'waiting_pickup'   => 'COM_SELLACIOUS_STATUS_TYPE_WAITING_PICKUP',
			'shipped'          => 'COM_SELLACIOUS_STATUS_TYPE_SHIPPED',
		);

		$allow_return   = $this->helper->config->get('purchase_return', 0);
		$allow_exchange = $this->helper->config->get('purchase_exchange', 0);

		if ($allow_return > 0)
		{
			$types['return_placed']    = 'COM_SELLACIOUS_STATUS_TYPE_RETURN_PLACED';
			$types['return_cancelled'] = 'COM_SELLACIOUS_STATUS_TYPE_RETURN_CANCELLED';
			$types['returned']         = 'COM_SELLACIOUS_STATUS_TYPE_RETURNED';
			$types['refund_placed']    = 'COM_SELLACIOUS_STATUS_TYPE_REFUND_PLACED';
			$types['refund_cancelled'] = 'COM_SELLACIOUS_STATUS_TYPE_REFUND_CANCELLED';
			$types['refunded']         = 'COM_SELLACIOUS_STATUS_TYPE_REFUNDED';
		}

		if ($allow_exchange > 0)
		{
			$types['exchange_placed']    = 'COM_SELLACIOUS_STATUS_TYPE_EXCHANGE_PLACED';
			$types['exchange_cancelled'] = 'COM_SELLACIOUS_STATUS_TYPE_EXCHANGE_CANCELLED';
			$types['exchanged']          = 'COM_SELLACIOUS_STATUS_TYPE_EXCHANGED';
		}

		return $assoc ? $types : array_keys($types);
	}

	/**
	 * Return list of valid statuses
	 *
	 * @param   string  $context  The context for which to load the statuses
	 * @param   int     $old      Old status in case we are about to update the status,
	 *                            we need to know old value to get allowable change
	 * @param   bool    $id_only  Whether to return only status ids
	 * @param   bool    $access   Whether to return only allowed by the individual set access level
	 *
	 * @return  stdClass[]|int[]
	 *
	 * @since   1.0.0
	 */
	public function getStatuses($context, $old = null, $id_only = false, $access = false)
	{
		$filters = array();

		// Todo: Check config and load return/exchange when applicable or any statuses only when allowed to
		try
		{
			if ($id_only)
			{
				$filters['list.select'] = 'a.id';
			}

			$filters['list.from'] = $this->getTable('Status')->getTableName();

			if ($context)
			{
				$filters['context'] = $context;
			}

			$filters['type'] = $this->getStatusTypes(false);

			if ($old)
			{
				$sub       = array();
				$sub['id'] = (int) $old;

				if ($context)
				{
					$sub['context'] = $context;
				}

				$status = $this->getTable('Status');
				$status->load($sub);

				if ($status->get('id'))
				{
					$change        = $status->get('allow_change_to');
					$filters['id'] = empty($change) ? 0 : (array) $change;
				}
			}

			$statuses = $id_only ? $this->loadColumn($filters) : $this->loadObjectList($filters);
		}
		catch (Exception $e)
		{
			$statuses = array();

			JLog::add($e->getMessage(), JLog::WARNING);
		}

		return $statuses;
	}

	/**
	 * Stock handling for the items in the order when a status change is triggered.
	 *
	 * @param   int  $order_id  The concerned order id
	 * @param   int  $item_uid  The uid of the order item (PnVnSn)
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function handleStock($order_id, $item_uid)
	{
		$history = $this->getStatusLog($order_id, $item_uid);

		if (empty($history))
		{
			return true;
		}

		$recent = array_shift($history);

		// If this status has no effect on stock, exit.
		if ($recent->s_stock == '')
		{
			return true;
		}

		while ($previous = array_shift($history))
		{
			if (in_array($previous->s_stock, array('A', 'R', 'O')))
			{
				break;
			}
		}

		$c_handle = $recent->s_stock;
		$o_handle = $previous ? $previous->s_stock : '';
		$o_handle = $o_handle ? $o_handle : '-';
		$h_key    = $o_handle . $c_handle;

		// Key => [A=Stock, R=Reserved, O=Sold]
		$handle_map = array(
		//  '-A' => array( '0',  '0',  '0'),
			'-R' => array('-1', '+1',  '0'),
			'-O' => array('-1',  '0', '+1'),
			'AR' => array('-1', '+1',  '0'),
			'AO' => array('-1',  '0', '+1'),
			'RA' => array('+1', '-1',  '0'),
			'RO' => array( '0', '-1', '+1'),
			'OA' => array('+1',  '0', '-1'),
			'OR' => array( '0', '+1', '-1'),
		);

		$change = ArrayHelper::getValue($handle_map, $h_key);

		if (empty($change))
		{
			return true;
		}

		$filters = array(
			'list.select' => 'a.product_id, a.variant_id, a.seller_uid, a.quantity, a.product_type',
			'list.from'   => '#__sellacious_order_items',
			'list.where'  => array(
				'a.order_id = ' . (int) $order_id,
				'a.item_uid = ' . $this->db->q($item_uid),
			),
		);

		$oi = $this->loadObject($filters);

		// Pick appropriate table based on the variant id
		if ($oi->variant_id)
		{
			$table = $this->getTable('VariantSeller');
			$table->load(array('variant_id' => $oi->variant_id, 'seller_uid' => $oi->seller_uid));
		}
		else
		{
			$table = $this->getTable('ProductSeller');
			$table->load(array('product_id' => $oi->product_id, 'seller_uid' => $oi->seller_uid));
		}

		if ($table->get('id'))
		{
			$c_stock = $table->get('stock');
			$r_stock = $table->get('stock_reserved');
			$o_stock = $table->get('stock_sold');

			$c_stock += $change[0] * $oi->quantity;
			$r_stock += $change[1] * $oi->quantity;
			$o_stock += $change[2] * $oi->quantity;

			$table->set('stock', $c_stock);
			$table->set('stock_reserved', $r_stock);
			$table->set('stock_sold', $o_stock);

			$table->store();
		}

		return true;
	}

	/**
	 * Get the list of items in a given order. Includes any child products as in packages.
	 *
	 * @param   int       $orderId  The concerned Order id
	 * @param   string    $columns  The table columns to load for order items
	 * @param   string[]  $where    The additional filter condition, usually a seller filter may be used.
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.0.0
	 */
	public function getOrderItems($orderId, $columns = null, array $where = array())
	{
		/** @var  SellaciousTableOrderItem  $table */
		$table   = $this->getTable('OrderItem');
		$filters = array('list.from' => '#__sellacious_order_items', 'order_id'  => (int) $orderId);

		if ($columns)
		{
			$filters['list.select'] = $columns;
		}

		if ($where)
		{
			$filters['list.where'] = $where;
		}

		$items = (array) $this->loadObjectList($filters);

		foreach ($items as $item)
		{
			$table->parseJson($item);

			$item->package_items = null;

			// The field 'product_type' may have been omitted using $columns parameter in the method call.
			if (isset($item->product_type) && $item->product_type == 'package')
			{
				$filters = array(
					'list.select' => 'a.*',
					'list.from'   => '#__sellacious_order_package_items',
					'list.where'  => 'a.order_item_id = ' . (int) $item->id,
				);

				$item->package_items = (array) $this->loadObjectList($filters);
			}
		}

		return $items;
	}

	/**
	 * Get status edit/update form's JForm object for relevant context
	 *
	 * @param   int       $status_id  The selected status for which to load the form
	 * @param   stdClass  $data       The data to bind to the form. If a data is already present it will be set as readonly
	 *
	 * @return  JForm
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getStatusForm($status_id = 0, $data = null)
	{
		/** @var  JForm  $form */
		$form = JForm::getInstance('com_sellacious.order_status', 'orderstatus/form', array('control' => 'jform'));

		if (!$form)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ORDERSTATUS_FORM_INVALID'));
		}

		$note_req = 0;
		$alert    = '';

		if ($status_id > 0)
		{
			$sTable = $this->getTable('Status');
			$sTable->load($status_id);

			if ($type = $sTable->get('type'))
			{
				$form->loadFile('orderstatus/' . $type);
			}

			$note_req = $sTable->get('notes_required');
			$alert    = $sTable->get('alert');
		}

		if ($note_req)
		{
			$form->setFieldAttribute('notes', 'required', 'true');
		}

		if ($alert)
		{
			$form->setFieldAttribute('alert', 'label', $alert);
		}
		else
		{
			$form->removeField('alert');
		}

		// Bind data
		$form->bind($data);

		// Now set readonly and disabled
		$fieldset = $form->getFieldset();

		foreach ($fieldset as $field)
		{
			if ($field->value)
			{
				$form->setFieldAttribute($field->fieldname, 'readonly', 'true', $field->group);
				$form->setFieldAttribute($field->fieldname, 'disabled', 'true', $field->group);
			}
		}

		return $form;
	}

	/**
	 * Get the status history of given order or order item
	 *
	 * @param   int     $order_id  Order Id
	 * @param   string  $item_uid  Item UID within order
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getStatusLog($order_id, $item_uid = null)
	{
		$db    = $this->db;
		$query = $this->getStatusQuery($order_id, $item_uid);

		try
		{
			$db->setQuery($query);

			$statuses = $db->loadObjectList();

			if ($statuses)
			{
				$table = $this->getTable('OrderStatus');

				array_walk($statuses, array($table, 'parseJson'));
			}
		}
		catch (Exception $e)
		{
			$statuses = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $statuses;
	}

	/**
	 * Get a order status id by type and core flag
	 *
	 * @param   string  $type     The status type value
	 * @param   bool    $core     Whether to load a core status
	 * @param   string  $context  The context for which status is queried
	 *
	 * @return  int  First matching status Id
	 *
	 * @since   1.1.0
	 */
	public function getStatusId($type, $core = false, $context = 'order')
	{
		$table  = $this->getTable('Status');
		$filter = array('type' => $type, 'context' => $context);

		// Null means pick any
		if (isset($core))
		{
			$filter['is_core'] = (int) $core;
		}

		$table->load($filter);

		return $table->get('id');
	}

	/**
	 * Add transaction records (in pending state) when a order has been placed but not paid/processed for payment.
	 *
	 * @param   int  $orderId  Order Id
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function createTransactions($orderId)
	{
		$values    = array();
		$order     = $this->getItem($orderId);
		$shoprules = $order->shoprules;
		$items     = $this->getOrderItems($orderId);

		// Shoprule for cart
		foreach ($shoprules as $shoprule)
		{
			$values[] = $this->shopruleTransactions($shoprule, $order);
		}

		// Shoprule for cart items
		foreach ($items as $item)
		{
			$shoprules = new Registry($item->shoprules);

			foreach ($shoprules as $shoprule)
			{
				$values[] = $this->shopruleTransactions($shoprule, $order, $item);
			}
		}

		foreach ($items as $item)
		{
			// Now Pay the seller his money for the item sold
			$values[] = $this->sellerEarningTransaction($order, $item);
		}

		if ($this->helper->config->get('multi_seller'))
		{
			foreach ($items as $item)
			{
				// Now charge the commission from seller for the item sold
				$this->sellerCommissionTransaction($order, $item, $values);
			}
		}

		// Credit the shipping amount to sellers/shop if applicable
		$filter    = array(
			'list.from' => '#__sellacious_order_shiprates',
			'order_id'  => (int) $orderId,
		);
		$shipRates = $this->loadObjectList($filter);

		foreach ($shipRates as $shipRate)
		{
			$values[] = $this->sellerShipmentTransactions($order, $shipRate);
		}

		// Debit the coupon amount from shop/seller as applicable
		if ($coupon = $this->getCoupon($orderId))
		{
			$values[] = $this->couponTransaction($order, $coupon);
		}

		// Remove empty values
		$transactions = array_filter($values, 'is_array');

		return $transactions;
	}

	/**
	 * Add transaction records when a order has been paid/processed for payment.
	 *
	 * @param   int  $orderId  Order Id
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function executeTransactions($orderId)
	{
		$entries = $this->createTransactions($orderId);
		$fKeys   = array(
			'order_id'   => null,
			'context'    => null,
			'context_id' => null,
			'reason'     => null,
			'crdr'       => null,
			'amount'     => null,
			'currency'   => null,
		);

		$batch = new TransactionBatch;

		foreach ($entries as $entry)
		{
			$filter = array_intersect_key((array) $entry, $fKeys);
			$exists = $this->helper->transaction->loadResult(array_merge($filter, array('list.select' => 'a.id')));

			if (!$exists)
			{
				/** @var  TransactionRecord  $record */
				$record = ArrayHelper::toObject((array) $entry, 'Sellacious\Transaction\TransactionRecord');

				$batch->add($record);
			}
		}

		$batch->execute();

		$query = $this->db->getQuery(true);
		$query->update('#__sellacious_transactions')
			->set('state = 1')
			->where('order_id = ' . (int) $orderId)
			->where('reason LIKE ' . $this->db->q('order.%', false));

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Rebuild Order numbers for the existing orders in database
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function rebuildOrderNumber()
	{
		jexit(__METHOD__ . ' used. Are you sure?');

		$filters  = array('list.select' => 'a.id, a.customer_uid, a.cart_hash, a.created', 'list.order' => 'a.id');
		$iterator = $this->getIterator($filters);

		foreach ($iterator as $record)
		{
			$pattern = strtoupper($this->helper->config->get('order_number_pattern', 'SO{USERID}{HASH}{YY}{M}{D}{OID}'));
			$oShift  = (int) $this->helper->config->get('order_number_shift', 0);
			$oPad    = (int) $this->helper->config->get('order_number_pad', 4);
			$oPad    = $oPad >= 1 ? $oPad : 1;
			$userId  = $record->customer_uid;
			$hash    = substr($record->cart_hash, 0, 5);
			$date    = JFactory::getDate($record->created);

			// {OID} is a mandatory parameter
			if (strpos($pattern, '{OID}') === false)
			{
				$pattern .= '{OID}';
			}

			// Y:{16-99} M:{1-C} D:{1-V}
			$replace = array(
				'{USERID}' => $userId,
				'{HASH}'   => $hash,
				'{YYYY}'   => $date->format('Y'),
				'{MM}'     => $date->format('m'),
				'{DD}'     => $date->format('d'),
				'{YY}'     => $date->format('y'),
				'{M}'      => base_convert($date->month, 10, 16),
				'{D}'      => base_convert($date->day, 10, 36),
				'{OID}'    => str_pad($record->id + $oShift, $oPad, '0', STR_PAD_LEFT),
			);

			$value = strtoupper(str_replace(array_keys($replace), array_values($replace), $pattern));

			$table = $this->getTable();
			$table->load($record->id);
			$table->set('order_number', $value);
			$table->store();
		}
	}

	/**
	 * Get a list of sellers for the given order
	 *
	 * @param   int  $order_id
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.0.0
	 */
	public function getSellers($order_id)
	{
		$filters = array(
			'list.select' => 'a.seller_uid, a.seller_name, a.seller_company, a.seller_email',
			'list.from'   => '#__sellacious_order_items',
			'list.where'  => 'a.order_id = ' . (int) $order_id,
			'list.group'  => 'a.seller_uid',
		);
		$items   = $this->loadObjectList($filters);

		return $items;
	}

	/**
	 * Get the coupon which was used with this order
	 *
	 * @param   int  $order_id
	 *
	 * @return  bool|stdClass
	 *
	 * @since   1.0.0
	 */
	public function getCoupon($order_id)
	{
		$filter = array(
			'list.select' => 'a.*, c.seller_uid',
			'list.from' => '#__sellacious_coupon_usage',
			'list.join' => array(array('left', '#__sellacious_coupons c ON c.id = a.coupon_id')),
			'order_id'  => $order_id
		);
		$value  = $this->loadObject($filter);

		return is_object($value) ? $value : false;
	}

	/**
	 * Get the total number of orders placed for a given item/variant/seller
	 *
	 * @param   int   $product_id
	 * @param   int   $variant_id
	 * @param   int   $seller_uid
	 * @param   bool  $quantity
	 *
	 * @return  int
	 *
	 * @since   1.0.0
	 */
	public function getOrderCount($product_id, $variant_id, $seller_uid, $quantity = false)
	{
		$query = $this->db->getQuery(true);

		$query->select($quantity ? 'SUM(a.quantity)' : 'COUNT(DISTINCT a.order_id)')
			->from($this->db->qn('#__sellacious_order_items', 'a'));

		if (isset($product_id))
		{
			$query->where('a.product_id = ' . $product_id);
		}

		if (isset($variant_id))
		{
			$query->where('a.variant_id = ' . $variant_id);
		}

		if (isset($seller_uid))
		{
			$query->where('a.seller_uid = ' . $seller_uid);
		}

		try
		{
			$count = $this->db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			$count = 0;
		}

		return $count;
	}

	/**
	 * Load an bill-to address from database
	 *
	 * @param   int   $id   Order Id
	 * @param   bool  $raw  Whether to get the address in raw form, i.e. without expanding the titles from location id
	 *
	 * @return  Registry
	 *
	 * @since   1.0.0
	 */
	public function getBillToAddress($id, $raw = false)
	{
		$filters = array(
			'id'          => $id,
			'list.select' => 'a.customer_uid, a.bt_address, a.customer_email, a.bt_mobile, a.bt_name, a.bt_country, a.bt_state, a.bt_district, a.bt_landmark, a.bt_zip',
		);

		$order = $this->helper->order->loadObject($filters);
		$order = new Registry($order);

		if (!$raw)
		{
			// Todo: Return a SellaciousAddress object
		}

		return $order;
	}

	/**
	 * Load an ship-to address from database
	 *
	 * @param   int   $id   Order Id
	 * @param   bool  $raw  Whether to get the address in raw form, i.e. without expanding the titles from location id
	 *
	 * @return  Registry
	 *
	 * @since   1.0.0
	 */
	public function getShipToAddress($id, $raw = false)
	{
		$filters = array(
			'list.select' => array(
				'a.customer_uid, a.st_address, a.customer_email, a.st_mobile',
				'a.st_name, a.st_country, a.st_state, a.st_district, a.st_landmark, a.st_zip',
			),
			'id'          => $id,
		);

		$order = $this->helper->order->loadObject($filters);
		$order = new Registry($order);

		if (!$raw)
		{
			// Todo: Return a SellaciousAddress object
		}

		return $order;
	}

	/**
	 * Record the purchased e-product media in the downloads/delivery table
	 *
	 * @param   int     $order_id
	 * @param   string  $item_uid
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function deliverEProduct($order_id, $item_uid)
	{
		// See if already delivered
		$dFilter  = array('list.from' => '#__sellacious_eproduct_delivery', 'order_id' => $order_id, 'item_uid' => $item_uid);
		$iFilter  = array('list.from' => '#__sellacious_order_items', 'order_id' => $order_id, 'item_uid' => $item_uid);
		$oFilter  = array('id' => $order_id);
		$inserted = $this->count($dFilter);
		$oItem    = $this->loadObject($iFilter);
		$order    = $this->loadObject($oFilter);

		if (isset($order, $oItem, $oItem->quantity) && $inserted < $oItem->quantity)
		{
			// Insert remaining units only
			$quantity     = $oItem->quantity - $inserted;
			$productTitle = trim(sprintf('%s %s', $oItem->product_title, $oItem->variant_title));

			$delivered = $this->deliverEproductItem($order_id, $order->customer_uid, $item_uid, $productTitle, $quantity);

			// Set next status as delivered for this item
			if ($delivered)
			{
				return $this->setStatusByType('order.electronic', 'delivered', $order_id, $item_uid, true, true);
			}
		}

		return false;
	}

	/**
	 * Validate whether the selected file can be allowed for download or not based on the relevant e-product purchase
	 *
	 * @param   int  $delivery_id
	 * @param   int  $file_id
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function checkEProductDelivery($delivery_id, $file_id = null)
	{
		$me    = JFactory::getUser();
		$query = $this->db->getQuery(true);

		$query->select($this->db->qn(array('a.id', 'a.order_id', 'a.item_uid', 'a.mode', 'a.download_limit', 'a.expiry', 'a.state')))
			->from($this->db->qn('#__sellacious_eproduct_delivery', 'a'))
			->where('a.id = ' . (int) $delivery_id)
			->where('a.user_id = ' . (int) $me->id);

		$query->select($this->db->qn(array('oi.product_id', 'oi.variant_id', 'oi.seller_uid', 'oi.quantity')))
			->join('inner', $this->db->qn('#__sellacious_order_items', 'oi') . ' ON oi.order_id = a.order_id AND oi.item_uid = a.item_uid');

		$delivery = $this->db->setQuery($query)->loadObject();

		if (!$delivery)
		{
			// No such purchase.
			throw new Exception(JText::_('COM_SELLACIOUS_ORDER_EPRODUCT_DELIVERY_NOT_FOUND'));
		}

		$expiry = JFactory::getDate($delivery->expiry)->toUnix();

		if ($delivery->state != 1 || $expiry < JFactory::getDate()->toUnix())
		{
			// Expired.
			throw new Exception(JText::_('COM_SELLACIOUS_ORDER_EPRODUCT_DELIVERY_EXPIRED'));
		}

		if ($delivery->mode != 'download')
		{
			// Download mode disabled.
			throw new Exception(JText::_('COM_SELLACIOUS_ORDER_EPRODUCT_DELIVERY_NOT_DOWNLOAD'));
		}

		$filter = array(
			'list.select' => 'a.id',
			'list.from'   => '#__sellacious_eproduct_media',
			'product_id'  => $delivery->product_id,
			'variant_id'  => $delivery->variant_id,
			'seller_uid'  => $delivery->seller_uid,
			'state'       => 1,
		);

		$medias = $this->loadColumn($filter);

		// If no media in variant, inherit from main product
		if (!$medias && $delivery->variant_id > 0)
		{
			$filter = array(
				'list.select' => 'a.id',
				'list.from'   => '#__sellacious_eproduct_media',
				'product_id'  => $delivery->product_id,
				'variant_id'  => 0,
				'seller_uid'  => $delivery->seller_uid,
				'state'       => 1,
			);

			$medias = $this->loadColumn($filter);
		}

		if (!$medias)
		{
			// No media in this purchase
			throw new Exception(JText::_('COM_SELLACIOUS_ORDER_EPRODUCT_DELIVERY_NO_DOWNLOAD_FILES'));
		}

		$filter = array(
			'list.select' => 'a.id',
			'table_name'  => 'eproduct_media',
			'record_id'   => $medias,
			'state'       => 1,
		);

		if ($file_id)
		{
			// Limit search if file already specified
			$filter['id'] = (int) $file_id;
		}

		$file_ids = $this->helper->media->loadColumn($filter);

		// If limit is not set, then allow unlimited
		if ($file_ids && (int) $delivery->download_limit > 0)
		{
			$delivery->files = array();

			foreach ($file_ids as $file_id)
			{
				$dl_count = array(
					'list.select' => 'SUM(a.dl_count) AS download_count',
					'list.from'   => '#__sellacious_eproduct_downloads',
					'delivery_id' => $delivery->id,
					'file_id'     => $file_id,
				);

				$count = $this->loadResult($dl_count);

				if ((int) $count < (int) $delivery->download_limit)
				{
					$delivery->files[] = $file_id;
				}
			}
		}
		else
		{
			$delivery->files = (array) $file_ids;
		}

		return $delivery;
	}

	/**
	 * Update the download log for the selected media
	 *
	 * @param   int       $delivery_id  The e-product purchase delivery record from the eproduct_delivery table
	 * @param   stdClass  $file         The media file record for the file being downloaded from the media table
	 *
	 * @return  string
	 *
	 * @see     checkEProductDelivery()
	 *
	 * @since   1.0.0
	 */
	public function logDownload($delivery_id, $file)
	{
		$me    = JFactory::getUser();
		$now   = JFactory::getDate();
		$hash  = md5(microtime());
		$table = $this->getTable('EProductDownload');

		// Hash to be used in future to allow reattempt a download within a specific time duration from the same ip etc.
		$data  = array(
			'id'          => null,
			'delivery_id' => $delivery_id,
			'user_id'     => $me->id,
			'file_id'     => $file->id,
			'file_name'   => $file->original_name,
			'dl_count'    => 1,
			'dl_date'     => $now->toSql(),
			'ip'          => $this->helper->location->getClientIP(),
			'hash'        => $hash,
		);

		$table->bind($data);
		$table->check();
		$table->store();

		return $table->get('id') ? $hash : false;
	}

	/**
	 * Track an order by source id, transaction id or cart id or a combination
	 *
	 * @param   string  $cartId  The Cart id
	 * @param   string  $txnId   The transaction id
	 * @param   string  $srcId   The source id
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.5
	 */
	public function trackOrder($cartId, $txnId, $srcId)
	{
		$db = JFactory::getDbo();

		$filter = array(
			'list.select' => array(
				'oi.order_id',
				'a.customer_uid',
				'a.customer_name',
				'a.customer_email',
				'a.customer_ip',
				'a.bt_name',
				'a.bt_address',
				'a.bt_district',
				'a.bt_landmark',
				'a.bt_city',
				'a.bt_state',
				'a.bt_zip',
				'a.bt_country',
				'a.bt_mobile',
				'a.bt_company',
				'a.bt_po_box',
				'a.bt_residential',
				'a.st_name',
				'a.st_address',
				'a.st_district',
				'a.st_landmark',
				'a.st_city',
				'a.st_state',
				'a.st_zip',
				'a.st_country',
				'a.st_mobile',
				'a.st_company',
				'a.st_po_box',
				'a.st_residential',
				'a.currency',
				'a.shipping_rule AS order_shipping_rule',
				'a.shipping_service AS order_shipping_service',
				'oi.item_uid',
				'oi.product_title',
				'oi.product_type',
				'oi.local_sku',
				'oi.manufacturer_sku',
				'oi.manufacturer_title',
				'oi.seller_email',
				'oi.seller_code',
				'oi.seller_name',
				'oi.seller_company',
				'oi.cost_price',
				'oi.price_margin',
				'oi.price_perc_margin',
				'oi.list_price',
				'oi.calculated_price',
				'oi.override_price',
				'oi.sales_price',
				'oi.basic_price',
				'oi.discount_amount',
				'oi.tax_amount',
				'oi.shipping_rule',
				'oi.shipping_service',
				'oi.shipping_amount',
				'oi.quantity',
				'oi.sub_total',
				'oi.cart_id',
				'oi.transaction_id',
				'oi.source_id',
				'oi.created',
			),
			'list.join' => array(array('left', '#__sellacious_order_items oi ON oi.order_id = a.id')),
		);

		if ($cartId)
		{
			$filter['list.where'][] = 'oi.cart_id = ' . $db->q($cartId);
		}

		if ($txnId)
		{
			$filter['list.where'][] = 'oi.transaction_id = ' . $db->q($txnId);
		}

		if ($srcId)
		{
			$filter['list.where'][] = 'oi.source_id = ' . $db->q($srcId);
		}

		$items = $this->helper->order->loadObjectList($filter);

		foreach ($items as $item)
		{
			$status = $this->getStatus($item->order_id, $item->item_uid);

			$item->order_status  = $status->s_title;
			$item->order_updated = $status->created;
		}

		return $items;
	}

	/**
	 * Check whether the selected order has payment completed
	 *
	 * @param   int  $order_id  The order id to check for
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.4
	 */
	public function isPaid($order_id)
	{
		$keys = array(
			'context'    => 'order',
			'order_id'   => $order_id,
			'list.where' => 'a.state > 0',
		);

		return $this->helper->payment->count($keys) > 0;
	}

	/**
	 * Check whether the selected order has shipping address
	 *
	 * @param   int  $order_id  The order id to check for
	 *
	 * @return  bool
	 *
	 * @since   1.5.3
	 */
	public function hasShippingAddress($order_id)
	{
		$filter = array(
			'id'          => $order_id,
			'list.select' => array(
				'a.st_name',
				'a.st_address',
				'a.st_district',
				'a.st_landmark',
				'a.st_city',
				'a.st_state',
				'a.st_zip',
				'a.st_country',
				'a.st_mobile',
			),
		);

		$order = $this->loadRow($filter);
		$address  = implode(array_map('trim', $order));

		return $address != '';
	}

	/**
	 * Calculate the effective seller commission for the selected order item (seller > seller_category > global)
	 *
	 * @param   stdClass  $item     The order item to check for
	 * @param   int       $orderId  The order id to check for
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	protected function getSellerCommission($item, $orderId)
	{
		$productId  = $item->product_id;
		$sellerUid  = $item->seller_uid;
		$basicPrice = $item->basic_price;

		// We'll use inheritance if a category is not mapped
		$pCatLevels = array();
		$categories = $this->helper->product->getCategories($productId, false);

		foreach ($categories as $categoryId)
		{
			$pCatLevel    = $this->helper->category->getParents($categoryId, true);
			$pCatLevels[] = array_reverse($pCatLevel);
		}

		// Check seller uid - product category specific
		$commissions = $this->helper->seller->getCommissions($sellerUid);

		list($bestA, $bestR, $bestP) = $this->pickCommission($commissions, $pCatLevels, $basicPrice);

		// We've found a commission value? Return!
		if (isset($bestA))
		{
			return array($bestA, $bestR, $bestP);
		}

		// Now lookup into the seller category
		$categoryS  = $this->helper->seller->loadResult(array('list.select' => 'a.category_id', 'user_id' => $sellerUid));
		$categories = $this->helper->category->getParents($categoryS, true);
		$sCatLevel  = array_reverse($categories);

		// We'll use inheritance if a category is not mapped. Inheritance includes default category here.
		foreach ($sCatLevel as $sCatid)
		{
			$commissions = $this->helper->category->getSellerCommissionsBySellerCategory($sCatid);

			list($bestA, $bestR, $bestP) = $this->pickCommission($commissions, $pCatLevels, $basicPrice);

			// We've found a commission value
			if (isset($bestA))
			{
				return array($bestA, $bestR, $bestP);
			}
		}

		// Calculate commission rate from global default
		$value  = $this->helper->config->get('on_sale_commission', 0);
		$perc   = substr($value, -1) == '%';
		$rate   = $perc ? substr($value, 0, -1) : $value;
		$amount = $perc ? round($basicPrice * $rate / 100.0, 2) : $rate;

		return array($amount, $rate, $perc);
	}

	/**
	 * Pick the best commission rate (minimum) for the seller's sale
	 *
	 * @param   string[]  $commissions     The commissions array from which to pick
	 * @param   int[][]   $categoriesList  The product categories id groups each for the inheritance level for each assigned category
	 * @param   float     $basicPrice      The effective sales price on which to calculate commission
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function pickCommission($commissions, $categoriesList, $basicPrice)
	{
		$bestA = null;
		$bestR = null;
		$bestP = null;

		// Iterate over each assigned category
		foreach ($categoriesList as $categories)
		{
			// Iterate for each parent upward for the assigned category in this iteration
			foreach ($categories as $categoryId)
			{
				if (isset($commissions[$categoryId]))
				{
					$value  = $commissions[$categoryId];
					$perc   = substr($value, -1) == '%';
					$rate   = $perc ? substr($value, 0, -1) : $value;
					$amount = $perc ? round($basicPrice * $rate / 100.0, 2) : $rate;

					if (!isset($bestA) || $amount < $bestA)
					{
						$bestA = $amount;
						$bestR = $rate;
						$bestP = $perc;
					}

					// No more inherit as we have found a value
					break;
				}
			}
		}

		return array($bestA, $bestR, $bestP);
	}

	/**
	 * Delivery of an e-product item as an individual
	 *
	 * @param   int     $orderId        The order id if available
	 * @param   string  $userId         The customer user id
	 * @param   string  $itemUid        The product unique code
	 * @param   string  $productTitle   The full product title
	 * @param   int     $quantity       Quantity ordered
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function deliverEproductItem($orderId, $userId, $itemUid, $productTitle, $quantity)
	{
		// Todo: Allow variants to set their own parameters for use here
		$this->helper->product->parseCode($itemUid, $productId, $variantId, $sellerUid);

		$filter    = array(
			'list.from'  => '#__sellacious_eproduct_sellers',
			'list.join'  => array(
				array('left', '#__sellacious_product_sellers AS psx ON psx.id = a.psx_id'),
			),
			'list.where' => array(
				'psx.product_id = ' . (int) $productId,
				'psx.seller_uid = ' . (int) $sellerUid,
			),
		);
		$seller    = $this->loadObject($filter);

		if (!$seller)
		{
			// Panic! Throw exception?
			throw new Exception(JText::_('COM_SELLACIOUS_ORDER_PRODUCT_ELECTRONIC_DELIVER_ERROR_SELLER_MISSING'));
		}

		$period = new Registry($seller->download_period);
		$l      = $period->get('l', 30) ?: 30;
		$p      = $period->get('p', 'day') ?: 'day';
		$expiry = JFactory::getDate()->modify('+' . $l . ' ' . $p)->format('Y-m-d H:i:s');

		$me  = JFactory::getUser();
		$now = JFactory::getDate();

		$delivery = array(
			'id'             => null,
			'order_id'       => $orderId,
			'item_uid'       => $itemUid,
			'user_id'        => $userId,
			'product_name'   => $productTitle,
			'license_id'     => $seller->license,
			'mode'           => $seller->delivery_mode,
			'download_limit' => $seller->download_limit,
			'license_limit'  => $seller->license_count,
			'expiry'         => $expiry,
			'preview_mode'   => $seller->preview_mode,
			'preview_url'    => $seller->preview_url,
			'state'          => 1,
			'created'        => $now->toSql(),
			'created_by'     => $me->id,
		);

		for ($i = 1; $i <= $quantity; $i++)
		{
			$obj = (object) $delivery;

			$this->db->insertObject('#__sellacious_eproduct_delivery', $obj, 'id');
		}

		return true;
	}

	/**
	 * Get e-product order delivery information
	 *
	 * @param   int  $orderId
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.3
	 */
	public function getEproductDelivery($orderId)
	{
		$query = $this->db->getQuery(true);

		$query->select($this->db->qn(array('a.id', 'a.order_id', 'a.item_uid', 'a.mode')))
			->from($this->db->qn('#__sellacious_eproduct_delivery', 'a'))
			->where('a.order_id = ' . (int) $orderId)
			->where('a.state = 1');

		$delivery = $this->db->setQuery($query)->loadObjectList();

		return $delivery;
	}

	/**
	 * Get the transaction records to be created based on the shoprules applied to an order
	 *
	 * @param   stdClass  $shoprule  The shoprule object
	 * @param   stdClass  $order     The order object
	 * @param   stdClass  $item      The order item object (optional, if processing cart shoprules)
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function shopruleTransactions($shoprule, $order, $item = null)
	{
		$value = null;

		if (abs($shoprule->change) >= 0.01 && ($shoprule->type == 'tax' || $shoprule->type == 'discount'))
		{
			$tsNow = JFactory::getDate()->toSql();
			$rate  = $shoprule->amount . ($shoprule->percent ? '%' : ' ' . $order->currency);

			if ($item)
			{
				$change = ($shoprule->change * $item->quantity) . ' ' . $order->currency;
				$amount = abs($shoprule->change) * $item->quantity;
				$reason = 'order.item.shoprule.' . $shoprule->type;
				$note   = JText::sprintf('COM_SELLACIOUS_NOTES_SHOPRULE_ITEMS_CHARGES', $shoprule->title, $rate, $change, $order->order_number, $item->item_uid);
			}
			else
			{
				$amount = abs($shoprule->change);
				$change = $shoprule->change . ' ' . $order->currency;
				$reason = 'order.shoprule.' . $shoprule->type;
				$note   = JText::sprintf('COM_SELLACIOUS_NOTES_SHOPRULE_CHARGES', $shoprule->title, $rate, $change, $order->order_number);
			}

			$value  = array(
				'order_id'   => $order->id,
				'user_id'    => $this->helper->transaction->getContextUser('shoprule.id', $shoprule->id),
				'context'    => 'shoprule.id',
				'context_id' => $shoprule->id,
				'reason'     => $reason,
				'crdr'       => $shoprule->type == 'tax' ? 'cr' : 'dr',
				'amount'     => $amount,
				'currency'   => $order->currency,
				'txn_date'   => $tsNow,
				'notes'      => $note,
				'state'      => 0,
			);
		}

		return $value;
	}

	/**
	 * Get the transaction records to be created based on the seller commission applied to an order/item
	 *
	 * @param   stdClass  $order   The order object
	 * @param   stdClass  $item    The order item object (optional, if processing cart shoprules)
	 * @param   array[]   $values  The entries array for the transactions, any entry should be pushed into this array
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function sellerCommissionTransaction($order, $item, &$values)
	{
		list($commAmount, $commRate, $isPercent) = $this->getSellerCommission($item, $order->id);

		if (abs($commAmount) >= 0.01)
		{
			$tsNow  = JFactory::getDate()->toSql();
			$rate   = $isPercent ? $commRate . '%' : $commRate . ' ' . $order->currency;
			$amount = abs($commAmount * $item->quantity);
			$note   = JText::sprintf('COM_SELLACIOUS_NOTES_COMMISSION_ITEMS_CHARGES', $rate, $amount . ' ' . $order->currency, $order->order_number, $item->item_uid);

			$values[] = array(
				'order_id'   => $order->id,
				'user_id'    => 0,
				'context'    => 'user.id',
				'context_id' => 0,
				'reason'     => 'order.item.sales_commission',
				'crdr'       => 'cr',
				'amount'     => $amount,
				'currency'   => $order->currency,
				'txn_date'   => $tsNow,
				'notes'      => $note,
				'state'      => 0,
			);
			$values[] = array(
				'order_id'   => $order->id,
				'user_id'    => $item->seller_uid,
				'context'    => 'user.id',
				'context_id' => $item->seller_uid,
				'reason'     => 'order.item.sales_commission',
				'crdr'       => 'dr',
				'amount'     => $amount,
				'currency'   => $order->currency,
				'txn_date'   => $tsNow,
				'notes'      => $note,
				'state'      => 0,
			);
		}
	}

	/**
	 * Get the transaction records to be created based on the sale made by the seller for an order/item
	 *
	 * @param   stdClass  $order  The order object
	 * @param   stdClass  $item   The order item object (optional, if processing cart shoprules)
	 *
	 * @return array
	 *
	 * @since   1.6.0
	 */
	protected function sellerEarningTransaction($order, $item)
	{
		$basicPrice = $item->basic_price;
		$currency   = $order->currency;
		$orderNum   = $order->order_number;
		$itemUid    = $item->item_uid;

		$tsNow = JFactory::getDate()->toSql();
		$note  = JText::sprintf('COM_SELLACIOUS_NOTES_SOLD_ITEMS_PRICE', $basicPrice, $currency, $orderNum, $itemUid);

		$value = array(
			'order_id'   => $order->id,
			'user_id'    => $item->seller_uid,
			'context'    => 'user.id',
			'context_id' => $item->seller_uid,
			'reason'     => 'order.sale',
			'crdr'       => 'cr',
			'amount'     => abs($basicPrice) * $item->quantity,
			'currency'   => $currency,
			'txn_date'   => $tsNow,
			'notes'      => $note,
			'state'      => 0,
		);

		return $value;
	}

	/**
	 * Get the transaction records to be created based on the shipment of the order for all sellers involved
	 *
	 * @param   stdClass  $order     The order object
	 * @param   stdClass  $shipRate  The shipping amount distribution per seller involved
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	protected function sellerShipmentTransactions($order, $shipRate)
	{
		$tsNow = JFactory::getDate()->toSql();
		$text  = 'COM_SELLACIOUS_NOTES_SHIPRULE_CHARGES' . ($shipRate->item_uid ? '_ITEM' : '');
		$note  = JText::sprintf($text, $shipRate->rule_title, $order->id, $shipRate->item_uid);

		$value = array(
			'order_id'   => $order->id,
			'user_id'    => $shipRate->seller_uid,
			'context'    => 'user.id',
			'context_id' => $shipRate->seller_uid,
			'reason'     => 'order.shippingrule.' . (int) $shipRate->rule_id,
			'crdr'       => 'cr',
			'amount'     => abs($shipRate->amount),
			'currency'   => $order->currency,
			'txn_date'   => $tsNow,
			'notes'      => $note,
			'state'      => 0,
		);

		return $value;
	}

	/**
	 * Get the transaction records to be created based on the coupon used for the order
	 *
	 * @param   stdClass  $order   The order object
	 * @param   stdClass  $coupon  The coupon object
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	protected function couponTransaction($order, $coupon)
	{
		$tsNow = JFactory::getDate()->toSql();
		$value = array(
			'order_id'   => $order->id,
			'user_id'    => $coupon->seller_uid ?: 0,
			'context'    => 'user.id',
			'context_id' => $coupon->seller_uid ?: 0,
			'reason'     => 'order.coupon.' . (int) $coupon->coupon_id,
			'crdr'       => 'dr',
			'amount'     => abs($coupon->amount),
			'currency'   => $order->currency,
			'txn_date'   => $tsNow,
			'notes'      => JText::sprintf('COM_SELLACIOUS_NOTES_ORDER_COUPON_USED', $order->order_number, $coupon->code),
			'state'      => 0,
		);

		return $value;
	}
}
