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

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious seller's product listing helper.
 *
 * @since   1.2.0
 */
class SellaciousHelperListing extends SellaciousHelperBase
{
	/**
	 * Generate SQL query from the given filters and other clauses
	 *
	 * @param   array  $filters
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.2.0
	 */
	public function getListQuery($filters)
	{
		$db    = $this->db;
		$query = parent::getListQuery($filters);

		$query->join('INNER', $db->qn('#__users', 'u') . ' ON a.seller_uid = u.id');

		return $query;
	}

	/**
	 * Get the date of expiry of a product listing
	 *
	 * @param   int  $product_id
	 * @param   int  $seller_uid
	 * @param   int  $category_id
	 *
	 * @return  stdClass
	 *
	 * @since   1.2.0
	 */
	public function getActive($product_id, $seller_uid, $category_id)
	{
		$filter = array(
			'product_id'  => $product_id,
			'seller_uid'  => $seller_uid,
			'category_id' => $category_id,
			'state'       => 1,
		);
		$item   = $this->getItem($filter);

		$now = JFactory::getDate();
		$end = JFactory::getDate($item->publish_down);

		// We don't assume it to be active if the time is over, so set state = 0
		if ($item->id == 0 || $end->toUnix() < $now->toUnix())
		{
			$item->state = 0;
		}

		return $item;
	}

