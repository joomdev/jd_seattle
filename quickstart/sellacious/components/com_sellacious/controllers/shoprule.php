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
 * Shoprule controller class
 */
class SellaciousControllerShoprule extends SellaciousControllerForm
{
	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_SHOPRULE';

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see     JControllerForm
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('setSeller', 'setType');
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array $data An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowAdd($data = array())
	{
		if ($this->helper->access->check('shoprule.create'))
		{
			return true;
		}

		$user           = JFactory::getUser();
		$multi_seller   = $this->helper->config->get('multi_seller', 0);
		$default_seller = $this->helper->config->get('default_seller');

		// Default seller can create shoprules if not multi-seller else only set permissions are used
		return !$multi_seller && $default_seller == $user->id;
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$me         = JFactory::getUser();
		$seller_uid = $this->helper->shopRule->getFieldValue($data[$key], 'seller_uid');

		$allow = $this->helper->access->check('shoprule.edit') ||
			($this->helper->access->check('shoprule.edit.own') && $seller_uid == $me->id);

		if ($allow)
		{
			return true;
		}

		$user           = JFactory::getUser();
		$multi_seller   = $this->helper->config->get('multi_seller', 0);
		$default_seller = $this->helper->config->get('default_seller');

		// Default seller can create shoprules if not multi-seller else only set permissions are used
		return !$multi_seller && $default_seller == $user->id;
	}

	/**
	 * Common function to simply update the form data and update session for it.
	 * Can be used in all contexts such as change of parent, type, category etc.
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function setType()
	{
		// Initialise variables.
		$key  = $this->input->getInt('id');
		$data = $this->input->post->get('jform', array(), 'array');

		if (strcasecmp($this->getTask(), 'setSeller') == 0)
		{
			unset($data['publish_up'], $data['publish_down']);
		}

		//Save the data in the session.
		$this->app->setUserState('com_sellacious.edit.shoprule.data', $data);
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($key), false));

		return true;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string $key The name of the primary key of the URL variable.
	 *
	 * @return  void.
	 *
	 * @since   12.2
	 */
	public function cancel($key = null)
	{
		if (parent::cancel($key))
		{
			$this->app->setUserState('com_sellacious.edit.shoprule.seller_uid', null);
		}
	}
}
