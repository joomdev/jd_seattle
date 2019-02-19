<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
namespace Sellacious\Language;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Sellacious language helper to perform common tasks for sellacious language
 *
 * @since   1.6.0
 */
abstract class LanguageHelper
{
	/**
	 * The strings loaded from various language files, strings from each language file is in a separate array.
	 *
	 * @var   string[][]
	 *
	 * @since   1.6.0
	 */
	protected static $strings;

	/**
	 * Loads a single language file and returns the resulting strings array
	 *
	 * @param   string  $extension  The extension for which a language file should be loaded
	 * @param   string  $language   The language to load, defaults to current language property of this object
	 * @param   string  $basePath   The basepath to use, relative to the baseDir
	 *
	 * @return  string[]|bool  The constants and language strings as an associative array if file has successfully loaded, false otherwise
	 *
	 * @since   1.6.0
	 */
	public static function load($extension, $language, $basePath = JPATH_BASE)
	{
		$basePath  = rtrim($basePath, '/');
		$extension = $extension ?: 'joomla';
		$storeId   = "{$basePath}:{$language}:{$extension}";

		// Load the language file if not already loaded
		if (!isset(static::$strings[$storeId]))
		{
			$filename = $basePath . '/language/' . $language . '/' . $language . ($extension == 'joomla' ? '' : '.' . $extension) . '.ini';

			static::$strings[$storeId] = static::parseIniFile($filename);
		}

		return static::$strings[$storeId];
	}

	/**
	 * Parses a language file
	 *
	 * @param   string  $file  The path of the file, relative to the baseDir
	 *
	 * @return  string[]|bool  Whether the file was parsed successfully
	 *
	 * @since   1.6.0
	 */
	public static function parseIniFile($file)
	{
		$strings = array();

		if (file_exists($file))
		{
			$disabledFunctions      = explode(',', ini_get('disable_functions'));
			$isParseIniFileDisabled = in_array('parse_ini_file', array_map('trim', $disabledFunctions));

			if (function_exists('parse_ini_file') && !$isParseIniFileDisabled)
			{
				$strings = @parse_ini_file($file);
			}
			else
			{
				$contents = file_get_contents($file);
				$contents = str_replace('_QQ_', '"\""', $contents);
				$strings  = @parse_ini_string($contents);
			}
		}

		return $strings;
	}

	/**
	 * Save strings to a language file.
	 *
	 * @param   string  $filename  The language ini file path.
	 * @param   array   $strings   The array of strings.
	 *
	 * @return  bool  True if saved, false otherwise.
	 *
	 * @since   1.6.0
	 */
	public static function saveToIniFile($filename, array $strings)
	{
		\JLoader::register('JFile', JPATH_LIBRARIES . '/joomla/filesystem/file.php');
		\JLoader::register('JFolder', JPATH_LIBRARIES . '/joomla/filesystem/folder.php');

		// Escape double quotes.
		foreach ($strings as $key => $string)
		{
			$strings[$key] = addcslashes($string, '"');
		}

		// Write override ini file with the strings.
		$registry = new Registry($strings);

		\JFolder::create(dirname($filename));

		return \JFile::write($filename, $registry->toString('INI'));
	}

	/**
	 * Get the path to a language
	 *
	 * @param   string  $basePath  The basepath to use
	 * @param   string  $language  The language tag
	 *
	 * @return  string  language related path or null
	 *
	 * @since   1.6.0
	 */
	public static function getLanguagePath($basePath = JPATH_BASE, $language = null)
	{
		return $basePath . '/language' . (empty($language) ? '' : '/' . $language);
	}

	/**
	 * Builds a list of the system languages which can be used in a select option
	 *
	 * @param   string   $selected  Client key for the area
	 * @param   boolean  $clientId  Get only installed languages for this client
	 * @param   string   $basePath  Get all languages from this base path whether installed or not, set clientName = null to use this
	 *
	 * @return  array  List of system languages
	 *
	 * @since   1.6.0
	 */
	public static function createLanguageList($selected, $clientId = null, $basePath = JPATH_BASE)
	{
		$list      = array();
		$languages = $clientId === null ? self::getKnownLanguages($basePath) : static::getInstalledLanguages($clientId, true);

		foreach ($languages as $languageCode => $language)
		{
			$metadata = $clientId ? $language->metadata : $language;
			$list[]   = array(
				'text'     => isset($metadata['nativeName']) ? $metadata['nativeName'] : $metadata['name'],
				'value'    => $languageCode,
				'selected' => $languageCode === $selected ? 'selected="selected"' : null,
			);
		}

		return $list;
	}

	/**
	 * Returns a list of known languages for an area
	 *
	 * @param   string  $basePath  The basepath to use
	 *
	 * @return  array  key/value pair with the language file and real name.
	 *
	 * @since   1.6.0
	 */
	public static function getKnownLanguages($basePath = JPATH_BASE)
	{
		return self::parseLanguageFiles(self::getLanguagePath($basePath));
	}

	/**
	 * Searches for language directories within a certain base dir.
	 *
	 * @param   string  $dir  directory of files.
	 *
	 * @return  array  Array holding the found languages as filename => real name pairs.
	 *
	 * @since   1.6.0
	 */
	public static function parseLanguageFiles($dir = null)
	{
		$languages = array();

		// Search main language directory for subdirectories
		foreach (glob($dir . '/*', GLOB_NOSORT | GLOB_ONLYDIR) as $directory)
		{
			// But only directories with lang code format
			if (preg_match('#/[a-z]{2,3}-[A-Z]{2}$#', $directory))
			{
				$dirPathParts = pathinfo($directory);
				$file         = $directory . '/' . $dirPathParts['filename'] . '.xml';

				if (is_file($file))
				{
					try
					{
						// Get installed language metadata from xml file and merge it with lang array
						if ($metadata = self::parseXMLLanguageFile($file))
						{
							$languages = array_replace($languages, array($dirPathParts['filename'] => $metadata));
						}
					}
					catch (\RuntimeException $e)
					{
					}
				}
			}
		}

		return $languages;
	}

