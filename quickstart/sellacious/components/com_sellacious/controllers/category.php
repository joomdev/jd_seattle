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
 * Category controller class.
 */
class SellaciousControllerCategory extends SellaciousControllerForm
{
	/**
	 * @var    string  The prefix to use with controller messages
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_CATEGORY';

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  bool
	 *
	 * @since   12.2
	 */
	protected function allowAdd($data = array())
	{
		return $this->helper->access->check('category.create');
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
		return $this->helper->access->check('category.edit');
	}

	/**
	 * Set the category type in the user state so that the form is rebuilt accordingly
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function setType()
	{
		// Get the posted values from the request.
		$data     = $this->input->post->get('jform', array(), 'array');
		$recordId = $this->input->getInt('id');

		//Save the data in the session.
		$this->app->setUserState('com_sellacious.edit.' . $this->context . '.data', $data);
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId), false));

		return true;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   12.2
	 */
	protected function getRedirectToListAppend()
	{
		$append = '&filter[type]=';
		$str    = $this->option . '.' . $this->view_list . '.filter.type';

		if ($filter_type = $this->app->getUserState($str))
		{
			$append = $append . $filter_type;
		}

		return $append . parent::getRedirectToListAppend();
	}
}
