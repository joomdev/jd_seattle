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
 * Field controller class.
 *
 */
class SellaciousControllerField extends SellaciousControllerForm
{
	protected $text_prefix = 'COM_SELLACIOUS_FIELD';

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
		return $this->helper->access->check('field.create');
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
		return $this->helper->access->check('field.edit');
	}

	/**
	 * Update application state from post so that form is rebuild accordingly
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	function setType()
	{
		// Get the posted values from the request.
		$data     = $this->input->post->get('jform', array(), 'array');
		$recordId = $this->input->post->getInt('id');

		//Save the data in the session.
		$this->app->setUserState('com_sellacious.edit.field.data', $data);
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId), false));
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model The model.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var JModelLegacy $model */
		$model = $this->getModel();

		$this->setRedirect('index.php?option=com_sellacious&view=fields');

		return parent::batch($model);
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
		$append = '&filter[context]=';
		$str    = $this->option . '.' . $this->view_list . '.filter.context';

		if ($filter_type = $this->app->getUserState($str))
		{
			$append = $append . $filter_type;
		}

		return $append . parent::getRedirectToListAppend();
	}
}
