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
use Joomla\CMS\Form\Form;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Import\AbstractImporter;
use Sellacious\Import\ImportHelper;
use Sellacious\Import\ImportRecord;
use Sellacious\Media\Upload\Uploader;

defined('_JEXEC') or die;

JLoader::import('sellacious.loader');

/**
 * Plugin to support imports for sellacious
 *
 * @subpackage  Sellacious Shipment
 *
 * @since   1.5.2
 */
abstract class SellaciousPluginImporter extends SellaciousPlugin
{
	/**
	 * The temporary directory where the uploaded import source files should be stored
	 *
	 * @var    string
	 *
	 * @since   1.5.2
	 */
	protected $tmpPath;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		$this->tmpPath = JFactory::getConfig()->get('tmp_path');

		if (!is_writable($this->tmpPath))
		{
			$this->tmpPath = JPATH_SITE . '/tmp';
		}

		JTable::addIncludePath(JPATH_SELLACIOUS . '/components/com_importer/tables');
	}

	/**
	 * Adds additional fields to the sellacious field editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.5.2
	 */
	public function onContentPrepareForm($form, $data)
	{
		parent::onContentPrepareForm($form, $data);

		// Inject import configuration into edit form.
		if ($form instanceof JForm)
		{
			$name    = $form->getName();
			$subject = is_array($data) ? ArrayHelper::toObject($data) : $data;

			if ($name == 'com_importer.template' && isset($subject->import_type))
			{
				$formPath = $this->pluginPath . '/forms/config_' . $subject->import_type . '.xml';

				$form->loadFile($formPath, false);
			}
		}

		return true;
	}

	/**
	 * Method to get a list of import templates
	 *
	 * @param   string  $handler  The type of import
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.2
	 */
	public function getTemplates($handler = null)
	{
		try
		{
			return ImportHelper::getTemplates($handler, JFactory::getUser()->id, true);
		}
		catch (Exception $e)
		{
			JLog::add(JText::_('LIB_SELLACIOUS_IMPORTER_ERROR_LOAD_TEMPLATES', $e->getMessage()), JLog::WARNING);

			return array();
		}
	}

	/**
	 * Method to get a list of import templates
	 *
	 * @var   string  $key  The state key to retrieve
	 *
	 * @return  mixed
	 *
	 * @since   1.5.2
	 *
	 * @deprecated   Use getActive() directly
	 */
	public function getState($key)
	{
		$import = $this->getActiveImport();

		return $import && property_exists($import, $key) ? $import->$key : null;
	}

	/**
	 * Method to get the active import job record
	 *
	 * @return  ImportRecord
	 *
	 * @since   1.6.1
	 */
	public function getActiveImport()
	{
		static $import = null;

		if (!$import)
		{
			try
			{
				if (class_exists('SellaciousImporterCli'))
				{
					$input    = new \Joomla\Input\Cli;
					$importId = $input->getInt('import_id');
				}
				else
				{
					$importId = $this->app->input->getInt('id');
				}

				$import = ImportHelper::getImport($importId);
			}
			catch (Exception $e)
			{
			}
		}

		return $import;
	}

	/**
	 * Method to store a single uploaded file to desired destination
	 *
	 * @param   string    $control     The form control name in dot notation (e.g. – jform.import_file)
	 * @param   string[]  $extensions  Allowed file extensions
	 *
	 * @return  string  The file site relative path
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	protected function uploadFile($control, $extensions)
	{
		$now = JFactory::getDate()->format('Ymd-His-T');

		$uploader = new Uploader($extensions);
		$uploader->select($control, 1);
		$uploader->moveTo($this->tmpPath . '/import-stage', '@@/import-' . $now, false);

		$files = $uploader->getSelected();

		if (!($file = reset($files)) || !$file->uploaded)
		{
			throw new Exception('Upload failed.');
		}

		return $file->location;
	}

	/**
	 * Get an instance of active importer object
	 *
	 * @return  AbstractImporter
	 *
	 * @since   1.5.2
	 */
	protected function getImporter()
	{
		$importer = null;

		try
		{
			$import = $this->getActiveImport();

			if ($import)
			{
				$options  = $import->options->toArray();
				$importer = ImportHelper::getImporter($import->handler);

				$importer->load($import->path);

				foreach ($options as $key => $value)
				{
					$importer->setOption($key, $value);
				}
			}
		}
		catch (Exception $e)
		{
			// But we should detect it earlier! How?
			if (!class_exists('SellaciousImporterCli'))
			{
				$this->app->enqueueMessage($e->getMessage(), 'error');
				$this->app->redirect(JRoute::_('index.php?option=com_importer'));
			}
		}

		return $importer;
	}

	/**
	 * Load the config form for the import plugin configuration
	 *
	 * @param   string    $handler   The handler name
	 * @param   stdClass  $template  The import template object
	 *
	 * @return  Form
	 *
	 * @since   1.5.2
	 */
	protected function getConfigForm($handler, $template)
	{
		$form = null;

		if (isset($template->override) && (int) $template->override === 1)
		{
			JFormHelper::addFormPath($this->pluginPath . '/forms');

			$form = JForm::getInstance($this->pluginName . '.import.' . $handler, 'config_' . $handler);

			if ($form)
			{
				$form->bind($template);
			}
		}

		return $form;
	}
}
