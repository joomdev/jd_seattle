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

use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\CacheHelper;

/**
 * Products list controller class.
 *
 * @since   1.0.0
 */
class SellaciousControllerProducts extends SellaciousControllerAdmin
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since   1.0.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PRODUCTS';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerAdmin
	 *
	 * @since   1.0.0
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		$this->registerTask('setNotSelling', 'setSelling');
		$this->registerTask('pending','publish');
		$this->registerTask('disapprove', 'publish');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name
	 * @param   string  $prefix
	 * @param   null    $config
	 *
	 * @return  object
	 *
	 * @since   1.0.0
	 */
	public function getModel($name = 'Product', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  bool  False on failure or error, true on success.
	 *
	 * @since   1.0.0
	 */
	public function rebuild()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$allowed = $this->helper->access->check('product.rebuild');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products', false));

		if (!$allowed)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'error');

			return false;
		}

		$model = $this->getModel();

		if ($model->rebuild())
		{
			$this->setMessage(JText::_($this->text_prefix . '_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			$this->setMessage(JText::sprintf($this->text_prefix . '_REBUILD_FAILURE', $model->getError()), 'error');

			return false;
		}
	}

	/**
	 * Create export of products
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function export()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			$tmplId   = $this->input->getInt('template_id');
			$download = $this->input->getInt('dl');

			if (!$download)
			{
				$formToken = JSession::getFormToken();
				$redirect  = JRoute::_(sprintf('index.php?option=com_sellacious&task=products.export&dl=1&template_id=%d&%s=1', $tmplId, $formToken));

				$this->helper->core->metaRedirect($redirect);

				$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products', false));

				return true;
			}

			$tmpPath  = $this->app->get('tmp_path');
			$filename = $tmpPath . '/products-' . md5(time() . '-products') . '.csv';

			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onRequestExport', array('com_sellacious.products', $filename, $tmplId));

			if (!is_file($filename) || filesize($filename) <= 10)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_EXPORT_NO_HANDLER_FOUND'));
			}

			header('content-type: application/csv');
			header('content-disposition: attachment;filename="' . basename($filename) . '"');
			readfile($filename);

			JLoader::import('joomla.filesytem.file');
			JFile::delete($filename);
			jexit();
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
			$this->setRedirect($this->getReturnURL());

			return false;
		}

		return true;

	}

	/**
	 * Method to manage listing of one or more existing products.
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   1.0.0
	 */
	public function listing()
	{
		$cid = $this->input->post->get('cid', array(), 'array');
		$cid = ArrayHelper::toInteger($cid);

		if (count($cid) == 0)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_PRODUCTLISTING_NO_ITEM_SELECTED'), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products', false));

			return false;
		}

		try
		{
			$applicable = $this->helper->listing->isApplicable();
		}
		catch (Exception $e)
		{
			$applicable = false;
		}

		if (!$applicable)
		{
			// Automatically renews basic listings and special listing is handled by $this->specialListing();
			$this->setMessage(JText::_('COM_SELLACIOUS_PRODUCTLISTING_FEATURE_UNAVAILABLE_CONFIG'), 'notice');
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products', false));

			return false;
		}

		$this->app->setUserState('com_sellacious.productlisting.products', $cid);
		$this->app->setUserState('com_sellacious.edit.productlisting.data', array('seller_uid' => '', 'splcat_id' => ''));

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=productlisting', false));

		return true;
	}

	/**
	 * Method to manage special listing of one or more existing products.
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   1.0.0
	 */
	public function sellerListing()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			$cid        = $this->input->post->get('cid', array(), 'array');
			$cat_id     = $this->input->post->getInt('catid');
			$seller_uid = $this->input->post->getInt('seller_uid');
			$cid        = ArrayHelper::toInteger($cid);

			if (count($cid) == 0)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCTLISTING_NO_ITEM_SELECTED'));
			}

			if (!$seller_uid)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCTLISTING_NO_SELLER_SELECTED'));
			}

			if ($cat_id === null)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCTLISTING_NO_CATEGORY_SELECTED'));
			}

			if (!$this->helper->listing->isApplicable($cat_id > 0))
			{
				// Automatically renew special listings = 365 days.
				foreach ($cid as $product_id)
				{
					$this->helper->listing->extend($product_id, $seller_uid, $cat_id, 365, true);
				}

				$cTitle = $this->helper->splCategory->getFieldValue($cat_id, 'title', JText::_('COM_SELLACIOUS_PRODUCTLISTING_FIELD_CATEGORY_BASIC'));

				if (count($cid) == 1)
				{
					$pTitle = $this->helper->product->loadResult(array('id' => $cid, 'list.select' => 'a.title'));
					$this->setMessage(JText::sprintf('COM_SELLACIOUS_PRODUCTLISTING_UPDATE_SUCCESS_LABELLED', $cTitle, $pTitle));
				}
				else
				{
					$this->setMessage(JText::sprintf('COM_SELLACIOUS_PRODUCTLISTING_UPDATE_SUCCESS_PRODUCT_COUNT', $cTitle, count($cid)));
				}

				$this->setRedirect($this->getReturnURL());
			}
			else
			{
				$this->app->setUserState('com_sellacious.productlisting.products', $cid);
				$this->app->setUserState('com_sellacious.edit.productlisting.data', array('seller_uid' => $seller_uid, 'splcat_id' => $cat_id));

				$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=productlisting', false));
			}
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
			$this->setRedirect($this->getReturnURL());

			return false;
		}

		return true;
	}

	/**
	 * Method to set selling active of products - sellers.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function setSelling()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect($this->getReturnURL());

		$cid   = $this->input->post->get('cid', array(), 'array');
		$value = strtolower($this->getTask()) == strtolower('setNotSelling') ? 0 : 1;

		$pIds = array();
		$sIds = array();

		foreach ($cid as $productSeller)
		{
			if (strpos($productSeller, ':'))
			{
				list($productId, $sellerUid) = explode(':', $productSeller);

				if ((int) $productId && (int) $sellerUid)
				{
					$pIds[] = (int) $productId;
					$sIds[] = (int) $sellerUid;
				}
			}
		}

		if (count($pIds) == 0)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_PRODUCTS_NO_ITEM_SELECTED'), 'warning');

			return false;
		}

		/** @var  SellaciousModelProduct  $model */
		$model = $this->getModel();
		$pks   = $model->setSelling($pIds, $sIds, $value);

		if (count($pks))
		{
			$this->setMessage(JText::sprintf('COM_SELLACIOUS_PRODUCTS_SELLING_SET_N_OF_N', count($pks), count($cid)));
		}
		else
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_PRODUCTS_SELLING_SET_NONE'), 'warning');
		}

		return true;
	}

	/**
	 * Duplicate selected products
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function duplicate()
	{
		JSession::checkToken('request') or die('Invalid Token');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products', false));

		try
		{
			/** @var  SellaciousModelProduct  $model */
			$cid   = $this->input->get('cid', array(), 'array');
			$cid   = ArrayHelper::toInteger($cid);
			$model = $this->getModel();

			if (!$this->helper->access->check('product.create'))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));
			}

			$new = array();

			foreach ($cid as $id)
			{
				$new[$id] = $model->duplicate($id);
			}

			$this->setMessage(JText::plural('COM_SELLACIOUS_PRODUCT_DUPLICATE_SUCCESS', count($new)));
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::sprintf('COM_SELLACIOUS_PRODUCT_DUPLICATE_FAILURE_ERROR', $e->getMessage()), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to manage special listing of one or more existing products via Ajax call.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function sellerListingAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$cid        = $this->input->post->get('cid', array(), 'array');
			$cat_id     = $this->input->post->getInt('catid');
			$seller_uid = $this->input->post->getInt('seller_uid');
			$cid        = ArrayHelper::toInteger($cid);

			if (count($cid) == 0)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCTLISTING_NO_ITEM_SELECTED'));
			}

			if (!$seller_uid)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCTLISTING_NO_SELLER_SELECTED'));
			}

			if ($cat_id === null)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCTLISTING_NO_CATEGORY_SELECTED'));
			}

			$splCategory = $this->helper->splCategory->getItem($cat_id);

			if (!$splCategory->fee_amount || !$this->helper->listing->isApplicable($cat_id > 0))
			{
				$listing_ids = array();
				$days        = 365;

				if (!$splCategory->fee_amount)
				{
					$days = 365 * 10;   // 10 Years for Free Spl Category
				}

				// Automatically renew special listings = 365 days.
				foreach ($cid as $product_id)
				{
					$listing_ids[] = $this->helper->listing->extend($product_id, $seller_uid, $cat_id, $days, true);
				}

				$object = new stdClass;

				$object->seller_uid  = $seller_uid;
				$object->product_ids = $cid;
				$object->listing_ids = $listing_ids;

				$dispatcher = $this->helper->core->loadPlugins();
				$dispatcher->trigger('onContentAfterSave', array('com_sellacious.productlisting', $object, false));

				$cTitle = $this->helper->splCategory->getFieldValue($cat_id, 'title', 'Basic');

				if (count($cid) == 1)
				{
					$pTitle  = $this->helper->product->loadResult(array('id' => $cid, 'list.select' => 'a.title'));
					$message = JText::sprintf('COM_SELLACIOUS_PRODUCTLISTING_UPDATE_SUCCESS_LABELLED', $cTitle, $pTitle);
				}
				else
				{
					$message = JText::sprintf('COM_SELLACIOUS_PRODUCTLISTING_UPDATE_SUCCESS_PRODUCT_COUNT', $cTitle, count($cid));
				}

				$redirect = null;
			}
			else
			{
				$this->app->setUserState('com_sellacious.productlisting.products', $cid);
				$this->app->setUserState('com_sellacious.edit.productlisting.data', array('seller_uid' => $seller_uid, 'splcat_id' => $cat_id));

				$message = '';
				$redirect = JRoute::_('index.php?option=com_sellacious&view=productlisting', false);
			}

			$response = array(
				'message'  => $message,
				'data'     => null,
				'status'   => 1,
				'redirect' => $redirect,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);
		jexit();
	}

	/**
	 * Save the prices and stock
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid   = $this->input->get('cid', array(), 'array');
		$items = $this->input->get('jform', array(), 'array');

		$this->setRedirect($this->getReturnURL());

		if (!$this->helper->access->isSubscribed())
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_ACCESS_PREMIUM_NOT_ALLOWED'), 'error');

			return false;
		}

		if (count($cid) == 0 || count($items) == 0)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_PRODUCTS_NO_ITEM_SELECTED'), 'warning');

			return false;
		}

		$records = array();

		/** @var  SellaciousModelProduct  $model */
		$model = $this->getModel();

		foreach ($items as $item)
		{
			$key = (int) $item['product_id'] . ':' . (int) $item['seller_uid'];

			if (in_array($key, $cid))
			{
				$records[] = $item;
			}
		}

		try
		{
			$model->savePriceAndStock($records);
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return true;
	}

	/**
	 * Update the products cache
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function refreshCache()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect($this->getReturnURL());

		try
		{
			// If we are allowed to use exec for cli, we'd use it
			$disabled = array_map('trim', explode(',', ini_get('disable_functions')));
			$useExec  = is_callable('exec') && !in_array('exec', $disabled) && strtolower(ini_get('safe_mode')) != 1;

			if ($useExec)
			{
				$now     = JFactory::getDate();
				$ts      = $now->toSql();
				$config  = JFactory::getConfig();
				$userId  = (int) JFactory::getUser()->id;
				$logfile = $config->get('tmp_path') . '/s-cache-' . $ts . '.log';
				$history = $config->get('tmp_path') . '/s-cache.log';

				$pid = CacheHelper::executeCli($logfile, $userId);

				if ($pid)
				{
					$this->app->setUserState('com_sellacious.cache.state.pid', $pid);

					$entry = array('TS' => $ts, 'PID' => $pid, 'LOG' => basename($logfile));

					file_put_contents($history, json_encode($entry) . PHP_EOL, FILE_APPEND);

					$this->setMessage(JText::_('COM_SELLACIOUS_PRODUCTS_CACHE_STARTED'));

					return true;
				}
				else
				{
					$this->setMessage(JText::_('COM_SELLACIOUS_PRODUCTS_CACHE_START_FAILED'));

					return false;
				}
			}
			else
			{
				CacheHelper::buildCache();

				$this->setMessage(JText::_('COM_SELLACIOUS_PRODUCTS_CACHE_REBUILD_SUCCESS'));

				return true;
			}
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function publish()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid = $this->input->get('cid', array(), 'array');
		$data = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'pending' => -1, 'disapprove' => -3);
		$task = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Publish the items.
			try
			{
				$result = $model->publish($cid, $value);
				$errors = $model->getErrors();
				$nText  = null;

				if ($result)
				{
					if ($value === 1)
					{
						if ($errors)
						{
							$this->app->enqueueMessage(JText::plural($this->text_prefix . '_N_ITEMS_FAILED_PUBLISHING', count($cid)), 'error');
						}
						else
						{
							$nText = $this->text_prefix . '_N_ITEMS_PUBLISHED';
						}
					}
					elseif ($value === 0)
					{
						$nText = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
					}
					elseif ($value === 2)
					{
						$nText = $this->text_prefix . '_N_ITEMS_ARCHIVED';
					}
					elseif ($value === -1)
					{
						$nText = $this->text_prefix . '_N_ITEMS_PENDING_APPROVAL';
					}
					elseif ($value === -3)
					{
						$nText = $this->text_prefix . '_N_ITEMS_DISAPPROVED';
					}
					else
					{
						$nText = $this->text_prefix . '_N_ITEMS_TRASHED';
					}

					if ($nText !== null)
					{
						$this->setMessage(JText::plural($nText, count($cid)));
					}
				}

			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		$extension = $this->input->get('extension');
		$extensionURL = $extension ? '&extension=' . $extension : '';
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
	}

	/**
	 * Export csv of the products record
	 *
	 * @since   1.0.0
	 */
	function csv()
	{
		// Where is this used?
		$url = JRoute::_('index.php?option=com_sellacious&view=products&layout=csv', false);

		$this->helper->core->metaRedirect($url);

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=products', false));
	}
}
