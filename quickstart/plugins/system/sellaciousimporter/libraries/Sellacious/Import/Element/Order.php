<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Element;

/**
 * Order entity import class
 *
 * @since   1.5.0
 */
class Order
{
	/**
	 * Internal method to create a order record.
	 *
	 * @param   \stdClass  $object  The record object to save
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public static function create($object)
	{
		$db = \JFactory::getDbo();

		if ($object->id)
		{
			$saved = $db->updateObject('#__sellacious_orders', $object, array('id'));
		}
		else
		{
			$saved = $db->insertObject('#__sellacious_orders', $object, 'id');
		}

		if (!$saved || $object->id == 0)
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_ORDER_SAVE_FAIL', $object->title));
		}

		return $object->id;
	}

	/**
	 * Internal method to create an order item.
	 *
	 * @param   \stdClass  $object  The record object to save
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public static function createItem($object)
	{
		$db = \JFactory::getDbo();

		if ($object->id)
		{
			$saved = $db->updateObject('#__sellacious_order_items', $object, array('id'));
		}
		else
		{
			$saved = $db->insertObject('#__sellacious_order_items', $object, 'id');
		}

		if (!$saved || $object->id == 0)
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_ORDER_ITEM_SAVE_FAIL', $object->order_id, $object->item_uid));
		}

		return $object->id;
	}
}