	/**
	 * Calculate the fee applicable to the listing selected
	 *
	 * @param   int  $days
	 * @param   int  $cat_id
	 *
	 * @return  stdClass
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function calculateCost($days, $cat_id = 0)
	{
		$listing = new stdClass;

		if ($cat_id > 0)
		{
			$cat = $this->helper->splCategory->getItem($cat_id);

			$listing_fee = $cat->fee_amount;
			$recurrence  = $cat->recurrence;
		}
		else
		{
			$listing_fee = $this->helper->config->get('listing_fee', 0);
			$recurrence  = $this->helper->config->get('listing_fee_recurrence', 0);
		}

		$slots = $recurrence ? ceil($days / $recurrence) : 1;
		$fee   = $slots * $listing_fee;

		$listing->category_id = $cat_id;
		$listing->recurrence  = $recurrence;
		$listing->slot_fee    = $listing_fee;
		$listing->days        = $days;
		$listing->slots       = $slots;
		$listing->fee_total   = $fee;

		return $listing;
	}

	/**
	 * Place a listing order and
	 *
	 * @param   int    $seller_uid
	 * @param   array  $listing_ids
	 * @param   float  $fee_total
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function executeOrder($seller_uid, $listing_ids, $fee_total)
	{
		$app = JFactory::getApplication();
		$now = JFactory::getDate()->toSql();

		$oTable = $this->getTable('ListingOrder');
		$cTable = $this->getTable()->getTableName();

		// Generate an order with PENDING status
		$order = array(
			'customer_id'    => $seller_uid,
			'customer_ip'    => $app->input->server->getString('REMOTE_ADDR'),
			'order_status'   => 'pending',
			'order_datetime' => $now,
			'order_subtotal' => $fee_total,
			'order_total'    => $fee_total,
			'state'          => 0,
			'params'         => array(
				'seller_uid'  => $seller_uid,
				'listing_ids' => $listing_ids,
				'fee_total'   => $fee_total,
			),
		);

		$oTable->save($order);

		// Update order id for new listings
		$order_id = $oTable->get('id');
		$old_ids  = ArrayHelper::getColumn($listing_ids, 'old');
		$new_ids  = ArrayHelper::getColumn($listing_ids, 'new');

		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->update($cTable)
			->set('order_id = ' . (int) $order_id)
			->where('id = ' . implode(' OR id = ', $db->q($new_ids)));

		$db->setQuery($query);
		$db->execute();

		// Make transactions entry - **money**
		$currency = $this->helper->currency->getGlobal('code_3');
		$values   = array(
			(object) array(
				'order_id'   => $order_id,
				'user_id'    => $seller_uid,
				'context'    => 'user.id',
				'context_id' => $seller_uid,
				'reason'     => 'listing',
				'crdr'       => 'dr',
				'amount'     => $fee_total,
				'currency'   => $currency,
				'txn_date'   => $now,
				'notes'      => "Product listing fee for seller {$seller_uid}. " .
								"Total " . count($listing_ids) . " listing items " .
								"with order id: {$order_id} of value {$fee_total} {$currency}.",
				'state'      => '1',
			),
			(object) array(
				'order_id'   => $order_id,
				'user_id'    => 0,
				'context'    => 'user.id',
				'context_id' => 0,
				'reason'     => 'listing',
				'crdr'       => 'cr',
				'amount'     => $fee_total,
				'currency'   => $currency,
				'txn_date'   => $now,
				'notes'      => "Product listing fee from seller {$seller_uid}. " .
								"Total " . count($listing_ids) . " listing items " .
								"with order id: {$order_id} of value {$fee_total} {$currency}.",
				'state'      => '1',
			)
		);
		$this->helper->transaction->register($values);

		// Update order status to PAID, It is a prepaid txn and money has been debited.
		$oTable->set('order_status', 'paid');
		$oTable->store();

		// Disable old listings and enable new ones.
		if (count($new_ids))
		{
			$query = $db->getQuery(true);
			$query->update($cTable)->set('state = 1')
					->where('id = ' . implode(' OR id = ', $db->q($new_ids)));

			$db->setQuery($query);
			$db->execute();
		}

		if (count($old_ids))
		{
			$query = $db->getQuery(true);
			$query->update($cTable)->set('state = 0')
					->where('id = ' . implode(' OR id = ', $db->q($old_ids)));

			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	/**
	 * Extend basic listing for given product+seller
	 *
	 * @param   int   $product_id
	 * @param   int   $seller_uid
	 * @param   int   $category_id
	 * @param   int   $days
	 * @param   bool  $enable
	 *
	 * @return  int[]
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function extend($product_id, $seller_uid, $category_id, $days, $enable = false)
	{
		$active   = $this->helper->listing->getActive($product_id, $seller_uid, $category_id);
		$expiry   = $active->state == 1 ? $active->publish_down : 'now';
		$dt_start = JFactory::getDate('now')->toSql();
		$dt_end   = JFactory::getDate($expiry)->add(new DateInterval('P' . (int) $days . 'D'))->toSql();

		// Todo: Separate any existing paid listing record and do not overwrite with this free one.
		$listing = new stdClass;
		$listing->product_id        = $product_id;
		$listing->seller_uid        = $seller_uid;
		$listing->category_id       = $category_id;
		$listing->days              = $days;
		$listing->publish_up        = $dt_start;
		$listing->publish_down      = $dt_end;
		$listing->subscription_date = $active->state == 1 ? $active->publish_up : $dt_start;
		$listing->carried_from      = $active->state == 1 ? $active->id : 0;
		$listing->state             = 0;

		$nTable = $this->getTable();
		$nTable->bind((array) $listing);
		$nTable->check();

		if ($enable)
		{
			$oTable = $this->getTable();
			$oTable->load($active->id);

			$nTable->set('state', 1);
			$nTable->store();

			if ($oTable->get('id'))
			{
				$oTable->set('state', 0);
				$oTable->store();
			}
		}
		else
		{
			$nTable->store();
		}

		$listing_id = array(
			'old' => $active->id,
			'new' => $nTable->get('id'),
		);

		return $listing_id;
	}

	/**
	 * Method to check whether the manage listing options should be exercised or not.
	 * This can be on several cases which will be checked here.
	 *
	 * @param   bool  $special  Whether to check only special category (true),
	 *                          only basic (false), or both (null) for being applicable
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function isApplicable($special = null)
	{
		if (!$this->helper->config->get('multi_seller', 0))
		{
			return false;
		}

		if ($special === true)
		{
			$filters = array('list.where' => array('a.state = 1', 'a.parent_id > 0'));
			$count   = $this->helper->splCategory->count($filters);

			return $count > 0;
		}
		elseif ($special === false)
		{
			$free = $this->helper->config->get('free_listing');

			return !$free;
		}
		else
		{
			$filters = array('list.where' => array('a.state = 1', 'a.parent_id > 0'));

			$count = $this->helper->splCategory->count($filters);
			$free  = $this->helper->config->get('free_listing');

			return $count > 0 || !$free;
		}
	}

	/**
	 * Method to revoke active subscriptions from selected categories
	 *
	 * @param   array  $catIds  The special categories id to revoke from
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function deactivate(array $catIds)
	{
		$query = $this->db->getQuery(true);

		// Mark trashed so that we know it was deactivated on purpose and not just expired.
		$query->update('#__sellacious_seller_listing')
			->set('state = -2')
			->where('category_id IN (' . implode(',', $catIds) . ')');

		$this->db->setQuery($query)->execute();
	}
}
