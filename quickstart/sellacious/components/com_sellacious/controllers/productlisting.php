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

use Joomla\Utilities\ArrayHelper;

/**
 * Product controller class.
 */
class SellaciousControllerProductListing extends SellaciousControllerForm
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PRODUCTLISTING';

	/** @var string */
	protected $view_list = 'products';

	/** @var string */
	protected $view_item = 'productlisting';

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
		$this->registerTask('setCategory', 'setType');
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
		return $this->helper->access->check('product.create');
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
		// fixme: apply correct rules here
		return $this->helper->access->check('product.edit');
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
	 * @since   12.2
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$cid     = ArrayHelper::getColumn($validData['products'], 'product_id');
		$cat_ids = ArrayHelper::getColumn($validData['listings'], 'category_id');

		$cTitles = (array) $this->helper->splCategory->loadColumn(array('id' => $cat_ids, 'list.select' => 'a.title'));

		if (in_array(0, $cat_ids))
		{
			array_unshift($cTitles, JText::_('COM_SELLACIOUS_PRODUCTLISTING_FIELD_CATEGORY_BASIC'));
		}

		$cTitle = implode(', ', $cTitles);

		if (count($cid) == 1)
		{
			$pTitle = $this->helper->product->loadResult(array('id' => $cid, 'list.select' => 'a.title'));
			$this->setMessage(JText::sprintf('COM_SELLACIOUS_PRODUCTLISTING_UPDATE_SUCCESS_LABELLED', $cTitle, $pTitle));
		}
		else
		{
			$this->setMessage(JText::sprintf('COM_SELLACIOUS_PRODUCTLISTING_UPDATE_SUCCESS_PRODUCT_COUNT', $cTitle, count($cid)));
		}

		if (isset($validData['seller_uid']))
		{
			$this->app->setUserState('com_sellacious.edit.productlisting.data.seller_uid', $validData['seller_uid']);
		}

		parent::postSaveHook($model, $validData);
	}

	/**
	 * Common function to simply update the form data and update session for it.
	 * Can be used in all contexts such as change of parent, type, category etc.
	 *
	 * @return  bool
	 */
	public function setType()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post = $this->input->get('jform', array(), 'array');

		if (strcasecmp($this->getTask(), 'setSeller') == 0 || strcasecmp($this->getTask(), 'setCategory') == 0)
		{
			// Reset all seller specific fields so that blank data can load for new selected seller
			unset($post['listing_days']);
			unset($post['special_categories']);
			unset($post['products']);
		}

		$this->app->setUserState('com_sellacious.edit.productlisting.data', $post);
		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=productlisting', false));

		return true;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	public function cancel($key = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products', false));

		if (parent::cancel($key))
		{
			$this->app->setUserState('com_sellacious.productlisting.products', null);
			$this->app->setUserState('com_sellacious.edit.productlisting.data.seller_uid', null);
		}
	}
}
