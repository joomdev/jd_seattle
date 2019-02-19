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
 * Question controller class.
 *
 * @since   1.6.0
 */
class SellaciousControllerQuestion extends SellaciousControllerForm
{
	/**
	 * @var     string   The prefix to use with controller messages.
	 *
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_QUESTION';

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see     JControllerForm
	 *
	 * @since   1.6.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
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
	 * @since   1.6.0
	 */
	protected function allowAdd($data = array())
	{
		return false;
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
	 * @since   1.6.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$me     = JFactory::getUser();
		$filter = array(
			'list.select' => 'a.seller_uid',
			'list.from'   => '#__sellacious_product_questions',
			$key          => $data[$key],
		);

		$seller_uid = $this->helper->product->loadResult($filter);

		$allow = $this->helper->access->check('question.edit') ||
			($this->helper->access->check('question.edit.own') && $seller_uid == $me->id);

		return $allow;
	}


	/**
	 * Method to cancel an edit.
	 *
	 * @param   string $key The name of the primary key of the URL variable.
	 *
	 * @return  void.
	 *
	 * @since   1.6.0
	 */
	public function cancel($key = null)
	{
		if (parent::cancel($key))
		{
			$this->app->setUserState('com_sellacious.edit.question.seller_uid', null);
		}
	}
}
