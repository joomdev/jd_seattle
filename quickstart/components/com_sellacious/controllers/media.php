<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Media\MediaHelper;

/**
 * list controller class
 *
 * @since  1.0.0
 */
class SellaciousControllerMedia extends SellaciousControllerBase
{
	/**
	 * @var   string  The prefix to use with controller messages.
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
	 * @param  string  $name
	 * @param  string  $prefix
	 * @param  array   $config
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
	 * Upload by ajax
	 *
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

		jexit();
	}

	/**
	 * Remove an uploaded file from reference and delete from server
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

		jexit();
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

		jexit();
	}

	/**
	 * Verify only the file existence for the file to be sent as download
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @see  download()
	 */
	public function downloadAjax()
	{
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$id = $this->input->getInt('id');

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

		jexit();
	}

	/**
	 * Initial the actual file download
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function download()
	{
		try
		{
			$id = $this->input->getString('id');

			if (is_numeric($id))
			{
				$file = $this->helper->media->getItem($id);

				if ($file->protected)
				{
					throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_FILE_IS_PROTECTED'));
				}

				$this->helper->media->downloadFile($file->path, $file->original_name, $file->type);
			}
			elseif (substr($id, 0, 4) === 'B64:')
			{
				if ($path = base64_decode(substr($id, 4)))
				{
					$this->helper->media->downloadFile($path, basename($path), MediaHelper::getMimeType(JPATH_SITE . '/' . $path, true));
				}
			}

			$this->setMessage(JText::_('COM_SELLACIOUS_ERROR_DOWNLOAD_FILE_NOT_FOUND'), 'warning');
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');

			return false;
		}

		$this->setRedirect($this->getRedirectURL());

		return true;
	}

	/**
	 * Get the redirect URL for this controller
	 *
	 * @return  string
	 *
	 * @since   1.5.1
	 */
	protected function getRedirectURL()
	{
		$view   = $this->input->get('view', null);
		$tmpl   = $this->input->get('tmpl', null);
		$layout = $this->input->get('layout', null);

		$view = !empty($view) ? '&view=' . $view : '';
		$tmpl = !empty($tmpl) ? '&tmpl=' . $tmpl : '';
		$lyt  = !empty($layout) ? '&layout=' . $layout : '';

		return JRoute::_('index.php?option=com_sellacious' . $view . $tmpl . $lyt, false);
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
