<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Product controller class.
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
	 * Switch the variant selection for the product page according to the selected variant specifications
	 *
	 * @return  bool
	 */
	public function switchVariant()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var JModelLegacy $model */
		$this->setRedirect($this->getReturnURL());

		$code  = $this->input->get('p');
		$valid = $this->helper->product->parseCode($code, $product_id, $variant_id, $seller_uid);

		if (!$valid)
		{
			$this->setMessage(JText::_($this->text_prefix . '_INVALID_ITEM_SELECTED'));

			return false;
		}

		$specs = $this->input->post->get('jform', array(), 'array');
		$specs = ArrayHelper::getValue($specs, 'variant_spec', array(), 'array');

		// Preload product fields for the getSpecifications call to save repetitive evaluating inside it.
		$vFields        = $this->helper->product->getFields($product_id, array('variant'));
		$variant_ids    = $this->helper->variant->loadColumn(array('list.select' => 'a.id', 'product_id' => $product_id));
		$specifications = $this->helper->variant->getProductSpecifications($product_id, $vFields, false);

		$variants = array();

		$variant         = new stdClass;
		$variant->id     = 0;
		$variant->fields = array_filter($specifications);
		$variants[0]     = $variant;

		foreach ($variant_ids as $vid)
		{
			$specifications  = $this->helper->variant->getSpecifications($vid, $vFields, false);

			$variant         = new stdClass;
			$variant->id     = $vid;
			$variant->fields = array_filter($specifications);
			$variants[$vid]  = $variant;
		}

		$filtered = $this->helper->variant->pick($specs, $variants, 1);

		// We didn't get an alternative combination for the selected filter value, now try just this value without a combination matching.
		if (count($filtered) == 0)
		{
			// This is the current product/variant's specification.
			if ($variant = ArrayHelper::getValue($variants, $variant_id))
			{
				// Lookup for the spec which was changed for this switching and make it the minimum requirement.
				$dSpecs = array();

				foreach ($specs as $fid => $fValue)
				{
					$vValue = ArrayHelper::getValue($variant->fields, $fid);

					if (is_array($vValue) ? !in_array($fValue, $vValue) : $vValue != $fValue)
					{
						$dSpecs[$fid] = $fValue;
					}
				}

				$filtered = $this->helper->variant->pick(array_filter($dSpecs), $variants, 1);
			}
		}

		if (count($filtered))
		{
			$variant    = reset($filtered);
			$variant_id = $variant->id;
		}
		else
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_PRODUCT_VARIANT_SPEC_NO_MATCH'));
		}

		// Clear 'edit' layout param from request
		$this->input->set('layout', '');
		$p_code = $this->helper->product->getCode($product_id, $variant_id, $seller_uid);
		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=product&p=' . $p_code, false));

		return true;
	}

	/**
	 * Save customer query for the specified product
	 *
	 * @since   1.2.0
	 */
	public function submitQuery()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var  SellaciousModelProduct  $model */
		$model = $this->getModel();
		$code  = $this->input->getString('p');
		$data  = $this->input->get('jform', array(), 'array');

		try
		{
			$query = ArrayHelper::getValue($data, 'query', array(), 'array');

			if (empty($query) || empty($code))
			{
				throw new Exception(JText::_($this->text_prefix . '_QUERY_FORM_INVALID_CONTENT'));
			}

			// todo: Validate against the query form instance
			$model->saveQuery($query, $code);

			$this->setRedirect('index.php?option=com_sellacious&view=product&layout=query&tmpl=component&sent=1&p=' . $code);
		}
		catch (Exception $e)
		{
			$this->setRedirect('index.php?option=com_sellacious&view=product&layout=query&tmpl=component&p=' . $code);

			JLog::add($e->getMessage(), JLog::WARNING);
		}
	}

	/**
	 * Save customer rating and review for the specified product
	 *
	 * @since  1.2.0
	 */
	public function saveRating()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$data       = $this->input->get('jform', array(), 'array');
		$context    = "$this->option.edit.$this->context.rating";
		$dispatcher = $this->helper->core->loadPlugins();

		// Redirect back to product page always
		$this->setRedirect($this->getReturnURL());

		if (empty($data['product_id']) || empty($data['seller_uid']) || !isset($data['variant_id']))
		{
			$this->setMessage(JText::_($this->text_prefix . '_RATING_INVALID_VALUES'), 'warning');

			return false;
		}

		try
		{
			$form = $this->helper->rating->getForm($data['product_id'], $data['variant_id'], $data['seller_uid']);

			// Filter and validate the form data.
			$data   = $form->filter($data);
			$return = $form->validate($data);

			// Check for an error.
			if ($return instanceof Exception)
			{
				throw $return;
			}

			// Check the validation results.
			if ($return === false)
			{
				// Get the validation messages from the form.
				foreach ($form->getErrors() as $message)
				{
					if ($message instanceof \Exception)
					{
						$this->app->enqueueMessage($message->getMessage(), 'warning');
					}
					else
					{
						$this->app->enqueueMessage($message, 'warning');
					}
				}

				// Save the data in the session.
				$this->app->setUserState($context . '.data', $data);

				return false;
			}

			$user = JFactory::getUser();

			if (!$user->guest)
			{
				$data['author_id']    = $user->id;
				$data['author_name']  = $user->name;
				$data['author_email'] = $user->email;
			}
			else
			{
				if (empty($data['author_name']) || empty($data['author_email']))
				{
					$this->setMessage(JText::_($this->text_prefix . '_RATING_USER_INFO_REQUIRED'), 'warning');

					return false;
				}

				$data['author_id'] = 0;
			}

			foreach (array('product', 'seller', 'packaging', 'shipment') as $type)
			{
				if (!empty($data[$type]) && is_array($data[$type]))
				{
					$record = $data[$type];

					$record['product_id']   = $data['product_id'];
					$record['variant_id']   = $data['variant_id'];
					$record['seller_uid']   = $data['seller_uid'];
					$record['author_id']    = $data['author_id'];
					$record['author_name']  = $data['author_name'];
					$record['author_email'] = $data['author_email'];
					$record['type']         = $type;
					$record['state']        = 1;

					$table = JTable::getInstance('Rating', 'SellaciousTable');
					$isNew = true;

					// Product is to handled differently as we need to overwrite previous review for it
					if ($type == 'product')
					{
						if ($user->guest)
						{
							$args = array('type' => $type, 'author_email' => $record['author_email']);
						}
						else
						{
							$args = array('type' => $type, 'author_id' => $record['author_id']);
						}

						// Select based solely on product id, ignore variant and seller
						$args['product_id'] = $record['product_id'];

						$table->load($args);
					}

					if ($table->get('id'))
					{
						$isNew = false;
					}

					$table->bind($record);
					$table->check();
					$table->store();

					if ($type != 'product')
					{
						// If non product review, this is a certified buyer
						$table->set('buyer', 1);
						$table->store();
					}
					elseif (!$user->guest)
					{
						// Mark as reviewed where pending order review for registered customers
						$db    = JFactory::getDbo();
						$sub   = $db->getQuery(true);
						$query = $db->getQuery(true);

						$sub->select('i.id')
							->from($db->qn('#__sellacious_order_items', 'i'))
							->where('i.product_id = ' . (int) $record['product_id'])
							->where('reviewed = 0')
							->join('INNER', $db->qn('#__sellacious_orders', 'o') . ' ON o.id = i.order_id')
							->where('o.customer_uid = ' . (int) $user->id);

						$pks = $db->setQuery($sub)->loadColumn();

						if (count($pks))
						{
							$query->update($db->qn('#__sellacious_order_items'))
								->set('reviewed = 1')
								->where('id IN (' . implode(', ', $pks) . ')');

							$db->setQuery($query)->execute();

							// If anything was pending, this is a certified buyer
							$table->set('buyer', 1);
							$table->store();
						}
					}

					$dispatcher->trigger('onContentAfterSave', array('com_sellacious.rating', $table, $isNew));
				}
			}
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');

			return false;
		}

		// Clear data from session
		$this->app->setUserState($context . '.data', null);

		$this->setMessage(JText::_($this->text_prefix . '_RATING_SAVE_SUCCESS'));

		return true;
	}

	/**
	 * Save customer question for the specified product
	 *
	 * @return   boolean  True or False depending on success of save question function.
	 *
	 * @since    1.6.0
	 *
	 * @throws   Exception
	 */
	public function saveQuestion()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$data = $this->app->input->get('jform', array(), 'array');

		// Redirect back to product page always
		$this->setRedirect($this->getReturnURL());

		if (empty($data['p_id']) || empty($data['s_uid']) || !isset($data['v_id']))
		{
			$this->setMessage(JText::_($this->text_prefix . '_QUESTION_INVALID_VALUES'), 'warning');

			return false;
		}

		if (empty($data['question']))
		{
			$this->setMessage(JText::_($this->text_prefix . '_QUESTION_COMMENT_REQUIRED'), 'warning');

			return false;
		}

		try
		{
			$form = $this->helper->product->getQuestionForm($data['p_id'], $data['v_id'], $data['s_uid']);

			// Filter and validate the form data.
			$data   = $form->filter($data);
			$return = $form->validate($data);

			// Check for an error.
			if ($return instanceof Exception)
			{
				throw $return;
			}

			// Check the validation results.
			if ($return === false)
			{
				// Get the validation messages from the form.
				foreach ($form->getErrors() as $message)
				{
					$this->app->enqueueMessage($message);
				}

				return false;
			}

			$user = JFactory::getUser();

			if (!$user->guest)
			{
				$data['created_by']       = $user->id;
				$data['questioner_name']  = $user->name;
				$data['questioner_email'] = $user->email;
			}
			else
			{
				if (empty($data['questioner_name']) || empty($data['questioner_email']))
				{
					$this->setMessage(JText::_($this->text_prefix . '_QUESTION_USER_INFO_REQUIRED'), 'warning');

					return false;
				}

				$data['created_by'] = 0;
			}

			/** @var   SellaciousModelProduct  $model */
			$model  = $this->getModel();
			$model->saveQuestion($data);
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');

			return false;
		}

		$this->setMessage(JText::_($this->text_prefix . '_QUESTION_SAVE_SUCCESS'));

		return true;
	}

	/**
	 * Method to download an eproduct file using its hotlink, if enabled.
	 *
	 * @return  bool
	 *
	 * @since   1.5.3
	 */
	public function downloadFile()
	{
		$mediaId = $this->input->getInt('id');

		try
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious'));

			$filter = array(
				'list.from'   => '#__sellacious_eproduct_media',
				'id'          => $mediaId,
				'state'       => 1,
			);
			$media  = $this->helper->product->loadObject($filter);

			if (!$media)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_MEDIA_NOT_FOUND'));
			}

			if (!$media->hotlink)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_MEDIA_HOTLINK_DISABLED'));
			}

			$filter = array(
				'table_name'  => 'eproduct_media',
				'record_id'   => $media->id,
				'state'       => 1,
			);
			$file = $this->helper->media->loadObject($filter);

			if (!$file || !is_file(JPATH_SITE . '/' . $file->path))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_MEDIA_NOT_FOUND'));
			}

			$this->helper->order->logDownload(-1, $file);

			$this->helper->media->download($file->id);

			return true;
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');

			return false;
		}
	}
}
