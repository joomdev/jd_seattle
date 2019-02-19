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

/**
 * Message controller class.
 */
class SellaciousControllerMessage extends SellaciousControllerForm
{
	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_MESSAGE';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerForm
	 * @since   3.0
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		$this->registerTask('reply', 'edit');
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowAdd($data = array())
	{
		return $this->helper->access->check('message.create');
	}

	/**
	 * Method to check if you can edit an existing record.
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		if ($this->helper->access->check('message.reply'))
		{
			return true;
		}

		if ($this->helper->access->check('message.reply.own'))
		{
			$me   = JFactory::getUser();
			$item = $this->helper->message->getItem($data[$key]);

			if ($item->sender == $me->id || $item->recipient == $me->id)
			{
				return true;
			}

			$rec = $this->helper->message->getRecipients($data[$key]);

			return in_array($me->id, (array) $rec);
		}

		return false;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  bool   True if access level checks pass, false otherwise.
	 *
	 * @since   12.2
	 */
	public function cancel($key = null)
	{
		if (parent::cancel($key))
		{
			$this->app->setUserState('com_sellacious.edit.message.seller_uid', null);

			return true;
		}

		return false;
	}
}
