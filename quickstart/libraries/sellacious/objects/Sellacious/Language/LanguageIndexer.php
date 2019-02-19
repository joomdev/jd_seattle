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
 * Sellacious language indexer object
 *
 * @since   1.6.0
 */
class LanguageIndexer
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
	 * Whether to use the override pattern
	 *
	 * @var   bool
	 *
	 * @since   1.6.0
	 */
	protected $override;

	/**
	 * The client application base path to find the source files to index
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $baseDir;

	/**
	 * The loaded language files status
	 *
	 * @var   bool[]
	 *
	 * @since   1.6.0
	 */
	protected $loaded = array();

	/**
	 * The loaded language strings
	 *
	 * @var   array
	 *
	 * @since   1.6.0
	 */
	protected $strings = array();

	/**
	 * The list of available language files
	 *
	 * @var   array
	 *
	 * @since   1.6.0
	 */
	protected $files;

	/**
	 * The list of available language folders
	 *
	 * @var   array
	 *
	 * @since   1.6.0
	 */
	protected $folders;

	/**
	 * LanguageIndexer constructor.
	 *
	 * @param   string  $language   The language code for the language to be indexed
	 * @param   string  $directory  The client application base path to find the source files to index
	 * @param   bool    $override   Whether to use the override pattern
	 *
	 * @since   1.6.0
	 */
	public function __construct($language, $directory = JPATH_BASE, $override = false)
	{
		$this->language = $language;
		$this->baseDir  = $directory;
		$this->override = $override;
	}

	/**
	 * Get the base language folders from
	 *
	 * @return  string[][]
	 *
	 * @since   1.6.0
	 */
	public function getLanguageStrings()
	{
		if (!$this->strings)
		{
			$extLists = array();

			$extLists['components'] = $this->getComponents();
			$extLists['modules']    = $this->getModules();
			$extLists['templates']  = $this->getTemplates();
			$extLists['plugins']    = $this->getPlugins();

			// Load core languages first
			$this->load('joomla');

			// Load extensions languages after
			foreach ($extLists as $type => $extensions)
			{
				foreach ($extensions as $path => $name)
				{
					// Load user language
					$loaded = $this->load($name, $type . '/' . $path);

					if (!$loaded || $this->override)
					{
						$this->load($name);
					}

					// Load system language
					$loaded = $this->load($name . '.sys', $type . '/' . $path);

					if (!$loaded || $this->override)
					{
						$this->load($name . '.sys');
					}
				}
			}
		}

		return $this->strings;
	}

	/**
	 * Get the list of available language folders from
	 *
	 * @return  string[]
	 *
	 * @since   1.6.0
	 */
	public function getLanguageFiles()
	{
		if (!$this->files)
		{
			$folders = $this->getLanguageFolders();
			$files   = $this->getFiles($folders);

			if ($this->override)
			{
				/**
				 * Language priority order:
				 *
				 * >> language
				 *
				 * > components /com_abc /language
				 * > modules    /mod_abc /language
				 * > templates  /tpl_abc /language
				 * > plugins    /xyz/abc /language
				 */
				$ovrDir    = 'language/' . $this->language;
				$overrides = $this->getFiles(array($ovrDir));
				$files     = array_replace($files, $overrides);
			}

			$this->files = $files;
		}

		return $this->files;
	}

	/**
	 * Get the base language folders from
	 *
	 * @return  string[]
	 *
	 * @since   1.6.0
	 */
	public function getLanguageFolders()
	{
		if (!$this->folders)
		{
			$folders    = array();
			$extensions = array();

			$extensions['components'] = $this->getComponents();
			$extensions['modules']    = $this->getModules();
			$extensions['templates']  = $this->getTemplates();
			$extensions['plugins']    = $this->getPlugins();

			foreach ($extensions as $type => $names)
			{
				foreach ($names as $name)
				{
					$folders[] = $type . '/' . $name . '/language/' . $this->language;
				}
			}

			$closure = function ($folder) {
				return is_dir($this->baseDir . '/' . $folder);
			};

			$this->folders = array_values(array_filter($folders, $closure));
		}

		return $this->folders;
	}

	/**
	 * Get the language files list from the given language folders
	 *
	 * @param   string[]  $folders  The client application base path
	 *
	 * @return  string[]
	 *
	 * @since   1.6.0
	 */
	protected function getFiles($folders)
	{
		$files    = array();
		$filesSys = array();

		foreach ($folders as $folder)
		{
			try
			{
				$directory = new \DirectoryIterator($this->baseDir . '/' . $folder);

				foreach ($directory as $item)
				{
					if ($item->isFile())
					{
						$basename = $item->getBasename();

						if (substr($basename, -8) == '.sys.ini')
						{
							$filesSys[$basename] = $folder . '/' . $basename;
						}
						elseif (substr($basename, -4) == '.ini')
						{
							$files[$basename] = $folder . '/' . $basename;
						}
					}
				}
			}
			catch (\Exception $e)
			{
				// Skip this folder, it does not exist.
			}
		}

		return array_merge($filesSys, $files);
	}

	/**
	 * Get the components installed in the given client application base path
	 *
	 * @return  string[]
	 *
	 * @since   1.6.0
	 */
	protected function getComponents()
	{
		$components = array();

		try
		{
			$directory = new \DirectoryIterator($this->baseDir . '/components');

			foreach ($directory as $item)
			{
				if ($item->isDir() && !$item->isDot())
				{
					$name = $item->getBasename();

					$components[$name] = $name;
				}
			}
		}
		catch (\Exception $e)
		{
			// Skip this folder, it does not exist.
		}

		return $components;
	}

	/**
	 * Get the modules installed in the given client application base path
	 *
	 * @return  string[]
	 *
	 * @since   1.6.0
	 */
	protected function getModules()
	{
		$modules = array();

		try
		{
			$directory = new \DirectoryIterator($this->baseDir . '/modules');

			foreach ($directory as $item)
			{
				if ($item->isDir() && !$item->isDot())
				{
					$name = $item->getBasename();

					$modules[$name] = $name;
				}
			}
		}
		catch (\Exception $e)
		{
			// Skip this folder, it does not exist.
		}

		return $modules;
	}

	/**
	 * Get the templates installed in the given client application base path
	 *
	 * @return  string[]
	 *
	 * @since   1.6.0
	 */
	protected function getTemplates()
	{
		$templates = array();

		try
		{
			$directory = new \DirectoryIterator($this->baseDir . '/templates');

			foreach ($directory as $item)
			{
				if ($item->isDir() && !$item->isDot())
				{
					$name = $item->getBasename();

					$templates[$name] = 'tpl_' . $name;
				}
			}
		}
		catch (\Exception $e)
		{
			// Skip this folder, it does not exist.
		}

		return $templates;
	}

	/**
	 * Get the plugins installed in the given client application base path
	 *
	 * @return  string[]
	 *
	 * @since   1.6.0
	 */
	protected function getPlugins()
	{
		$plugins = array();

		try
		{
			$directory = new \DirectoryIterator($this->baseDir . '/plugins');

			foreach ($directory as $item)
			{
				if ($item->isDir() && !$item->isDot())
				{
					try
					{
						$folder = new \DirectoryIterator($item->getPathname());

						foreach ($folder as $i)
						{
							if ($i->isDir() && !$i->isDot())
							{
								$name = $i->getBasename();
								$type = $item->getBasename();
								$path = $type . '/' . $name;

								$plugins[$path] = 'plg_' . $type . '_' . $name;
							}
						}
					}
					catch (\Exception $e)
					{
						// Skip this folder, it does not exist.
					}
				}
			}
		}
		catch (\Exception $e)
		{
			// Skip this folder, it does not exist.
		}

		return $plugins;
	}

	/**
	 * Loads a single language file and appends the results to the existing strings
	 *
	 * @param   string  $extension  The extension for which a language file should be loaded
	 * @param   string  $basePath   The basepath to use, relative to the baseDir
	 * @param   string  $language   The language to load, defaults to current language property of this object
	 *
	 * @return  bool  True if the file has successfully loaded
	 *
	 * @since   1.6.0
	 */
	protected function load($extension = 'joomla', $basePath = '', $language = null)
	{
		if ($language === null)
		{
			$language = $this->language;
		}

		// Load the default language first
		if ($language != $this->default)
		{
			$this->load($extension, $basePath, $this->default);
		}

		$extension = $extension ?: 'joomla';
		$storeId   = "{$basePath}:{$language}:{$extension}";

		if (!isset($this->loaded[$storeId]))
		{
			$loaded  = false;
			$strings = LanguageHelper::load($extension, $language, $this->baseDir . '/' . $basePath);

			if (is_array($strings) && count($strings))
			{
				if (!isset($this->strings[$extension]))
				{
					$this->strings[$extension] = array();
				}

				$this->strings[$extension] = array_replace($this->strings[$extension], $strings);

				$loaded = true;
			}

			$this->loaded[$storeId] = $loaded;
		}

		return $this->loaded[$storeId];
	}
}
