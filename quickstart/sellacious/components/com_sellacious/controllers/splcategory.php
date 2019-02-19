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
 * SplCategory controller class.
 */
class SellaciousControllerSplCategory extends SellaciousControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_SPLCATEGORY';

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see     JControllerForm
	 *
	 * @since   1.5.0
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);

		$this->registerTask('save2update', 'save');
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
		return $this->helper->access->check('splcategory.create');
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
		return $this->helper->access->check('splcategory.edit');
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   JModelLegacy $model     The data model object.
	 * @param   array        $validData The validated data.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		parent::postSaveHook($model, $validData);

		if ($this->getTask() == 'save2update' && $validData['fee_amount'] <= 0.00)
		{
			$catid = $model->getState('splcategory.id');
			$days  = 2000;

			try
			{
				$listings = (array) $this->helper->listing->loadObjectList(array('category_id' => $catid, 'state' => 1));

				if ($listings)
				{
					foreach ($listings as $listing)
					{
						$this->helper->listing->extend($listing->product_id, $listing->seller_uid, $catid, $days, true);
					}

					$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_SPLCATEGORY_UPDATE_SUBSCRIPTION_SUCCESS'));
				}
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage(JText::sprintf('COM_SELLACIOUS_SPLCATEGORY_UPDATE_SUBSCRIPTION_FAILED', $e->getMessage()), 'warning');
			}
		}
	}
}
