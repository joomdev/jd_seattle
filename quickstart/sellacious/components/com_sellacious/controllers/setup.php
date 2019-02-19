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
 * Config controller class.
 *
 * @since   1.5.0
 */
class SellaciousControllerSetup extends SellaciousControllerForm
{
	/**
	 * @var   string  The name of the list view related to this
	 *
	 * @since   1.5.0
	 */
	protected $view_list = 'dashboard';

	/**
	 * @var   string  The prefix to use with controller messages
	 *
	 * @since   1.5.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_CONFIG';

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	protected function allowSave($data, $key = 'id')
	{
		return $this->helper->access->check('config.edit');
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		// Activate premium trial
		if (!empty($validData['premium_trial']))
		{
			try
			{
				if ($this->helper->core->requestTrial())
				{
					$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_ACTIVATION_SUCCESS_PREMIUM_TRIAL'));
				}
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'warning');
			}
		}

		parent::postSaveHook($model, $validData);
	}
}
