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
 * Unit controller class.
 */
class SellaciousControllerUnit extends SellaciousControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_UNIT';

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
		return $this->helper->access->check('unit.create');
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
		return $this->helper->access->check('unit.edit');
	}
	/**
	 * Sets the group of the unit currently being edited.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function setType()
	{
		// Get the posted values from the request.
		$data     = $this->input->post->get('jform', array(), 'array');
		$recordId = $this->input->getInt('id');

		//Save the data in the session.
		$this->app->setUserState('com_sellacious.edit.unit.data', $data);
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item . $this->getRedirectToItemAppend($recordId), false));
	}

}
