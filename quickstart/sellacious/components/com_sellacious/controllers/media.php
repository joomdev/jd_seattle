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

/**
 * list controller class
 *
 * @since  1.0.0
 */
class SellaciousControllerMedia extends SellaciousControllerBase
{
	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_MEDIA';

	/**
	 * Constructor.
	 *
	 * @param  array $config An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  3.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublishAjax', 'publishAjax');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name
	 * @param   string  $prefix
	 * @param   array   $config
	 *
	 * @return  JModelLegacy
	 *
	 * @since  1.0.0
	 */
	public function getModel($name = 'Media', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Create a new folder
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function addFolder()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$record = $this->input->post->get('jform', array(), 'array');

		$this->setRedirect($this->getRedirectURL());

		try
		{
			if ($record['newfolder'] == '')
			{
				throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_CREATE_FOLDER_EMPTY_NAME'));
			}

			/** @var SellaciousModelMedia $model */
			$model = $this->getModel();
			$model->addFolder($record['newfolder']);
		}
		catch (Exception $e)
		{
			$this->app->setUserState('com_sellacious.edit.media.newfolder', $record);
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		// clear session state
		$this->app->setUserState('com_sellacious.edit.media.newfolder', null);
		$this->setMessage(JText::_('COM_SELLACIOUS_MEDIA_CREATE_FOLDER_SUCCESS'));

		return true;
	}

	/**
	 * Upload a file
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function upload()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$data = $this->input->post->get('jform', array(), 'array');

		$this->setRedirect($this->getRedirectURL());

		try
		{
			/** @var SellaciousModelMedia $model */
			$model = $this->getModel();
			$count = $model->upload($data);
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		$this->setMessage(JText::sprintf('COM_SELLACIOUS_MEDIA_UPLOAD_SUCCESS_N', $count));

		return true;
	}

	/**
	 * Delete a file
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$files = $this->input->post->get('delete', array(), 'array');
		$total = count($files);

		$this->setRedirect($this->getRedirectURL());

		if ($total == 0)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_MEDIA_NO_ITEM_SELECTED_TO_DELETE'), 'notice');
		}

		try
		{
			/** @var SellaciousModelMedia $model */
			$model = $this->getModel();
			$model->delete($files);
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		if (count($files) == $total)
		{
			$this->setMessage(JText::plural('COM_SELLACIOUS_MEDIA_DELETE_SUCCESS_N', $total));
		}
		else
		{
			$this->setMessage(JText::plural('COM_SELLACIOUS_MEDIA_DELETE_SUCCESS_M_OF_N', count($files), $total), 'notice');
		}

