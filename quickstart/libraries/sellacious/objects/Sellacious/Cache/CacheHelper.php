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
namespace Sellacious\Cache;

use JFactory;
use JText;

defined('_JEXEC') or die;

/**
 * Sellacious Cache helper class.
 *
 * @since  1.6.1
 */
class CacheHelper
{
	/**
	 * Method to queue the cli based cache builder to run in the background
	 *
	 * @param   string  $logfile  Path to the log file where the generated output will be saved
	 * @param   int     $userId   The masked joomla/sellacious user as which the process will be run
	 *
	 * @return  bool
	 *
	 * @since   1.6.1
	 */
	public static function executeCli($logfile, $userId)
	{
		$config     = JFactory::getConfig();
		$executable = $config->get('php_executable', 'php');
		$script     = escapeshellarg(JPATH_SELLACIOUS . '/cli/sellacious_cache.php');
		$logfileE   = escapeshellarg($logfile);

		// Truncate/initialize log file
		file_put_contents($logfile, '');

		$CMD = "{$executable} {$script} --user={$userId} --log={$logfileE} > {$logfileE} 2> {$logfileE} & echo \$!";
		$pid = exec($CMD);

		return $pid;
	}

	/**
	 * Build the cache for all cache handlers.
	 * We'd soon load the handlers dynamically instead of hard-coding here
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	public static function buildCache()
	{
		$pCache = new Products;
		$rCache = new Prices;
		$sCache = new Specifications;

		$pCache->build();
		$rCache->build();
		$sCache->build();

		// Sync media as well
		$helper = \SellaciousHelper::getInstance();

		$helper->media->purgeMissing();
		$helper->media->syncFromFilesystem();
	}

	/**
	 * Running status check when running by Cli,
	 * if running in a web session this will return false anyway
	 *
	 * @return   bool
	 *
	 * @since   1.6.1
	 */
	public static function isRunning()
	{
		$tmp = JFactory::getConfig()->get('tmp_path');

		return file_exists($tmp . '/.s-cache-lock');
	}
}
