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

/**
 * Sellacious Cache CLI.
 *
 * This is a command-line script to help with sellacious products/prices cache building.
 */
define('_JEXEC', 1);

define('JPATH_BASE', dirname(__DIR__));

require_once __DIR__ . '/cli.php';
require_once __DIR__ . '/application.php';

/**
 * A command line cron job to run the Sellacious cache.
 *
 * @since   1.6.1
 */
class SellaciousCacheCli extends SellaciousCliApplication
{
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
		$this->out(JText::_('Sellacious Cache Cli'));
		$this->out('============================');

		// Remove the script time limit.
		@set_time_limit(0);
		jimport('joomla.filesystem.folder');

		$tmp  = JFactory::getConfig()->get('tmp_path');
		$lock = $tmp . '/.s-cache-lock';

		try
		{
			$force = $this->input->get('force');

			if ($force)
			{
				JFolder::delete($lock);
			}

			if (!mkdir($lock))
			{
				$this->out('Could not acquire system lock for cache rebuild. Another cache process is already running.');
			}
			else
			{
				file_put_contents($lock . '/pid', getmypid());
				file_put_contents($lock . '/log', $this->input->get('log'));

				$this->out('Cache build starting...');

				// Do the business stuff
				$this->start();
			}
		}
		catch (Exception $e)
		{
			$this->out('========= ERROR ===================');

			$this->out($e->getMessage());
			$this->out(PHP_EOL);
			$this->out($e->getFile() . ' : ' . $e->getLine());
			$this->out(PHP_EOL);
			$this->out($e->getTraceAsString());

			$this->out('========= ERROR ==================');
		}

		// Only release lock if locked by me, allow other forced job to keep running
		if (trim(@file_get_contents($lock . '/pid')) == getmypid())
		{
			JFolder::delete($lock);
		}

		$this->out(ob_get_contents());
		$this->out('Cache finished.');
		$this->out('EOF');

		$this->out();
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
	protected function start()
	{
		$this->out('Initializing cache rebuild...');

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
}

JApplicationCli::getInstance('SellaciousCacheCli')->execute();
