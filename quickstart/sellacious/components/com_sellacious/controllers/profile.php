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
 * User own profile controller class.
 */
class SellaciousControllerProfile extends SellaciousControllerForm
{
	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PROFILE';

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
		return false;
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
		return $this->helper->access->check('user.edit.own') && $data[$key] == JFactory::getUser()->id;
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string $key    The name of the primary key of the URL variable.
	 * @param   string $urlVar The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$me  = JFactory::getUser();

		$this->input->set('id', $me->id);

		return parent::save($key, $urlVar);
	}

	/**
	 * Method to save an address via Ajax request.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function saveAddressAjax()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var SellaciousModelVariant $model */
		$model = $this->getModel();
		$post  = $this->input->post->get('jform', array(), 'array');
		$me    = JFactory::getUser();

		try
		{
			if (!$this->allowSave($post['address'], 'user_id'))
			{
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			}

			/** @var JForm $form */
			$form      = JForm::getInstance('com_sellacious.profile.address', 'profile/address');
			$validData = $model->validate($form, $post);

			if ($validData == false)
			{
				throw new Exception(JText::_($this->text_prefix . '_ADDRESS_SAVE_ERROR_INVALID_DATA', $model->getError()));
			}

			$address = $validData['address'];
			$address = $this->helper->user->saveAddress($address, $me->id);
			$message = JText::_($this->text_prefix . '_ADDRESS_SAVE_SUCCESS_BACKEND');
			$data    = $address->id;
			$state   = 1;
		}
		catch (Exception $e)
		{
			$state   = 0;
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $e->getMessage());
			$data    = null;
		}

		echo json_encode(array('state' => $state, 'message' => $message, 'data' => $data));

		$this->app->close();
	}

	/**
	 * Method to get an address via Ajax request.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function getAddressAjax()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$pk  = $this->input->post->getInt('id');
		$me  = JFactory::getUser();

		try
		{
			$address = $this->helper->user->getAddressById($pk);

			// Make sure edit id matches the selected user.
			if ($pk == 0 || $address->user_id == $me->id)
			{
				$html       = JLayoutHelper::render('com_sellacious.user.address.row', $address);
				$data       = $address;
				$data->html = preg_replace(array('|[\n\t]|', '|\s+|'), array('', ' '), $html);
				$state      = 1;
				$message    = '';
			}
			else
			{
				throw new Exception(JText::_('COM_SELLACIOUS_USER_ADDRESS_LOAD_FAILED'));
			}
		}
		catch (Exception $e)
		{
			$data    = null;
			$state   = 0;
			$message = $e->getMessage();
		}

		echo json_encode(array('state' => $state, 'message' => $message, 'data' => $data));

		$this->app->close();
	}

	/**
	 * Method to get an address via Ajax request.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function deleteAddressAjax()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$pk  = $this->input->post->getInt('id');
		$me  = JFactory::getUser();

		try
		{
			$this->helper->user->removeAddress($pk, $me->id);
			$message = JText::plural($this->text_prefix . '_ADDRESS_REMOVE_SUCCESS_N', 1);
			$state   = 1;
		}
		catch (Exception $e)
		{
			$message = JText::sprintf($this->text_prefix . '_ADDRESS_REMOVE_FAILED', $e->getMessage());
			$state   = 0;
		}

		echo json_encode(array('state' => $state, 'message' => $message, 'data' => null));

		$this->app->close();
	}
}
