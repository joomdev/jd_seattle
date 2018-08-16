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

namespace Akeeba\Engine\Util;

// Protection against direct access
defined('AKEEBAENGINE') or die();

abstract class ParseIni
{
	/**
	 * Parse an INI file and return an associative array. This monstrosity is required because some so-called hosts
	 * have disabled PHP's parse_ini_file() function for "security reasons". Apparently their blatant ignorance doesn't
	 * allow them to discern between the innocuous parse_ini_file and the potentially dangerous ini_set, leading them to
	 * disable the former and let the latter enabled.
	 *
	 * @param    string  $file              The file to process
	 * @param    bool    $process_sections  True to also process INI sections
	 *
	 * @return   array    An associative array of sections, keys and values
	 */
	public static function parse_ini_file($file, $process_sections, $rawdata = false)
	{
		/**
		 * WARNING: DO NOT USE INI_SCANNER_RAW IN THE parse_ini_string / parse_ini_file FUNCTION CALLS
		 *
		 * Sometimes we need to save data which is either multiline or has double quotes in the Engine's
		 * configuration. For this reason we have to manually escape \r, \n, \t and \" in
		 * Akeeba\Engine\Configuration::dumpObject(). If we don't we end up with multiline INI values which
		 * won't work. However, if we are using INI_SCANNER_RAW these characters are not escaped back to their
		 * original form. As a result we end up with broken data which cause various problems, the most visible
		 * of which is that Google Storage integration is broken since the JSON data included in the config is
		 * now unparseable.
		 */

		if ($rawdata)
		{
			if (!function_exists('parse_ini_string'))
			{
				return self::parse_ini_file_php($file, $process_sections, $rawdata);
			}

			// !!! VERY IMPORTANT !!! Read the warning above before touching this line
			return parse_ini_string($file, $process_sections);
		}

		if (!function_exists('parse_ini_file'))
		{
			return self::parse_ini_file_php($file, $process_sections);
		}

		// !!! VERY IMPORTANT !!! Read the warning above before touching this line
		return parse_ini_file($file, $process_sections);
	}

	/**
	 * A PHP based INI file parser.
	 *
	 * Thanks to asohn ~at~ aircanopy ~dot~ net for posting this handy function on
	 * the parse_ini_file page on http://gr.php.net/parse_ini_file
	 *
	 * @param    string $file             Filename to process
	 * @param    bool   $process_sections True to also process INI sections
	 * @param    bool   $rawdata          If true, the $file contains raw INI data, not a filename
	 *
	 * @return    array    An associative array of sections, keys and values
	 */
	static function parse_ini_file_php($file, $process_sections = false, $rawdata = false)
	{
		$process_sections = ($process_sections !== true) ? false : true;

		if (!$rawdata)
		{
			$ini = file($file);
		}
		else
		{
			$file = str_replace("\r", "", $file);
			$ini = explode("\n", $file);
		}

		if (count($ini) == 0)
		{
			return array();
		}

		$sections = array();
		$values = array();
		$result = array();
		$globals = array();
		$i = 0;
		foreach ($ini as $line)
		{
			$line = trim($line);
			$line = str_replace("\t", " ", $line);

			// Comments
			if (!preg_match('/^[a-zA-Z0-9[]/', $line))
			{
				continue;
			}

			// Sections
			if ($line{0} == '[')
			{
				$tmp = explode(']', $line);
				$sections[] = trim(substr($tmp[0], 1));
				$i++;
				continue;
			}

			// Key-value pair
			$lineParts = explode('=', $line, 2);
			if (count($lineParts) != 2)
			{
				continue;
			}
			$key = trim($lineParts[0]);
			$value = trim($lineParts[1]);
			unset($lineParts);

			if (strstr($value, ";"))
			{
				$tmp = explode(';', $value);
				if (count($tmp) == 2)
				{
					if ((($value{0} != '"') && ($value{0} != "'")) ||
						preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
						preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value)
					)
					{
						$value = $tmp[0];
					}
				}
				else
				{
					if ($value{0} == '"')
					{
						$value = preg_replace('/^"(.*)".*/', '$1', $value);
					}
					elseif ($value{0} == "'")
					{
						$value = preg_replace("/^'(.*)'.*/", '$1', $value);
					}
					else
					{
						$value = $tmp[0];
					}
				}
			}
			$value = trim($value);
			$value = trim($value, "'\"");

			if ($i == 0)
			{
				if (substr($line, -1, 2) == '[]')
				{
					$globals[$key][] = $value;
				}
				else
				{
					$globals[$key] = $value;
				}
			}
			else
			{
				if (substr($line, -1, 2) == '[]')
				{
					$values[$i - 1][$key][] = $value;
				}
				else
				{
					$values[$i - 1][$key] = $value;
				}
			}
		}

		for ($j = 0; $j < $i; $j++)
		{
			if ($process_sections === true)
			{
				if (isset($sections[$j]) && isset($values[$j]))
				{
					$result[$sections[$j]] = $values[$j];
				}
			}
			else
			{
				if (isset($values[$j]))
				{
					$result[] = $values[$j];
				}
			}
		}

		return $result + $globals;
	}
}
