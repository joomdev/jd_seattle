<?php
/**
 * @version     1.6.1
 * @package     sellacious.plugin
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\Prices;
use Sellacious\Cache\Products;
use Sellacious\Cache\Specifications;
use Sellacious\Export\OrdersExporter;
use Sellacious\Export\ProductsExporter;
use Sellacious\Import\AbstractImporter;
use Sellacious\Import\ImportHandler;
use Sellacious\Import\ImportHelper;
use Sellacious\Import\ImportRecord;
use Sellacious\Utilities\Timer;

jimport('sellacious.loader');
jimport('sellacious_importer.loader');

/**
 * Sellacious Importer plugin.
 *
 * @since  1.4.7
 */
class PlgSystemSellaciousImporter extends SellaciousPluginImporter
{
	/**
	 * Whether this class has a configuration to inject into sellacious configurations
	 *
	 * @var    bool
	 *
	 * @since  1.4.0
	 */
	protected $hasConfig = true;

	/**
	 * List of handlers supported by this plugin
	 *
	 * @var    ImportHandler[]
	 *
	 * @since  1.6.0
	 */
	protected $handlers = array();

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

		JLoader::registerNamespace('Sellacious', __DIR__ . '/libraries');
	}

	/**
	 * This method imports any CSV file found in a designated folder for this
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function onAfterRoute()
	{
		$this->folderImport();

		$lang = JFactory::getLanguage();
		$lang->load('lib_importer', JPATH_LIBRARIES . '/sellacious_importer');
		$lang->load('lib_importer', JPATH_BASE);
	}

	/**
	 * Returns handlers to the importable entities that will be managed by this plugin
	 *
	 * @param   string           $context    The calling context, must be 'com_importer.import' to effect
	 * @param   ImportHandler[]  &$handlers  ByRef, associative array of handlers
	 *
	 * @return  bool
	 *
	 * @since   1.5.2
	 */
	public function onCollectHandlers($context, array &$handlers)
	{
		if ($context == 'com_importer.import')
		{
			if (!$this->handlers)
			{
				$this->handlers['products']   = new ImportHandler('products', JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_API_IMPORT_PRODUCTS'));
				$this->handlers['categories'] = new ImportHandler('categories', JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_API_IMPORT_CATEGORIES'));
				$this->handlers['images']     = new ImportHandler('images', JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_API_IMPORT_IMAGES'), false);
			}

			foreach ($this->handlers as $name => $handler)
			{
				$handlers[$name] = $handler;
			}
		}

		return true;
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
		if ($form instanceof JForm)
		{
			$name    = $form->getName();
			$subject = is_array($data) ? ArrayHelper::toObject($data) : $data;

			if ($name == 'com_importer.template' && isset($subject->import_type) && $subject->import_type == 'images')
			{
				$form->removeField('mapping_note');
				$form->removeField('mapping');
			}
		}

		return parent::onContentPrepareForm($form, $data);
	}

	/**
	 * Handles the file upload for the supported handlers
	 *
	 * @param   string  $context  The calling context, must be 'com_importer.import' to effect
	 * @param   string  $handler  The handler name that is called for import
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function onImportUploadSource($context, $handler)
	{
		if ($context == 'com_importer.import')
		{
			if (in_array($handler, array('products', 'categories')))
			{
				$this->setupCsvImport($handler);
			}
			elseif ($handler == 'images')
			{
				$this->setupImagesImport($handler);
			}
		}

		return true;
	}

	/**
	 * Handles setting import parameters for the active import session
	 *
	 * @param   string  $context  The calling context, must be 'com_importer.import' to effect
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function onImportSetOptions($context)
	{
		if ($context !== 'com_importer.import')
		{
			return true;
		}

		$import = $this->getActiveImport();

		if (!$import)
		{
			return true;
		}

		$handlers = array();
		$this->onCollectHandlers($context, $handlers);

		if (!array_key_exists($import->handler, $handlers))
		{
			return true;
		}

		// Request may override selected template id
		$import->template = $this->app->input->getInt('template_id', $import->template);
		$template         = ImportHelper::getTemplate($import->template);

		if ($import->handler === 'products')
		{
			// Alias override can always be allowed as we already have limited the columns using the template mapping
			$options = array();
			$mapping = $this->app->input->get('alias', array(), 'array');

			if (!$template || $template->override)
			{
				$options = $this->app->input->get('params', array(), 'array');
			}

			/** @var  AbstractImporter  $importer */
			$importer = ImportHelper::getImporter($import->handler);
			$importer->load($import->path);
			$importer->setColumnsAlias($mapping);

			// There was no validation errors in the import handler, lets update options
			$import->mapping = json_encode($mapping);
			$import->options = json_encode($options);

			$import->save();
		}
		elseif ($import->handler === 'categories')
		{
			// Alias override can always be allowed as we already have limited the columns using the template mapping
			$options = array();
			$mapping = $this->app->input->get('alias', array(), 'array');
			$fields  = $this->app->input->get('fields', array(), 'array');

			if (!$template || $template->override)
			{
				$options = $this->app->input->get('params', array(), 'array');
			}

			/** @var  AbstractImporter  $importer */
			$importer = ImportHelper::getImporter($import->handler);
			$importer->load($import->path);
			$importer->setColumnsAlias($mapping);

			// There was no validation errors in the import handler, lets update options
			$options = new Registry($options);
			$options->set('fields', $fields);

			$import->mapping = json_encode($mapping);
			$import->options = json_encode($options);

			$import->save();
		}
		elseif ($import->handler === 'images')
		{
			// Templates not applicable for images importer
			$options = $this->app->input->get('params', array(), 'array');

			/** @var  AbstractImporter  $importer */
			$importer = ImportHelper::getImporter($import->handler);
			$importer->load($import->path);

			// There was no validation errors in the import handler, lets update options
			$import->options = json_encode($options);

			$import->save();
		}

		return true;
	}

	/**
	 * Renders the page for a specific handler that will be managed by this plugin
	 *
	 * @param   string  $context  The calling context, must be 'com_importer.import' to effect
	 * @param   string  $handler  The handler name for which the layout should be rendered
	 * @param   mixed   $data     The data to be used by the layout
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function onImportRenderLayout($context, $handler, $data = null)
	{
		$handlers = array();
		$this->onCollectHandlers($context, $handlers);

		if ($context != 'com_importer.import' || !array_key_exists($handler, $handlers))
		{
			return true;
		}

		echo $this->renderLayout($handler, $data);

		return true;
	}

	/**
	 * Process the export of items
	 *
	 * @param   string  $context
	 * @param   string  $filename
	 * @param   int     $templateId
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function onRequestExport($context, $filename, $templateId = 0)
	{
		if (!$filename || !is_dir(dirname($filename)))
		{
			return;
		}

		$aliases = null;

		if ($templateId)
		{
			$template = ImportHelper::getTemplate($templateId);

			if (!$template)
			{
				throw new Exception(JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_ERROR_EXPORT_LOAD_TEMPLATE'));
			}

			$aliases = $template->mapping;
		}

		if ($context == 'com_sellacious.products')
		{
			$exporter = new ProductsExporter;
			$exporter->export($filename, $aliases);
		}
		elseif ($context == 'com_sellacious.orders')
		{
			$exporter = new OrdersExporter;
			$exporter->export($filename);
		}
	}

	/**
	 * Handles the source file upload for a product import job
	 *
	 * @param   string  $handler  The handler name that is called for import
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	protected function setupCsvImport($handler)
	{
		$path     = $this->uploadFile('import_file', array('.csv'));
		$tplId    = $this->app->input->getInt('template_id', 0);
		$template = ImportHelper::getTemplate($tplId);

		$import = new ImportRecord;

		$import->id          = null;
		$import->handler     = $handler;
		$import->path        = $path;
		$import->log_path    = substr($path, 0, - 4) . '.log';
		$import->output_path = substr($path, 0, - 4) . '-output.csv';
		$import->template    = $tplId;
		$import->mapping     = $template->mapping;
		$import->options     = $template->params;
		$import->progress    = null;
		$import->params      = null;
		$import->state       = 1;

		$import->save();

		$this->app->setUserState('com_importer.import.state.id', $import->id);
	}

	/**
	 * Handles the source file upload for a images import job
	 *
	 * @param   string  $handler  The handler name that is called for import
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	protected function setupImagesImport($handler)
	{
		$src = 'import-uploads';

		if (!$this->app->input->getInt('useFolder'))
		{
			$src     = uniqid('images_');
			$path    = $this->uploadFile('import_file', array('.zip'));
			$archive = new Archive();
			$unzip   = $archive->extract($path, $this->tmpPath . '/' . $src);

			// Remove source archive anyway
			JFile::delete($path);

			if (!$unzip)
			{
				throw new Exception(JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_IMAGES_ARCHIVE_EXTRACT_ERROR'));
			}
		}

		if (!is_dir($this->tmpPath. '/' . $src))
		{
			throw new Exception(JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_IMAGES_SOURCE_NOT_FOUND'));
		}

		$import = new ImportRecord;

		$import->id          = null;
		$import->handler     = $handler;
		$import->path        = $this->tmpPath . '/' . $src;
		$import->log_path    = $this->tmpPath . '/' . $src . '-logs.log';
		$import->output_path = $this->tmpPath . '/' . $src . '-output.csv';
		$import->template    = 0;
		$import->mapping     = null;
		$import->options     = null;
		$import->progress    = null;
		$import->params      = null;
		$import->state       = 1;

		$import->save();

		$this->app->setUserState('com_importer.import.state.id', $import->id);
	}

	/**
	 * Process category json from magento (temporary trigger capture)
	 *
	 * @param   string            $context
	 * @param   AbstractImporter  $importer
	 *
	 * @since   1.5.0
	 */
	public function onBeforeImport($context, $importer)
	{
		if ($context == 'com_importer.import.products')
		{
			$this->handleMagentoCategories($importer);
		}
	}

	/**
	 * Utility method to create or update a product category
	 *
	 * @param   string  $title     Category title
	 * @param   string  $type      Product type - physical, electronic, package
	 * @param   int     $parentId  Parent category id
	 * @param   int     $id        Category id, positive integer for existing, zero for new
	 *
	 * @return  int  Category id of the created/updated category
	 *
	 * @since   1.5.0
	 */
	protected function createCategory($title, $type, $parentId, $id = null)
	{
		static $categories = array();

		if (!$categories)
		{
			$query = $this->db->getQuery(true);
			$query->select('a.id, a.title, a.parent_id, a.type')
				->from($this->db->qn('#__sellacious_categories', 'a'))
				->where('a.type LIKE ' . $this->db->q('product/%', false));

			$items = $this->db->setQuery($query)->getIterator();

			foreach ($items as $index => $item)
			{
				$categories[$item->type][$item->parent_id][strtolower($item->title)] = $item->id;
			}
		}

		$cType = 'product/' . $type;

		if (!empty($categories[$cType][$parentId][strtolower($title)]))
		{
			return $categories[$cType][$parentId][strtolower($title)];
		}

		$category = new \stdClass;

		$category->id        = $id;
		$category->title     = $title;
		$category->alias     = JApplicationHelper::stringURLSafe($title) ?: JUserHelper::genRandomPassword(12);
		$category->type      = $cType;
		$category->parent_id = $parentId;
		$category->state     = 1;

		if ($category->id)
		{
			$this->db->updateObject('#__sellacious_categories', $category, array('id'));
		}
		else
		{
			$this->db->insertObject('#__sellacious_categories', $category, 'id');
		}

		return $categories[$cType][$parentId][strtolower($title)] = $category->id;
	}

	/**
	 * A scheduler task that detects any directly uploaded files in specific folder and imports it if found.
	 * NOTE: Only supports CSV templates as of now.
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function folderImport()
	{
		$useCRON  = $this->params->get('cron', 1);
		$cronKey  = $this->params->get('cron_key', '');
		$interval = $this->params->get('exec_interval', 1800);
		$key      = $this->app->input->getString('import_key');

		$lastAccess = 0;
		$curTime    = time();
		$logfile    = $this->tmpPath . '/' . md5(__METHOD__);

		if (is_readable($logfile))
		{
			$lastAccess = file_get_contents($logfile);
		}

		// Cron use is disabled or the cronKey matches, if cron enabled do only at given seconds interval
		$canRun = $useCRON ? (trim($cronKey) != '' && $cronKey == $key) : ($lastAccess == 0 || $curTime - $lastAccess >= $interval);

		if (!$canRun)
		{
			return;
		}

		// Mark started earlier to avoid any other instance creating in between
		file_put_contents($logfile, $curTime);

		try
		{
			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');

			$templates = $this->getTemplates();
			$root      = $this->params->get('import_source', '/import-source');

			foreach ($templates as $template)
			{
				$folder = JPath::clean(JPATH_SITE . '/' . $root . '/' . $template->alias);

				if (JFolder::exists($folder))
				{
					// Match all "*.csv" but not "*-output.csv"
					$files = JFolder::files($folder, '(?<!-output)\.csv$');

					if ($file = reset($files))
					{
						// Todo: set default options in template edit view
						$now   = JFactory::getDate()->format('Y-m-d H:i:s T');
						$base  = basename($file, '.csv');
						$state = (object) array(
							'handler'   => $template->import_type,
							'path'      => $folder . '/' . $file,
							'log_path'    => $folder . '/' . $base . '-log.log',
							'output_path' => $folder . '/' . $base . '-output.csv',
							'template'  => $template->id,
							'mapping'   => $template->mapping ?: array(),
							'options'   => $template->params ?: array(),
							'timestamp' => $now,
							'done'      => false,
						);

						$timer = Timer::getInstance('Import.' . $state->handler, $state->log_path);

						try
						{
							$this->importCsv($state);
						}
						catch (Exception $e)
						{
							$timer->log(JText::_('Import from CSV failed. ' . $e->getMessage()));
						}

						try
						{
							$pCache = new Products;
							$pCache->build();
							$timer->log(JText::_('Rebuild products cache completed.'));
						}
						catch (Exception $e)
						{
							$timer->log(JText::_('Rebuild products cache failed. ' . $e->getMessage()));
						}

						try
						{
							$rCache = new Prices;
							$rCache->build();
							$timer->log(JText::_('Rebuild prices cache completed.'));
						}
						catch (Exception $e)
						{
							$timer->log(JText::_('Rebuild prices cache failed. ' . $e->getMessage()));
						}

						try
						{
							$sCache = new Specifications;
							$sCache->build();
							$timer->log(JText::_('Rebuild specifications cache completed.'));
						}
						catch (Exception $e)
						{
							$timer->log(JText::_('Rebuild specifications cache failed. ' . $e->getMessage()));
						}

						$timer->log(JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_EMAILING'));

						$subject    = JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_LOG_AUTO', $state->handler, $state->created);
						$body       = file_get_contents($state->log_path);
						$attachment = array($state->path);

						if (is_file($state->output_path))
						{
							$attachment[] = $state->output_path;
						}

						try
						{
							$sent = $this->sendMail($subject, $body, $attachment);

							if ($sent)
							{
								$timer->log(JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_EMAIL_SENT'));
							}
							else
							{
								$timer->log(JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_EMAIL_FAIL'));
							}
						}
						catch (Exception $e)
						{
							$timer->log(JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_EMAIL_FAIL') . $e->getMessage());
						}

						$timer->log('EOF');

						// Move the file away to prevent re-execution
						$nowTs  = JFactory::getDate()->format('Y-m-d-H-i-s-T');
						$moveTo = $folder . '/processed-' . $nowTs . '/';
						JFolder::create($moveTo);

						JFile::move($state->path, $moveTo . basename($state->path));
						JFile::move($state->log_path, $moveTo . basename($state->log_path));
						JFile::move($state->output_path, $moveTo . basename($state->output_path));
						JFile::write($moveTo . 'index.html', '<html></html>');

						// We only execute one file per job cycle to avoid any possible conflicts or collisions.
						break;
					}
				}
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::CRITICAL);

			$this->app->close($e->getMessage());
		}

		$this->app->close();
	}

	/**
	 * Method to import a given CSV file using the provided state value
	 *
	 * @param   stdClass  $state  The import state as populated to maintain the import sessions
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	protected function importCsv($state)
	{
		// Get stateful instance of Timer
		$timer = Timer::getInstance('Import.' . $state->handler, $state->log_path);

		if (!is_file($state->path))
		{
			throw new Exception(JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_FILE_NO_FOUND', $state->path));
		}

		try
		{
			$state->mapping = isset($state->mapping) ? (array) $state->mapping : array();
			$state->options = isset($state->options) ? (array) $state->options : array();

			/** @var  AbstractImporter  $importer */
			$importer = ImportHelper::getImporter($state->handler);

			$importer->load($state->path);
			$importer->setColumnsAlias($state->mapping);
			$importer->setOption('import.output_path', $state->output_path);

			foreach ($state->options as $oKey => $oValue)
			{
				$importer->setOption($oKey, $oValue);
			}

			$importer->import();
		}
		catch (Exception $e)
		{
			$timer->interrupt($e->getMessage());

			throw new Exception(JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_INTERRUPTED', $e->getMessage()), 0, $e);
		}
	}

	/**
	 * Send an email with the given parameters
	 *
	 * @param   string  $subject     Email subject
	 * @param   string  $body        Email body
	 * @param   array   $attachment  The list of attachment
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	protected function sendMail($subject, $body, $attachment)
	{
		$app = JFactory::getApplication();

		$mailFrom    = $app->get('mailfrom');
		$fromName    = $app->get('fromname');
		$replyTo     = $app->get('mailfrom');
		$replyToName = $app->get('fromname');
		$to          = array($fromName => $mailFrom, 'Izhar Aazmi' => 'izharaazmi@gmail.com');
		$cc          = array();

		$mailer = JFactory::getMailer();

		if ($mailer->setSender(array($mailFrom, $fromName, false)) === false)
		{
			throw new RuntimeException(JText::_('COM_IMPORTER_IMPORT_EMAIL_FAIL_ADD_SENDER'));
		}

		if ($mailer->addReplyTo($replyTo, $replyToName) === false)
		{
			throw new RuntimeException(JText::_('COM_IMPORTER_IMPORT_EMAIL_FAIL_ADD_REPLY_TO'));
		}

		$mailer->clearAllRecipients();

		if ($mailer->addRecipient(array_values($to), array_keys($to)) === false)
		{
			throw new RuntimeException(JText::_('COM_IMPORTER_IMPORT_EMAIL_FAIL_ADD_RECIPIENT'));
		}

		if (count($cc) && $mailer->addCc(array_values($cc), array_keys($cc)) === false)
		{
			throw new RuntimeException(JText::_('COM_IMPORTER_IMPORT_EMAIL_FAIL_ADD_RECIPIENT'));
		}

		$mailer->isHtml(false);
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->addAttachment($attachment);

		return $mailer->Send();
	}

	/**
	 * Process category json from magento (temporary trigger capture)
	 *
	 * @param   AbstractImporter  $importer
	 *
	 * @since   1.5.0
	 */
	protected function handleMagentoCategories($importer)
	{
		if (!$importer->getOption('magento'))
		{
			return;
		}

		$idMap = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.x__id, a.product_categories, a.product_type')
			->from($db->qn($importer->importTable, 'a'));

		$iterator = $db->setQuery($query)->getIterator();
		$idMap[1] = 1;
		$i        = 0;

		foreach ($iterator as $i => $item)
		{
			$cats       = json_decode($item->product_categories, true);
			$categories = array();

			foreach ($cats as $catInfo)
			{
				list($cid, $cPath, $cTitle) = $catInfo;

				$levels   = explode('/', $cPath);
				$cid      = (int) $cid;
				$parentId = 1;

				// Create placeholders for parent categories
				foreach ($levels as $catid)
				{
					$catid = (int) $catid;

					// Exclude repeating item and the leaf node
					if ($cid != $catid)
					{
						if (!isset($idMap[$catid]))
						{
							$idMap[$catid] = $this->createCategory('Unnamed', $item->type ?: 'physical', $idMap[$parentId]);
						}

						$parentId = $catid;
					}
				}

				// Create the leaf node
				$id = isset($idMap[$cid]) ? $idMap[$cid] : null;
				$id = $this->createCategory($cTitle, $item->type ?: 'physical', $idMap[$parentId], $id);

				$categories[] = $id;
			}

			$item->x__category_ids    = json_encode($categories);
			$item->product_categories = null;

			$db->updateObject($importer->importTable, $item, array('x__id'));

			$importer->timer->hit($i + 1, 100, ' records processed. Processing categories.');
		}

		$importer->timer->hit($i + 1, 1, ' records processed. Processing categories.');
	}
}
