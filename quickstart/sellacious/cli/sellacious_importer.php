<?php
/**
 * @version     1.6.1
 * @package     Sellacious.Cli
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

use Sellacious\Cache\Prices as PricesCache;
use Sellacious\Cache\Products as ProductsCache;
use Sellacious\Cache\Specifications as SpecificationsCache;
use Sellacious\Import\AbstractImporter;
use Sellacious\Import\ImportHelper;
use Sellacious\Import\ImportRecord;
use Sellacious\Utilities\Timer;

/**
 * Sellacious Importer CLI.
 *
 * This is a command-line script to help with sellacious database import, such as from CSV etc.
 */
define('_JEXEC', 1);

define('JPATH_BASE', dirname(__DIR__));

require_once __DIR__ . '/cli.php';
require_once __DIR__ . '/application.php';

ini_set('memory_limit', '1G');

/**
 * A command line cron job to run the Sellacious importer.
 *
 * @since   1.6.1
 */
class SellaciousImporterCli extends SellaciousCliApplication
{
	/**
	 * The timer/logger instance
	 *
	 * @var   Timer
	 *
	 * @since   1.6.1
	 */
	protected $timer;

	/**
	 * Entry point for Smart Search CLI script
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function doExecute()
	{
		ob_start();

		// Print a blank line.
		$this->out();
		$this->out(JText::_('Sellacious Importer Cli'));
		$this->out('============================');

		// Remove the script time limit.
		@set_time_limit(0);

		try
		{
			file_put_contents(__DIR__ . '/.import-lock', getmypid());

			$this->loadLanguage();
			JLoader::import('sellacious_importer.loader');
			JPluginHelper::importPlugin('system', 'sellaciousimporter');

			$this->out('Import starting...');

			// Do the business stuff
			$this->doImport();
		}
		catch (Exception $e)
		{
			$this->out('========= ERROR ===================');

			$this->out($e->getMessage());
			$this->out($e->getFile() . ' : ' . $e->getLine());
			$this->out($e->getTraceAsString());

			$this->out('========= ERROR ==================');
		}

		@unlink(__DIR__ . '/.import-lock');

		$this->out('Import finished.');
		$this->out('EOF');

		$this->out();
	}

	/**
	 * Start the queued import process
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	protected function doImport()
	{
		$userId   = $this->input->getInt('user');
		$reset    = $this->input->getBool('reset');
		$importId = $this->input->getInt('job');
		$import   = ImportHelper::getImport($importId);

		if (!$import->id)
		{
			return;
		}

		// Force importer log file
		Timer::getInstance('Import.' . $import->handler, $import->log_path);

		/** @var  AbstractImporter  $importer */
		$importer = ImportHelper::getImporter($import->handler);

		$importer->timer->log('Initializing import...');

		// Remove current progress if reset is requested, will be saved only if executed
		if ($reset)
		{
			$import->progress = null;

			@unlink($import->log_path);
			@unlink($import->output_path);
		}

		$importer->setup($import);

		// Override active user if set
		if ($userId)
		{
			$importer->setOption('session.user', $userId);
		}

		$importer->timer->log('Starting import process...');

		$import->setState(2);

		$importer->import();

		$import->setState(3);

		// $this->rebuildCache();
	}

	/**
	 * Start the cache process
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	protected function rebuildCache()
	{
		$this->out('Initializing cache rebuild. This may take very long to update. Please be patient...');

		try
		{
			$helper = SellaciousHelper::getInstance();

			$pCache = new ProductsCache;
			$rCache = new PricesCache;
			$sCache = new SpecificationsCache;

			$this->out('Products cache rebuild started...');
			$pCache->build();
			$this->out('Products cache rebuild finished.');

			$this->out('Prices cache rebuild started...');
			$rCache->build();
			$this->out('Prices cache rebuild finished.');

			$this->out('Specifications cache rebuild started...');
			$sCache->build();
			$this->out('Specifications cache rebuild finished.');

			// Sync media as well
			$this->out('Discovering media files from filesystem.');

			$helper->media->purgeMissing();
			$helper->media->syncFromFilesystem();

			$this->out('Discovery of media files from filesystem finished.');
		}
		catch (Exception $e)
		{
			$this->out($e->getMessage());
			$this->out($e->getTraceAsString());
		}
	}

	protected function loadLanguage()
	{
		$language = JFactory::getLanguage();
		$current  = $language->getTag();

		$language->load('lib_importer', JPATH_LIBRARIES . '/sellacious_importer');
		$language->load('lib_importer', JPATH_BASE);

		$language->load('com_importer', JPATH_BASE . '/components/com_importer', 'en-GB');
		$language->load('com_importer', JPATH_BASE, 'en-GB');
		$language->load('com_importer', JPATH_BASE . '/components/com_importer', $current);
		$language->load('com_importer', JPATH_BASE, $current);
	}
}

$application = JApplicationCli::getInstance('SellaciousImporterCli');

// Set application instance to not cause loading default application (site) in Cli
JFactory::$application = $application;

$application->execute();
