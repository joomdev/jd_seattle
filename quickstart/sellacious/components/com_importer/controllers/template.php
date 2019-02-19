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
use Sellacious\Import\AbstractImporter;
use Sellacious\Import\ImportHelper;

/**
 * Import template controller class.
 *
 * @since   1.5.2
 */
class ImporterControllerTemplate extends SellaciousControllerForm
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_IMPORTER_TEMPLATE';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   1.5.2
	 */
	public function getModel($name = 'Template', $prefix = 'ImporterModel', $config = null)
	{
		return parent::getModel($name, $prefix, $config);
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
	 * @since   1.5.2
	 */
	protected function allowAdd($data = array())
	{
		return $this->helper->access->check('template.create', null, 'com_importer');
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   1.5.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		if ($this->helper->access->check('template.edit', null, 'com_importer'))
		{
			return true;
		}

		/** @var  \ImporterModelTemplate  $model */
		$model    = $this->getModel();
		$template = $model->getItem($data['id']);

		if (!$template || !$template->get('id'))
		{
			return false;
		}

		if ($this->helper->access->check('template.edit', $template->get('import_type'), 'com_importer'))
		{
			return true;
		}

		if ($this->helper->access->check('template.edit.own') && $template->get('created_by') == JFactory::getUser()->id)
		{
			return true;
		}

		return false;
	}

	/**
	 * Save a column alias for the import CSV
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function saveAjax()
	{
		$this->validateAjaxToken();

		try
		{
			$post    = $this->input->get('jform', array(), 'array');
			$source  = ArrayHelper::getValue($post, 'source', '', 'string');
			$name    = ArrayHelper::getValue($post, 'name', '', 'string');
			$aliases = ArrayHelper::getValue($post, 'alias', array(), 'array');

			if (!$name || !$source || !$aliases)
			{
				throw new Exception(JText::_('COM_IMPORTER_IMPORT_TEMPLATE_SAVE_PARAMETER_MISSING'));
			}

			/** @var  \ImporterModelTemplate  $model */
			$model = $this->getModel();
			$me    = JFactory::getUser();

			$model->saveTemplate($source, $name, $aliases, $me->id);

			$response = array(
				'state'   => 1,
				'message' => JText::sprintf('COM_IMPORTER_IMPORT_TEMPLATE_SAVE_SUCCESS', $name),
				'data'    => null,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => JText::sprintf('COM_IMPORTER_IMPORT_TEMPLATE_SAVE_FAILED', $e->getMessage()),
				'data'    => null,
			);
		}

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Rename an import template
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function renameAjax()
	{
		$this->validateAjaxToken();

		try
		{
			/** @var  \ImporterModelTemplate  $model */
			$model = $this->getModel();
			$id    = $this->input->getInt('id');
			$title = $this->input->getString('title');

			if (!$id || !$title)
			{
				throw new Exception(JText::_('COM_IMPORTER_IMPORT_TEMPLATE_RENAME_PARAMETER_INVALID'));
			}

			$template = $model->getItem($id);

			if (!$this->helper->access->check('template.edit', $id, 'com_importer') &&
				!($this->helper->access->check('template.edit.own', $id, 'com_importer') && $template->get('created_by') == JFactory::getUser()->id))
			{
				throw new Exception(JText::_('COM_IMPORTER_IMPORT_TEMPLATE_ACCESS_DENIED'));
			}

			$model->rename($id, $title);

			$response = array(
				'state'   => 1,
				'message' => JText::_('COM_IMPORTER_IMPORT_TEMPLATE_RENAME_SUCCESS'),
				'data'    => null,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => JText::sprintf('COM_IMPORTER_IMPORT_TEMPLATE_RENAME_FAILED', $e->getMessage()),
				'data'    => null,
			);
		}

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Remove an import template from database
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function removeAjax()
	{
		$this->validateAjaxToken();

		try
		{
			/** @var  \ImporterModelTemplate  $model */
			$model = $this->getModel();
			$id    = $this->input->getInt('id');

			if (!$id)
			{
				throw new Exception(JText::_('COM_IMPORTER_IMPORT_TEMPLATE_REMOVE_PARAMETER_INVALID'));
			}

			$template = $model->getItem($id);

			if (!$this->helper->access->check('template.delete', $id, 'com_importer') &&
				!($this->helper->access->check('template.delete.own', $id, 'com_importer') && $template->get('created_by') == JFactory::getUser()->id))
			{
				throw new Exception(JText::_('COM_IMPORTER_IMPORT_TEMPLATE_ACCESS_DENIED'));
			}

			$model->remove($id);

			$response = array(
				'state'   => 1,
				'message' => JText::_('COM_IMPORTER_IMPORT_TEMPLATE_REMOVE_SUCCESS'),
				'data'    => null,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => JText::sprintf('COM_IMPORTER_IMPORT_TEMPLATE_REMOVE_FAILED', $e->getMessage()),
				'data'    => null,
			);
		}

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Method to set the default category for a category type.
	 *
	 * @since   1.5.2
	 */
	public function csvTemplate()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$dl         = $this->input->getInt('dl');
		$templateId = $this->input->getInt('template_id');
		$handler    = $this->input->getCmd('handler');
		$params     = $this->input->get('params', array(), 'array');

		$this->setRedirect($this->getReturnURL());

		try
		{
			if ($dl)
			{
				/** @var  AbstractImporter  $importer */
				$importer = ImportHelper::getImporter($handler);

				foreach ($params as $option => $value)
				{
					$importer->setOption($option, $value);
				}

				$columns  = $importer->getColumns();

				$template = ImportHelper::getTemplate($templateId);

				if ($templateId && $template)
				{
					$columns = array_intersect_key($template->mapping, array_flip($columns));
				}

				if (headers_sent($file, $line))
				{
					throw new Exception(JText::sprintf('COM_IMPORTER_HEADERS_ALREADY_SENT_AT', $file, $line));
				}

				header('content-type: text/csv');
				header('content-disposition: attachment; filename="' . $handler . '-import-template.csv"');
				header("Pragma: no-cache");
				header("Expires: 0");

				$stdout = fopen('php://output', 'w');
				fputcsv($stdout, $columns);
				fclose($stdout);

				jexit();
			}
			else
			{
				$token = JSession::getFormToken();
				$uri   = new JUri('index.php?option=com_importer&task=template.csvTemplate');

				$uri->setVar('handler', $handler);
				$uri->setVar('template_id', $templateId);
				$uri->setVar('dl', 1);
				$uri->setVar($token, 1);

				$this->helper->core->metaRedirect($uri->toString());

				$this->setMessage(JText::_('COM_IMPORTER_IMPORT_CSV_TEMPLATE_DOWNLOAD_INITIATED'));
			}
		}
		catch (SellaciousExceptionPremium $e)
		{
			$this->setMessage($e->getMessage(), 'premium');

			return false;
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Checks for a form token in the ajax request.
	 *
	 * @param   string  $method  The request method in which to look for the token key.
	 *
	 * @return  void  Aborts request with invalid token response on invalid CSRF token
	 *
	 * @since   1.5.2
	 */
	protected function validateAjaxToken($method = 'post')
	{
		if (!JSession::checkToken($method))
		{
			$response = array(
				'state'   => 0,
				'message' => JText::_('JINVALID_TOKEN'),
				'data'    => null,
			);
			echo json_encode($response);

			$this->app->close();
		}
	}
}