		return true;
	}

	/**
	 * Get the redirect URL for this controller
	 *
	 * @return  string
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function getRedirectURL()
	{
		$label  = $this->input->getString('label', null);
		$tmpl   = $this->input->get('tmpl', null);
		$layout = $this->input->get('layout', null);

		$lbl  = !empty($label) ? '&label=' . $label : '';
		$tmpl = !empty($tmpl) ? '&tmpl=' . $tmpl : '';
		$lyt  = !empty($layout) ? '&layout=' . $layout : '';

		return JRoute::_('index.php?option=com_sellacious&view=media' . $lbl . $tmpl . $lyt, false);
	}

	/**
	 * Upload by ajax
	 *
	 * @throws  Exception
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function uploadAjax()
	{
		// Image assignment to asset context
		$control   = $this->input->get('control', 'jform');
		$tbl_name  = $this->input->getString('table');
		$record_id = $this->input->getInt('record_id');
		$context   = $this->input->getString('context');
		$type      = $this->input->getString('type', null);
		$rename    = $this->input->getBool('rename', false);
		$limit     = $this->input->getInt('limit', null);
		$data      = $this->input->get('jform', array(), 'array');

		try
		{
			if (!$this->helper->access->checkMediaAccess($tbl_name, $context, $record_id, 'media.create'))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));
			}

			if (empty($tbl_name) || empty($record_id) || empty($context))
			{
				throw new Exception(JText::_($this->text_prefix . '_INSUFFICIENT_PARAMETERS'));
			}

			$folder  = $this->helper->media->getBaseDir($tbl_name . '/' . $context . '/' . $record_id);
			$options = array(
				'ignore'   => false,
				'type'     => $type,
				'rename'   => $rename,
				'table'    => $tbl_name,
				'record'   => $record_id,
				'context'  => $context,
				'limit'    => $limit ? $limit : false,
				'filename' => '*',
			);

			$result = $this->helper->media->upload($folder, $control, $options);

			$info   = ArrayHelper::getValue($data, 'data', array(), 'array');
			$errors = $this->processUpload($result, $info);

			if (count($errors))
			{
				throw new Exception(implode('<br/>', $errors));
			}

			echo json_encode(array('message' => JText::_('COM_SELLACIOUS_MEDIA_FILE_UPLOADED_SUCCESSFULLY'), 'status' => 1, 'data' => $result));
		}
		catch (Exception $e)
		{
			echo json_encode(array('message' => $e->getMessage(), 'status' => 0, 'data' => null));
		}

		$this->app->close();
	}

	/**
	 * Crop the image via Ajax request
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function cropAjax()
	{
		// todo: verify this
		$image_id  = $this->input->getInt('img');
		$selection = $this->input->get('selection', array(), 'array');

		try
		{
			$record = $this->helper->media->getItem($image_id);

			if ($record->id == 0)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ERROR_IMAGE_SOURCE_MISSING'));
			}
			elseif ($this->helper->access->checkMediaAccess($record->table_name, $record->context, $record->record_id, 'media.edit'))
			{
				$cropped = $this->helper->media->crop($image_id, $selection, true);
				$return  = array('message' => JText::_('COM_SELLACIOUS_MEDIA_IMAGE_CROPPED_SUCCESSFULLY'), 'status' => 1, 'data' => $cropped);
			}
			else
			{
				$return = array('message' => JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'status' => 0, 'data' => null);
			}
		}
		catch (Exception $e)
		{
			$return = array('message' => $e->getMessage(), 'status' => 0);
		}

		echo json_encode($return);

		$this->app->close();
	}

	/**
	 * Remove an uploaded file from reference and delete from server
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function removeAjax()
	{
		$imageId = $this->input->getInt('img');

		try
		{
			$record = $this->helper->media->getItem($imageId);

			if ($record->id == 0)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ERROR_IMAGE_SOURCE_MISSING'));
			}

			if (!$this->helper->access->checkMediaAccess($record->table_name, $record->context, $record->record_id, 'media.delete'))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));
			}

			if (!$this->helper->media->remove($imageId))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_FILE_REMOVE_OPERATION_FAILED'));
			}

			echo json_encode(array('message' => JText::_('COM_SELLACIOUS_MEDIA_FILE_REMOVED_SUCCESSFULLY'), 'status' => 1, 'data' => true));
		}
		catch (Exception $e)
		{
			echo json_encode(array('message' => $e->getMessage(), 'status' => 0, 'data' => null));
		}

		$this->app->close();
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function publishAjax()
	{
		// Check for request forgeries
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$tasks = array('publishAjax' => 1, 'unpublishAjax' => 0, 'archiveAjax' => 2, 'trashAjax' => -2, 'reportAjax' => -3);
		$task  = $this->getTask();
		$state = ArrayHelper::getValue($tasks, $task, 0, 'int');

		$cid = $this->input->get('cid', array(), 'array');

		// Make sure the item ids are integers
		$cid = ArrayHelper::toInteger($cid);

		// Publish the items.
		try
		{
			if (empty($cid))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));
			}

			foreach ($cid as $id)
			{
				$table = SellaciousTable::getInstance('Media');
				$table->load($id);

				if ($table->get('id') == 0)
				{
					JLog::add(JText::_('COM_SELLACIOUS_ERROR_IMAGE_SOURCE_MISSING'), JLog::NOTICE);
				}

				if (!$this->helper->access->checkMediaAccess($table->get('table_name'), $table->get('context'), $table->get('record_id'), 'media.edit.state'))
				{
					throw new Exception(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));
				}
			}

			$table = SellaciousTable::getInstance('Media');
			$table->publish($cid, $state);

			$texts = array('UNPUBLISHED', 'PUBLISHED', 'ARCHIVED');
			$text  = $this->text_prefix . '_N_ITEMS_' . ArrayHelper::getValue($texts, $state, 'TRASHED', 'string');

			echo json_encode(array('message' => JText::plural($text, count($cid)), 'status' => 1, 'data' => null));
		}
		catch (Exception $e)
		{
			echo json_encode(array('message' => $e->getMessage(), 'status' => 0, 'data' => null));
		}

		$this->app->close();
	}

	/**
	 * Verify only the file existence for the file to be sent as download
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 *
	 * @see  download()
	 */
	public function downloadAjax()
	{
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$id  = $this->input->getInt('id');

		try
		{
			$item = $this->helper->media->getItem($id);

			if ($item->protected)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_FILE_IS_PROTECTED'));
			}

			if (!is_file(JPATH_SITE . '/' . $item->path))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_FILE_NOT_FOUND'));
			}

			echo json_encode(array('message' => '', 'status' => 1));
		}
		catch (Exception $e)
		{
			echo json_encode(array('message' => $e->getMessage(), 'status' => 0));
		}

		$this->app->close();
	}

	/**
	 * Method to sync media information in database with filesystem
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.4.7
	 */
	public function syncAjax()
	{
		try
		{
			$this->helper->media->purgeMissing();
			$this->helper->media->syncFromFilesystem();

			echo json_encode(array('message' => JText::_('COM_SELLACIOUS_MEDIA_SYNC_FILESYSTEM_SUCCESS'), 'state' => 1));
		}
		catch (Exception $e)
		{
			echo json_encode(array('message' => $e->getMessage(), 'state' => 0));
		}

		$this->app->close();
	}

	/**
	 * Initial the actual file download
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function download()
	{
		$id  = $this->input->getInt('id');

		try
		{
			$item = $this->helper->media->getItem($id);

			if ($item->protected)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_FILE_IS_PROTECTED'));
			}

			$this->helper->media->download($id);
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		$this->setRedirect($this->getRedirectURL());

		return true;
	}

	/**
	 * Check for an upload error from media upload method return value.
	 * Array nesting should be same for uploads and the data.
	 *
	 * @param   array  $uploads  Response array received from media helper
	 * @param   array  $data     Additional data to add to the uploaded file record
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function processUpload($uploads, $data)
	{
		static $errors = array();

		if ($uploads === false)
		{
			$errors[] = JText::_('COM_SELLACIOUS_MEDIA_ERROR_UPLOAD_PARTIAL');
		}
		elseif ($uploads instanceof Exception)
		{
			$errors[] = $uploads->getMessage();
		}
		elseif (is_array($uploads))
		{
			foreach ($uploads as $k => $row)
			{
				$value = ArrayHelper::getValue($data, $k);
				$this->processUpload($row, $value);
			}
		}
		elseif ($uploads instanceof JTable && is_array($data) && count($data))
		{
			$uploads->bind($data);
			$uploads->check();
			$uploads->store();
		}

		return $errors;
	}
}
