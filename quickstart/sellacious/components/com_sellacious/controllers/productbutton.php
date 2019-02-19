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

/**
 * ProductButton controller class
 *
 * @since  1.6.0
 */
class SellaciousControllerProductButton extends SellaciousControllerForm
{
	/**
	 * @var   string  The prefix to use with controller messages.
	 *
	 * @since  1.6.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PRODUCT_BUTTON';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name
	 * @param   string  $prefix
	 * @param   array   $config
	 *
	 * @return  JModelLegacy
	 *
	 * @since   1.6.0
	 */
	public function getModel($name = 'ProductButton', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
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
	 * @since   1.6.0
	 */
	protected function allowAdd($data = array())
	{
		return $this->helper->access->check('product.create');
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
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		/** @var  SellaciousModelProductButton  $model */
		$model = $this->getModel();
		$item  = $model->getItem($data[$key]);

		$actions  = array('basic');
		$editable = $this->helper->access->checkAny($actions, 'product.edit.');

		if ($editable)
		{
			return true;
		}

		$me       = JFactory::getUser();
		$actions  = array('basic.own');
		$editable = $this->helper->access->checkAny($actions, 'product.edit.') && $me->id == $item->get('created_by');

		return $editable;
	}

	/**
	 * Set Shiprule selected for the selected item via Ajax
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function saveAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$data = $this->input->post->get('jform', array(), 'array');

			if (!$this->allowSave($data, 'id'))
			{
				throw new Exception(\JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			}

			/** @var  SellaciousModelProductButton  $model */
			$model = $this->getModel();
			$model->save($data);

			$response = array(
				'message' => JText::_($this->text_prefix . '_SAVE_SUCCESS'),
				'data'    => array('id' => $model->getState('productbutton.id', $data['id'])),
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => JText::sprintf($this->text_prefix . '_SAVE_FAILED', $e->getMessage()),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		jexit();
	}
}
