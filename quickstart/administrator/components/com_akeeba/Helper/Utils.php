<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Helper;

// Protect from unauthorized access
defined('_JEXEC') or die();

class Utils
{
	/**
	 * Returns the relative path of directory $to to root path $from
	 *
	 * @param   string  $from  Root directory
	 * @param   string  $to    The directory whose path we want to find relative to $from
	 *
	 * @return  string  The relative path
	 */
	public static function getRelativePath($from, $to)
	{
		// some compatibility fixes for Windows paths
		$from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
		$to   = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
		$from = str_replace('\\', '/', $from);
		$to   = str_replace('\\', '/', $to);

		$from    = explode('/', $from);
		$to      = explode('/', $to);
		$relPath = $to;

		foreach ($from as $depth => $dir)
		{
			// find first non-matching dir
			if ($dir === $to[ $depth ])
			{
				// ignore this directory
				array_shift($relPath);

				continue;
			}

			// Get number of remaining dirs to $from
			$remaining = count($from) - $depth;

			if ($remaining > 1)
			{
				// add traversals up to first matching dir
				$padLength = (count($relPath) + $remaining - 1) * -1;
				$relPath   = array_pad($relPath, $padLength, '..');

				break;
			}

			$relPath[0] = './' . $relPath[0];
		}

		return implode('/', $relPath);
	}

	/**
	 * Escapes a string for use with Javascript
	 *
	 * @param   string  $string  The string to escape
	 * @param   string  $extras  The characters to escape
	 *
	 * @return  string
	 */
	static function escapeJS($string, $extras = '')
	{
		// Make sure we escape single quotes, slashes and brackets
		if (empty($extras))
		{
			$extras = "'\\[]";
		}

		return addcslashes($string, $extras);
	}
}
