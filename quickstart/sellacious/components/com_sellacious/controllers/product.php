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
use Joomla\Utilities\ArrayHelper;
use Sellacious\Media\MediaHelper;
use Sellacious\Media\Upload\Uploader;

defined('_JEXEC') or die;

/**
 * Product controller class.
 *
 * @since   1.0.0
 */
class SellaciousControllerProduct extends SellaciousControllerForm
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PRODUCT';

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see     JControllerForm
	 *
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('setCategory', 'setType');
		$this->registerTask('setSeller', 'setType');
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
		// Fixme: apply correct rules here
		$actions  = array('basic', 'seller', 'pricing', 'shipping', 'related', 'seo',
			'basic.own', 'seller.own', 'pricing.own', 'shipping.own', 'related.own', 'seo.own');
		$editable = $this->helper->access->checkAny($actions, 'product.edit.');

		return $editable;
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 *                           (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   1.6
	 */
	public function edit($key = null, $urlVar = null)
	{
		$cid = $this->input->post->get('cid', array(), 'array');

		$recordId = count($cid) ? $cid[0] : $this->input->getString('id');

		if (!empty($recordId))
		{
			$entities = explode(':', $recordId);
			$this->app->setUserState('com_sellacious.edit.product.seller_uid', (int) @$entities[1]);
		}

		return parent::edit($key, $urlVar);
	}

	/**
	 * Provides autocomplete interface to javascript functions with certain filters
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function searchAjax()
	{
		$query      = $this->input->getString('q');
		$type       = $this->input->getInt('type');
		$seller_uid = $this->input->getInt('seller_uid');
		$offset     = $this->input->getInt('list_start');
		$limit      = $this->input->getInt('list_limit');

		$filters    = array(
			'keyword'    => $query,
			'type'       => $type,
			'seller_uid' => $seller_uid,
		);

		/** @var  SellaciousModelProduct  $model */
		$model = $this->getModel();
		$items = $model->search($filters, $offset, $limit, $more);

		$response = array('status' => 1, 'message' => '', 'data' => array('items' => $items, 'more' => $more));

		echo json_encode($response);
		jexit();
	}

	/**
	 * Provides autocomplete interface to javascript functions with certain filters
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function searchSellerAjax()
	{
		$query   = $this->input->getString('q');
		$product = $this->input->getString('p');
		$offset  = $this->input->getInt('list_start');
		$limit   = $this->input->getInt('list_limit');
		$filters = array(
			'keyword' => $query,
			'product' => $product,
		);

		/** @var  SellaciousModelProduct  $model */
		$model = $this->getModel();
		$items = $model->searchSeller($filters, $offset, $limit, $more);

		$response = array('status' => 1, 'message' => '', 'data' => array('items' => $items, 'more' => $more));

		echo json_encode($response);
		jexit();
	}

	/**
	 * Get a product object for the given product id, variant_id, seller_uid
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function getItemAjax()
	{
		$product_id = $this->input->getInt('product_id');
		$variant_id = $this->input->getInt('variant_id');
		$seller_uid = $this->input->getInt('seller_uid');

		try
		{
			if (!$product_id)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_ITEM_NOT_SELECTED'));
			}

			if (!$seller_uid)
			{
				$seller_uid = $this->helper->config->get('default_seller');
			}

			/** @var  SellaciousModelProduct $model */
			$model = $this->getModel('Product');
			$item  = $model->getProduct($product_id, $variant_id, $seller_uid);

			$response = array('state' => 1, 'message' => '', 'data' => $item);
		}
		catch (Exception $e)
		{
			$response = array('state' => 1, 'message' => $e->getMessage(), 'data' => null);
		}

		echo json_encode($response);

		jexit();
	}

	/**
	 * Provides autocomplete interface to javascript functions
	 * supported contexts are: title, manufacturer
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function autoComplete()
	{
		$query   = $this->input->get('query');
		$context = $this->input->get('context');

		/** @var  SellaciousModelProduct  $model */
		$model    = $this->getModel();
		$data     = $model->suggest($context, trim($query), null);
		$response = array('status' => 1, 'message' => '', 'data' => $data);

		echo json_encode($response);
		jexit();
	}

	/**
	 * Get details of given item
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getInfoAjax()
	{
		$context = $this->input->getString('context');
		$keys    = $this->input->get('id', array(), 'array');
		$keys    = ArrayHelper::toInteger($keys);

		/** @var  SellaciousModelProduct $model */
		$model    = $this->getModel();
		$items    = $model->suggest($context, '', $keys);
		$response = array('status' => 1, 'message' => '', 'data' => $items);

		echo json_encode($response);

		jexit();
	}

	/**
	 * Add a new placeholder (draft) e-product row
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function addEProductAjax()
	{
		// Todo: We need to only add for session matched product and seller id
		$product_id = $this->input->getInt('product_id');
		$variant_id = $this->input->getInt('variant_id');
		$seller_uid = $this->input->getInt('seller_uid');

		try
		{
			if (!JSession::checkToken('request'))
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			if (!$product_id || !$seller_uid)
			{
				throw new Exception(JText::_($this->text_prefix . '_EPRODUCT_DRAFT_NO_ITEM_SELECTED'));
			}

			// Todo: Check required access and config too
			$media = $this->helper->product->createEProductMedia($product_id, $variant_id, $seller_uid, 99);

			$response = array('status' => 1, 'message' => '', 'data' => $media);
		}
		catch (Exception $e)
		{
			$response = array('status' => 0, 'message' => $e->getMessage(), 'data' => null);
		}

		echo json_encode($response);

		jexit();
	}

	/**
	 * Upload an e-product file by ajax
	 *
	 * @throws  Exception
	 *
	 * @return  void
	 *
	 * @since   1.5.3
	 */
	public function uploadEProductAjax()
	{
		$control   = $this->input->get('control', 'jform');
		$record_id = $this->input->getInt('record_id');
		$context   = $this->input->getString('context');

		try
		{
			if (!JSession::checkToken('request'))
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			// Todo: add access check
			if (!$record_id || !$context)
			{
				throw new Exception(JText::_($this->text_prefix . '_EPRODUCT_UPLOAD_INSUFFICIENT_PARAMETERS'));
			}

			$eTypes   = array('image', 'document', 'archive', 'audio', 'video');
			$eTypes   = $this->helper->config->get('eproduct_file_type') ?: $eTypes;
			$uploader = new Uploader(array());

			$uploader->addType($eTypes, true);
			$uploader->allowUnsafe(Uploader::UNSAFE_OPT_ALLOW_FOBIDDEN_EXT_IN_CONTENT);

			$uploader->select($control, 1);

			$uploader->moveTo('images/com_sellacious/eproduct_media/' . $context . '/' . $record_id, '*-@@');
			$uploader->saveTo('eproduct_media', $context, $record_id);

			$files    = $uploader->getSelected();
			$file     = reset($files);
			$response = array(
				'message' => JText::_('COM_SELLACIOUS_MEDIA_FILE_UPLOADED_SUCCESSFULLY'),
				'status'  => $file->uploaded,
				'data'    => array('file' => $file),
			);
			echo json_encode($response);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'status'  => 0,
				'data'    => null,
			);
			echo json_encode($response);
		}

		jexit();
	}

	/**
	 * Add a new placeholder (draft) e-product row
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function removeEProductAjax()
	{
		try
		{
			if (!JSession::checkToken('request'))
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			/** @var  SellaciousModelProduct  $model */
			$id     = $this->input->getInt('id');
			$model  = $this->getModel();
			$result = $model->removeEProductMedia($id);

			$message  = JText::_($this->text_prefix . ($result ? '_EPRODUCT_REMOVE_SUCCESS' : '_EPRODUCT_REMOVE_FAILED'));
			$response = array('status' => $result, 'message' => $message, 'data' => null);
		}
		catch (Exception $e)
		{
			$response = array('status' => 0, 'message' => $e->getMessage(), 'data' => null);
		}

		echo json_encode($response);
		jexit();
	}

	/**
	 * Common function to simply update the form data and update session for it.
	 * Can be used in all contexts such as change of parent, type, category etc.
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function setType()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post = $this->input->get('jform', array(), 'array');

		if (strcasecmp($this->getTask(), 'setSeller') == 0)
		{
			// Reset all seller specific fields so that blank data can load for new selected seller
			unset($post['seller']);
			unset($post['prices']);
			unset($post['listing']);
		}

		$this->app->setUserState('com_sellacious.edit.product.data', $post);
		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=product&layout=edit', false));

		return true;
	}

	/**
	 * Function to switch language of the product.
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	public function setLanguage()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post = $this->input->get('jform', array(), 'array');

		/** @var  SellaciousModelProduct  $model */
		$model = $this->getModel();

		$productId = $model->setLanguage($post);

		if ($productId)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=product&layout=edit&id=' . $productId, false));
		}
		else
		{
			if ($post['id'] > 0)
			{
				$this->app->setUserState('com_sellacious.edit.product.assoc_id', $post['id']);
				$post['id'] = 0;
				$this->app->setUserState('com_sellacious.edit.product.id', 0);
			}

			$this->app->setUserState('com_sellacious.edit.product.data', $post);
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=product&layout=edit', false));
		}

		return true;
	}
}
