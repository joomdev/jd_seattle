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

defined('_JEXEC') or die;

/**
 * Sellacious language text object to handle language translations
 * All loaded files are combined into one in order to effectively get a single translation source per language
 *
 * This object will work for one language at a time. To use another language create another instance,
 *
 * @since   1.6.0
 */
class LanguageText
{
	/**
	 * The default language code for the application
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $default = 'en-GB';

	/**
	 * The language code for the language to be indexed
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $language;

	/**
	 * The loaded language files list
	 *
	 * @var   bool[]
	 *
	 * @since   1.6.0
	 */
	protected $loaded = array();

	/**
	 * The loaded language strings
	 *
	 * @var   string[]
	 *
	 * @since   1.6.0
	 */
	protected $strings = array();

	/**
	 * Constructor
	 *
	 * @param   string  $language  The language to load, defaults to current language property of this object
	 *
	 * @since   1.6.0
	 */
	public function __construct($language = null)
	{
		$this->language = $language ?: $this->default;
	}

	/**
	 * Loads a single language file and appends the results to the existing strings
	 *
	 * @param   string  $extension  The extension for which a language file should be loaded
	 * @param   string  $path       The absolute base path for the language to load from
	 * @param   bool    $default    Flag to load the default language first
	 * @param   bool    $reload     Flag that will force a language to be reloaded if set to true
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function load($extension, $path = JPATH_BASE, $default = true, $reload = false)
	{
		$storeId = $extension . ':' . $path;

		if ($reload || !isset($this->loaded[$storeId]))
		{
			if ($default && $this->language != $this->default)
			{
				$strings = LanguageHelper::load($extension, $this->default, $path);

				if (is_array($strings) && count($strings))
				{
					$this->strings = array_replace($this->strings, $strings);
				}
			}

			$strings = LanguageHelper::load($extension, $this->language, $path);

			if (is_array($strings) && count($strings))
			{
				$this->strings = array_replace($this->strings, $strings);
			}

			$this->loaded[$storeId] = true;
		}
	}

	/**
	 * Returns all the loaded strings
	 *
	 * @return  string[]
	 *
	 * @since   1.6.0
	 */
	public function getStrings()
	{
		return $this->strings;
	}

	/**
	 * Returns the text value for the given string if found
	 *
	 * @param   string  $key  The language key to process
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function getString($key)
	{
		return isset($this->strings[$key]) ? $this->strings[$key] : '';
	}
}