	/**
	 * Get a list of installed languages.
	 *
	 * @param   integer  $clientId         The client app id
	 * @param   boolean  $processMetaData  Fetch Language metadata
	 * @param   boolean  $processManifest  Fetch Language manifest
	 * @param   string   $pivot            The pivot of the returning array
	 * @param   string   $orderField       Field to order the results
	 * @param   string   $orderDirection   Direction to order the results
	 *
	 * @return  array  Array with the installed languages
	 *
	 * @since   1.6.0
	 */
	public static function getInstalledLanguages($clientId = null, $processMetaData = false, $processManifest = false, $pivot = 'element',
	                                             $orderField = null, $orderDirection = null)
	{
		static $installedLanguages = null;

		if ($installedLanguages === null)
		{
			$cache = \JFactory::getCache('com_languages', '');

			/** @var \JCache $cache  */
			if ($cache->contains('installedlanguages'))
			{
				$installedLanguages = $cache->get('installedlanguages');
			}
			else
			{
				$db = \JFactory::getDbo();

				$query = $db->getQuery(true)
					->select($db->quoteName(array('element', 'name', 'client_id', 'extension_id')))
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('type') . ' = ' . $db->quote('language'))
					->where($db->quoteName('state') . ' = 0')
					->where($db->quoteName('enabled') . ' = 1');

				$installedLanguages = $db->setQuery($query)->loadObjectList();

				$cache->store($installedLanguages, 'installedlanguages');
			}
		}

		$languages = array();
		$clients   = \JApplicationHelper::getClientInfo($clientId);

		if ($clientId !== null)
		{
			$clients = is_object($clients) ? array($clients->id => $clients) : array();
		}

		foreach ($clients as $client)
		{
			$languages[$client->id] = array();
		}

		foreach ($installedLanguages as $language)
		{
			$cid = (int) $language->client_id;

			if (!array_key_exists($cid, $languages))
			{
				continue;
			}

			$lang = $language;

			if ($processMetaData || $processManifest)
			{
				$metafile   = self::getLanguagePath($clients[$cid]->path, $language->element) . '/' . $language->element . '.xml';

				// Process the language metadata.
				if ($processMetaData)
				{
					try
					{
						$lang->metadata = self::parseXMLLanguageFile($metafile);
					}

						// Not able to process xml language file. Fail silently.
					catch (\Exception $e)
					{
						\JLog::add(\JText::sprintf('JLIB_LANGUAGE_ERROR_CANNOT_LOAD_METAFILE', $language->element, $metafile), \JLog::WARNING, 'language');

						continue;
					}

					// No metadata found, not a valid language. Fail silently.
					if (!is_array($lang->metadata))
					{
						\JLog::add(\JText::sprintf('JLIB_LANGUAGE_ERROR_CANNOT_LOAD_METADATA', $language->element, $metafile), \JLog::WARNING, 'language');

						continue;
					}
				}

				// Process the language manifest.
				if ($processManifest)
				{
					try
					{
						$lang->manifest = \JInstaller::parseXMLInstallFile($metafile);
					}

					// Not able to process xml language file. Fail silently.
					catch (\Exception $e)
					{
						\JLog::add(\JText::sprintf('JLIB_LANGUAGE_ERROR_CANNOT_LOAD_METAFILE', $language->element, $metafile), \JLog::WARNING, 'language');

						continue;
					}

					// No metadata found, not a valid language. Fail silently.
					if (!is_array($lang->manifest))
					{
						\JLog::add(\JText::sprintf('JLIB_LANGUAGE_ERROR_CANNOT_LOAD_METADATA', $language->element, $metafile), \JLog::WARNING, 'language');

						continue;
					}
				}
			}

			$languages[$cid][] = $lang;
		}

		// Order the list, if needed
		if ($orderField !== null && $orderDirection !== null)
		{
			$orderDirection = strtolower($orderDirection) === 'desc' ? -1 : 1;

			foreach ($languages as $cId => $language)
			{
				$languages[$cId] = ArrayHelper::sortObjects($languages[$cId], $orderField, $orderDirection, true, true);
			}
		}

		// Add the pivot, if needed.
		if ($pivot !== null)
		{
			foreach ($languages as $cId => $language)
			{
				$languages[$cId] = ArrayHelper::pivot($languages[$cId], $pivot);
			}
		}

		return $clientId !== null ? $languages[$clientId] : $languages;
	}

	/**
	 * Parse XML file for language information.
	 *
	 * @param   string  $path  Path to the XML files.
	 *
	 * @return  array  Array holding the found metadata as a key => value pair.
	 *
	 * @since   1.6.0
	 *
	 * @throws  \RuntimeException
	 */
	public static function parseXMLLanguageFile($path)
	{
		if (!is_readable($path))
		{
			throw new \RuntimeException('File not found or not readable');
		}

		// Try to load the file
		$xml = simplexml_load_file($path);

		if (!$xml)
		{
			return null;
		}

		// Check that it's a metadata file
		if ((string) $xml->getName() != 'metafile')
		{
			return null;
		}

		$metadata = array();

		foreach ($xml->metadata->children() as $child)
		{
			$metadata[$child->getName()] = (string) $child;
		}

		return $metadata;
	}
}
