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

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious message model.
 */
class SellaciousModelMessage extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		return $this->helper->access->check('message.delete');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  bool  True if allowed to change the state of the record.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return false;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $item  A record object.
	 *
	 * @return  bool  True if allowed to edit the record.
	 *
	 * @since   12.2
	 */
	protected function canEdit($item)
	{
		if (!$item->id)
		{
			return $this->helper->access->checkAny(array('create.bulk', 'create'), 'message.');
		}

		if ($this->helper->access->check('message.reply'))
		{
			return true;
		}

		if ($this->helper->access->check('message.reply.own'))
		{
			$me   = JFactory::getUser();
			$item = $this->helper->message->getItem($item->id);

			if ($item->sender == $me->id || $item->recipient == $me->id)
			{
				return true;
			}

			$rec = $this->helper->message->getRecipients($item->id);

			return in_array($me->id, (array) $rec);
		}

		return false;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function save($data)
	{
		$date = JFactory::getDate();
		$me   = JFactory::getUser();

		try
		{
			if (!$this->canEdit((object) $data))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));
			}

			if (empty($data['id']))
			{
				$sel_keys = explode(',', ArrayHelper::getValue($data, 'recipients'));
				$user_ids = $this->getRecipients($sel_keys, $sel_cats);

				if (count($user_ids) == 0)
				{
					throw new Exception(JText::_('COM_SELLACIOUS_MESSAGE_NO_VALID_RECIPIENTS_FOUND'));
				}

				// Check whether this will be treated as a bulk/broadcast message.
				$is_bulk   = count($sel_keys) > 1 || count($user_ids) > 1 || !is_numeric(reset($sel_keys));
				$recipient = $is_bulk ? -1 : reset($user_ids);
				$parent_id = 1;
				$subject   = $data['title'];
				$body      = $data['body'];
			}
			else
			{
				// Fixme: Something is not right about the reply handling!
				$item = $this->helper->message->getItem($data['id']);

				while ($item->parent_id > 1)
				{
					$item = $this->helper->message->getItem($item->parent_id);
				}

				if ($item->parent_id != 1)
				{
					throw new Exception(JText::_('COM_SELLACIOUS_MESSAGE_NO_REPLY_SUB_MESSAGE'));
				}

				$is_bulk   = false;
				$recipient = $me->id == $item->sender ? $item->recipient : ($me->id == $item->recipient ? $item->sender : -1);
				$parent_id = $item->id;
				$subject   = $item->title;
				$body      = $data['body'];
				$sel_cats  = array($item->sender, $item->recipient);
				$user_ids  = array($item->sender, $item->recipient);
			}

			$array = array(
				'parent_id' => $parent_id,
				'sender'    => $me->id,
				'recipient' => $recipient,
				'title'     => $subject,
				'body'      => $body,
				'context'   => 'message',
				'date_sent' => $date->toSql(),
				'state'     => 1,
				'remote_ip' => $this->app->input->server->getString('REMOTE_ADDR'),
				'params'    => array('recipients' => $sel_cats, 'users' => $user_ids),
			);

			/** @var  SellaciousTableMessage  $table */
			$table = $this->getTable();

			$table->bind($array);

			$table->setLocation($table->parent_id, 'last-child');

			$table->check();
			$table->store();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		if ($is_bulk)
		{
			$db     = JFactory::getDbo();
			$query  = $db->getQuery(true);
			$msg_id = $this->state->get('message.id');

			$query->insert('#__sellacious_message_recipients')->columns(array('message_id', 'recipient'));

			foreach ($user_ids as $user_id)
			{
				$query->values($db->q($msg_id) . ', ' . $db->q($user_id));
			}

			$db->setQuery($query)->execute();
		}

		$message            = (object) $table->getProperties();
		$message->recipient = $is_bulk ? $user_ids : $message->recipient;

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.message', $message, $isNew = true));

		return true;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not
	 *
	 * @return  JForm|bool  A JForm object on success, false on failure
	 *
	 * @since   1.2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$allow = false;
		$item  = $this->getItem();

		if ($item->get('id') == 0)
		{
			$allow = $this->helper->access->check('message.create');
		}
		elseif ($this->helper->access->check('message.reply'))
		{
			$allow = true;
		}
		elseif ($this->helper->access->check('message.reply.own'))
		{
			$me    = JFactory::getUser();
			$allow = $item->get('sender') == $me->id || $item->get('recipient') == $me->id;

			if (!$allow)
			{
				// Uncomment following lines if we want to allow replying to a broadcast message
				// $rec   = $this->helper->message->getRecipients($item->get('id'));
				// $allow = in_array($me->id, (array) $rec);
			}
		}

		return $allow ? parent::getForm($data, $loadData) : false;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = $this->app->getUserStateFromRequest($this->option . '.edit.' . $this->name . '.data', 'jform', array(), 'array');

		if (empty($data))
		{
			$data = array('id' => $this->getState('message.id'));
		}

		$this->preprocessData('com_sellacious.' . $this->name, $data);

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   12.2
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		if ($this->getState('message.id'))
		{
			$form->removeField('recipients');
			$form->removeField('title');
		}

		if (!$this->helper->access->check('message.html'))
		{
			$form->setFieldAttribute('body', 'type', 'textarea');
			$form->setFieldAttribute('body', 'filter', 'string');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Pre-process loaded item before returning if needed
	 *
	 * @param   object  $item
	 *
	 * @return  object
	 */
	protected function processItem($item)
	{
		if (!$this->canEdit($item))
		{
			$this->setError(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));
		}
		elseif ($item->id)
		{
			$item->thread     = $this->helper->message->getThread($item);
			$item->recipients = $item->recipient == -1 ? $this->helper->message->getRecipients($item->id) : array($item->recipient);
		}

		return parent::processItem($item);
	}

	/**
	 * Process the recipient categories submitted from compose message form to find out target user ids
	 *
	 * @param   array  $sel_keys   Array of values (category identifiers + user ids) submitted via form
	 * @param   array  &$sel_cats  Associative array of category identifiers and the respective titles (to be returned)
	 *
	 * @return  int[]  User ids of the final recipients
	 */
	protected function getRecipients($sel_keys, &$sel_cats)
	{
		$all_cats = $this->helper->message->getRecipientGroups();
		$sel_cats = array();
		$user_ids = array();

		foreach ($sel_keys as $sel_id)
		{
			if (is_numeric($sel_id))
			{
				$sel_cats['uid:' . $sel_id] = $this->helper->user->getFieldValue($sel_id, 'name');

				$user_ids[] = $sel_id;
			}
			elseif ($this->helper->access->check('message.create.bulk'))
			{
				$sel_cats[$sel_id] = $this->helper->core->getArrayField($all_cats, 'id', $sel_id, 'text', '');

				$uids = $this->helper->message->getRecipientsByGroup($sel_id);

				foreach ($uids as $uid)
				{
					$user_ids[] = $uid;
				}
			}
		}

		return ArrayHelper::toInteger(array_unique($user_ids));
	}
}
