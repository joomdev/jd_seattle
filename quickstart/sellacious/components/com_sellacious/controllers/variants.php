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
 * Variants list controller class.
 */
class SellaciousControllerVariants extends SellaciousControllerBase
{
	/**
	 * Constructor.
	 *
	 * @param  array $config An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  3.0
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		$this->registerTask('apply', 'save');
	}

	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_VARIANTS';

	/**
	 * Method to manage variants of one or more existing products.
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 */
	public function manage()
	{
		$cid = $this->input->post->get('cid', array(), 'array');

		if (!$this->helper->config->get('multi_variant'))
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_VARIANTS_CONFIG_DISABLED'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products&layout=bulk', false));

			return false;
		}

		if (count($cid) == 0)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_VARIANTS_NO_ITEM_SELECTED'), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products&layout=bulk', false));

			return false;
		}

		$this->app->setUserState('com_sellacious.variants.products', $cid);
		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=variants', false));

		return true;
	}

	/**
	 * Save the prices and stock
	 *
	 * @return  bool
	 */
	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$items = $this->input->get('jform', array(), 'array');

		$this->setRedirect($this->getReturnURL());

		if (count($items) == 0)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_PRODUCTS_NO_ITEM_SELECTED'), 'warning');

			return false;
		}

		/** @var  SellaciousModelVariants $model */
		$i     = 0;
		$model = $this->getModel('Variants');

		foreach ($items as $item)
		{
			try
			{
				$model->savePriceAndStock($item);

				$i++;
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}
		}

		if ($i == count($items))
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_VARIANTS_UPDATE_SUCCESS'));

			if ($this->getTask() == 'save')
			{
				$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products&layout=bulk', false));
			}
		}
		else
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_VARIANTS_UPDATE_FAILED'), 'warning');
		}

		return true;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	public function cancel()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->app->setUserState('com_sellacious.variants.products', null);

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products&layout=bulk', false));

		return true;
	}

	/**
	 * Provides autocomplete interface to javascript functions
	 * supported contexts are: title, manufacturer
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function autoComplete()
	{
		$query = $this->input->get('query');

		try
		{
			ob_start();

			/** @var  SellaciousModelVariants $model */
			$model    = $this->getModel('Variants');
			$data     = $model->suggest(trim($query));
			$response = array('status' => 1, 'message' => '', 'data' => $data);
		}
		catch (Exception $e)
		{
			$response = array('status' => 0, 'message' => $e->getMessage(), 'data' => null);
		}

		$response['junk'] = ob_get_clean();

		echo json_encode($response);
		jexit();
	}

	/**
	 * Get details of given item
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getInfoAjax()
	{
		$keys = $this->input->get('code', array(), 'array');

		try
		{
			ob_start();

			/** @var  SellaciousModelVariants $model */
			$model    = $this->getModel('Variants');
			$items    = $model->getSuggested($keys);
			$response = array('status' => 1, 'message' => '', 'data' => $items);
		}
		catch (Exception $e)
		{
			$response = array('status' => 0, 'message' => $e->getMessage(), 'data' => null);
		}

		$response['junk'] = ob_get_clean();

		echo json_encode($response);
		jexit();
	}

	/**
	 * Get the redirect url for the calling page
	 *
	 * @return  string
	 */
	protected function getReturnURL()
	{
		return JRoute::_('index.php?option=com_sellacious&view=variants', false);
	}
}
