<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 *
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or, at your option, any later version
 * @package   akeebaengine
 *
 */

namespace Akeeba\Engine\Util\Transfer;

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * An interface for Transfer adapters which support remote resources, allowing us to efficient read from / write to
 * remote locations as if they were local files.
 */
interface RemoteResourceInterface
{
	/**
	 * Return a string with the appropriate stream wrapper protocol for $path. You can use the result with all PHP
	 * functions / classes which accept file paths such as DirectoryIterator, file_get_contents, file_put_contents,
	 * fopen etc.
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 */
	public function getWrapperStringFor($path);

	/**
	 * Return the raw server listing for the requested folder.
	 *
	 * @param   string  $folder        The path name to list
	 *
	 * @return  string
	 */
	public function getRawList($folder);
}
