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
 * Coupon controller class.
 */
class SellaciousControllerCoupon extends SellaciousControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_COUPON';

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
		return $this->helper->access->check('coupon.create');
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
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
		$me         = JFactory::getUser();
		$seller_uid = $this->helper->coupon->getFieldValue($data[$key], 'seller_uid');

		$allow = $this->helper->access->check('coupon.edit') ||
			($this->helper->access->check('coupon.edit.own') && $seller_uid == $me->id);

		return $allow;
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
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post = $this->input->get('jform', array(), 'array');

		if (strcasecmp($this->getTask(), 'setSeller') == 0)
		{
			// todo: Reset all seller specific fields so that blank data can load for new selected seller
			// fixme: user_utc fails here
			unset($post['publish_up'], $post['publish_down']);
		}

		$this->app->setUserState('com_sellacious.edit.coupon.data', $post);
		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=coupon&layout=edit', false));

		return true;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string $key The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   12.2
	 */
	public function cancel($key = null)
	{
		if (parent::cancel($key))
		{
			$this->app->setUserState('com_sellacious.edit.coupon.seller_uid', null);
		}
	}
}
