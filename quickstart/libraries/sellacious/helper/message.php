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

/**
 * Sellacious message helper.
 *
 * @since  1.0
 */
class SellaciousHelperMessage extends SellaciousHelperBase
{
	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  stdClass[]
	 */
	public function getRecipientGroups()
	{
		$db     = $this->db;
		$groups = array();
		$types  = array('client', 'seller', 'staff', 'manufacturer');
		$filter = array(
			'list.select' => 'a.id, a.title, a.type',
			'list.order'  => 'a.type ASC, a.title ASC',
		);

		$item           = new stdClass();
		$item->id       = 'cat:all';
		$item->text     = JText::_('COM_SELLACIOUS_MESSAGE_RECIPIENT_OPTION_ALL');
		$item->bulk     = true;
		$item->optgroup = true;
		$groups[]       = $item;

		foreach ($types as $type)
		{
			$filter['list.where'] = array('a.state = 1', 'a.type = ' . $db->q($type));

			$cats = $this->helper->category->loadObjectList($filter);

			if (count($cats))
			{
				$item           = new stdClass();
				$item->text     = JText::_('COM_SELLACIOUS_MESSAGE_RECIPIENT_OPTION_' . strtoupper($type));
				$item->bulk     = true;
				$item->optgroup = true;
				$item->disabled = true;
				$groups[]       = $item;

				$item           = new stdClass();
				$item->id       = 'cat:' . $type;
				$item->text     = JText::_('COM_SELLACIOUS_MESSAGE_RECIPIENT_OPTION_ALL_' . strtoupper($type));
				$item->bulk     = true;
				$item->optgroup = true;
				$groups[]       = $item;

				foreach ($cats as $cat)
				{
					$item       = new stdClass();
					$item->id   = 'cat:' . $cat->id;
					$item->text = $cat->title;
					$item->bulk = true;
					$groups[]   = $item;
				}
			}
		}

		return $groups;
	}

	/**
	 * Get the user id of all users that fall into the given group-id.
	 *
	 * @param   string $group Group identifier as defined in the function above
	 *
	 * @return  int[]
	 * @see     getRecipientGroups()
	 */
	public function getRecipientsByGroup($group)
	{
		$pks = array();

		switch (true)
		{
			case $group == 'cat:all':
				$pks = $this->helper->profile->loadColumn(array('list.select' => 'a.user_id', 'state' => 1));
				break;

			case $group == 'cat:seller':
				$pks = $this->helper->seller->loadColumn(array('list.select' => 'a.user_id', 'state' => 1));
				break;

			case $group == 'cat:client':
				$pks = $this->helper->client->loadColumn(array('list.select' => 'a.user_id', 'state' => 1));
				break;

			case $group == 'cat:staff':
				$pks = $this->helper->staff->loadColumn(array('list.select' => 'a.user_id', 'state' => 1));
				break;

			case $group == 'cat:manufacturer':
				$pks = $this->helper->manufacturer->loadColumn(array('list.select' => 'a.user_id', 'state' => 1));
				break;

			case is_numeric($cid = substr($group, 4)):
				$pka[] = $this->helper->seller->loadColumn(array('list.select' => 'a.user_id', 'state' => 1, 'category_id' => $cid));
				$pka[] = $this->helper->client->loadColumn(array('list.select' => 'a.user_id', 'state' => 1, 'category_id' => $cid));
				$pka[] = $this->helper->staff->loadColumn(array('list.select' => 'a.user_id', 'state' => 1, 'category_id' => $cid));
				$pka[] = $this->helper->manufacturer->loadColumn(array('list.select' => 'a.user_id', 'state' => 1, 'category_id' => $cid));
				$pks   = array_reduce($pka, 'array_merge', array());
				break;

			default:
				// ignore
		}

		return $pks;
	}

	/**
	 * Get the entire thread for any selected message.
	 *
	 * @param  int|object $item  The message object or the entire record from the db
	 *
	 * @return  stdClass[]
	 */
	public function getThread($item)
	{
		if (is_numeric($item))
		{
			$item = $this->getItem($item);
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		$where = array(
			'(a.lft <= ' . $db->q($item->lft) . ' AND ' . $db->q($item->rgt) . ' <= a.rgt)',
			'(a.lft >= ' . $db->q($item->lft) . ' AND ' . $db->q($item->rgt) . ' >= a.rgt)',
		);

		$query->select('a.*')
			->from($db->qn('#__sellacious_messages', 'a'))
			->where('(' . implode(' OR ', $where) . ')')
			->where('a.level > 0')
			->order('a.date_sent DESC');

		try
		{
			$items = $db->setQuery($query)->loadObjectList();

			if (is_array($items))
			{
				// Prepare the content before rendering
				$dispatcher = $this->helper->core->loadPlugins();
				$params     = array('link' => true);

				foreach ($items as $item)
				{
					$item->text = $item->body;
					$dispatcher->trigger('onContentPrepare', array('com_sellacious.message', &$item, &$params));
					$item->body = $item->text;
				}
			}
		}
		catch (Exception $e)
		{
			$items = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $items;
	}

	/**
	 * Get the recipients list for the selected message. Only applicable for broadcast messages.
	 *
	 * @param   int  $msg_id  Message id for the query
	 *
	 * @return  int[]
	 *
	 * @since   1.2.0
	 */
	public function getRecipients($msg_id)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->select('recipient')
			->from('#__sellacious_message_recipients')
			->where('message_id = ' . (int) $msg_id);

		try
		{
			$result = $db->setQuery($query)->loadColumn();
		}
		catch (Exception $e)
		{
			$result = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $result;
	}
}
