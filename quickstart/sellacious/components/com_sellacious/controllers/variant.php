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
 * Variant controller class.
 */
class SellaciousControllerVariant extends SellaciousControllerForm
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_VARIANT';

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
		return $this->helper->access->check('variant.create');
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
		if ($this->helper->access->check('variant.edit'))
		{
			return true;
		}

		$me       = JFactory::getUser();
		$owned_by = $this->helper->variant->getFieldValue($data['id'], 'owned_by');

		if ($owned_by > 0 && $owned_by == $me->get('id'))
		{
			return $this->helper->access->check('variant.edit.own');
		}

		return false;
	}

	/**
	 * Method to save a record via Ajax request.
	 *
	 * @return  void
	 */
	public function saveAjax()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var SellaciousModelVariant $model */
		$model = $this->getModel();
		$data  = $this->input->post->get('jform', array(), 'array');

		try
		{
			// if (!empty($data['variant']) && !empty($data['variant_fields']))
			if (!empty($data['variant']))
			{
				$fields = ArrayHelper::getValue($data, 'variant_fields', array(), 'array');
				$data   = array_merge($data['variant'], array('fields' => $fields));
			}
			else
			{
				throw new Exception(JText::_($this->text_prefix . '_SAVE_ERROR_INSUFFICIENT_DATA'));
			}

			if (!$this->allowSave($data, 'id'))
			{
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			}

			$model->save($data);

			$state   = 1;
			$message = JText::_($this->text_prefix . '_SAVE_SUCCESS');
			$data    = $model->getState('variant.id');
		}
		catch (Exception $e)
		{
			$state   = 0;
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $e->getMessage());
			$data    = null;
		}

		echo json_encode(array('state' => $state, 'message' => $message, 'data' => $data));

		jexit();
	}

	/**
	 * Method to get a variant record via Ajax request.
	 *
	 * @return  void
	 */
	public function getItemAjax()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			$me  = JFactory::getUser();
			$pk  = $this->input->post->getInt('id');

			$this->helper->core->loadPlugins('sellacious');

			// Fixme: This state value read here may be unreliable at times, not sure!
			// Fixme+ However for now this is only called from product edit context, hence valid.
			$seller_uid = $this->app->getUserState('com_sellacious.edit.product.seller_uid');

			$variant = $this->helper->variant->getItem($pk);
			$price   = $this->helper->variant->getSellerAttributes($variant->id, $seller_uid);
			$product = $this->helper->product->getItem($variant->product_id);

			$filter1 = array(
				'table_name' => 'variants',
				'record_id'  => $variant->id,
				'context'    => 'images',
			);
			$image   = $this->helper->media->getFieldValue($filter1, 'path');

			$filterN = array(
				'list.select' => 'a.id, a.path, a.state, a.original_name',
				'table_name'  => 'variants',
				'context'     => 'images',
				'record_id'   => $variant->id,
			);

			$variant->fields        = $this->helper->variant->getSpecifications($variant->id, null, true);
			$variant->image         = $this->helper->media->getURL($image, true);
			$variant->images        = $this->helper->media->loadObjectList($filterN);
			$variant->price         = $price->price_mod;
			$variant->price_pc      = $price->price_mod_perc;
			$variant->product_title = $product->title;
			$variant->product_sku   = $product->local_sku;
			$variant->eproducts     = $this->helper->product->getEProductMedia($variant->product_id, $variant->id, $seller_uid);

			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onBeforeLoadVariant', array('com_sellacious.variant', &$variant));

			// If allowed then extend with edit form
			// Todo: Decouple this access check from here and layout, probably move to a helper function
			$isNew       = $variant->id == 0;
			$isOwner     = $variant->owned_by > 0 && ($variant->owned_by == $me->get('id'));
			$allowCreate = $this->helper->access->check('variant.create');
			$allowEdit   = $isNew ? $allowCreate : $this->helper->access->check('variant.edit')
				|| ($isOwner && $this->helper->access->check('variant.edit.own'));
			$allowDelete = $this->helper->access->check('variant.delete') || ($isOwner && $this->helper->access->check('variant.delete.own'));

			$args = new stdClass;

			$args->variant      = $variant;
			$args->seller_uid   = $seller_uid;
			$args->allow_edit   = $allowEdit;
			$args->allow_create = $allowCreate;
			$args->allow_delete = $allowDelete;

			$html = JLayoutHelper::render('com_sellacious.product.variant.row', $args);

			$data       = $variant;
			$data->html = preg_replace(array('|[\n\t]|', '|\s+|'), array('', ' '), $html);
			$state      = 1;
			$message    = '';
		}
		catch (Exception $e)
		{
			$data    = null;
			$state   = 0;
			$message = $e->getMessage();
		}

		echo json_encode(array('state' => $state, 'message' => $message, 'data' => $data));

		jexit();
	}

	/**
	 * Method to get a variant record via Ajax request.
	 *
	 * @return  void
	 */
	public function deleteAjax()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$pk = $this->input->post->getInt('id');

		try
		{
			$this->helper->variant->delete($pk);
			$state   = 1;
			$message = JText::plural($this->text_prefix . '_REMOVE_SUCCESS_N', 1);;
		}
		catch (Exception $e)
		{
			$variant = null;
			$state   = 0;
			$message = JText::sprintf($this->text_prefix . '_REMOVE_FAILED', $e->getMessage());
		}

		echo json_encode(array('state' => $state, 'message' => $message, 'data' => null));

		jexit();
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param  string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   12.2
	 */
	public function cancel($key = null)
	{
		$this->app->setUserState('com_sellacious.edit.product.data.seller_uid', null);

		return parent::cancel($key);
	}
}
