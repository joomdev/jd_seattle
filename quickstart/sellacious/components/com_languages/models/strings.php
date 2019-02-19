<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Sellacious\Language\LanguageHelper;
use Sellacious\Language\LanguageIndexer;
use Sellacious\Language\LanguageText;
use Sellacious\Language\LanguageTranslator;

/**
 * Languages Strings Model
 *
 * @since  1.6.0
 */
class LanguagesModelStrings extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @see     JController
	 *
	 * @since   1.6.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'lang_constant', 'a.lang_constant',
				'orig_text', 'a.orig_text',
				'client', 'a.client',
				'extension', 'a.extension',
				'filename', 'a.filename',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$lang = $this->getUserStateFromRequest('com_languages.strings.list.language', 'language', '', 'string');

		$this->state->set('list.language', $lang);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string  An SQL query
	 *
	 * @since   1.6.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select all fields from the languages table.
		$query->select('a.*')->from($db->qn('#__languages_strings', 'a'));

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.lang_constant LIKE ' . $search . ' OR a.orig_text LIKE ' . $search . ')');
			}
		}

		if ($client = $this->getState('list.client'))
		{
			$query->where('a.client = ' . $db->q($client));
		}

		if ($extension = $this->getState('filter.extension'))
		{
			if (substr($extension, 0, 2) === 'g:')
			{
				$extensions = $this->getExtensionsByGroup(substr($extension, 2));

				if ($extensions)
				{
					$query->where('a.extension IN (' . implode(',', $db->q($extensions)) . ')');
				}
				else
				{
					$query->where('0');
				}
			}
			else
			{
				$query->where('a.extension = ' . $db->q($extension));
			}
		}

		if ($filename = $this->getState('filter.filename'))
		{
			$query->where('a.filename = ' . $db->q($filename));
		}

		$ordering = $this->state->get('list.fullordering', 'a.lang_constant ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws  Exception if there is an error in the form event.
	 *
	 * @since   1.6.0
	 */
	protected function preprocessForm(\JForm $form, $data, $group = 'content')
	{
		if ($form->getName() == 'com_languages.strings.filter' && ($extension = $this->getState('filter.extension')))
		{
			if (substr($extension, 0, 2) === 'g:')
			{
				$extensions = $this->getExtensionsByGroup(substr($extension, 2));
				$where      = $extensions ? 'extension IN (' . implode(',', $this->_db->q($extensions)) . ')' : '0';
			}
			else
			{
				$where = 'extension = ' . $this->_db->q($extension);
			}

			$form->setFieldAttribute('filename', 'sql_where', $where, 'filter');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method for refreshing the cache in the database with the known language strings.
	 *
	 * @return  bool  True on success, Exception object otherwise.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function reindex()
	{
		$language = 'en-GB';

		$this->clearIndex();

		$indexer = new LanguageIndexer($language, JPATH_BASE, false);
		$aSet    = $indexer->getLanguageStrings();

		$this->saveIndex($aSet, $language, 'sellacious');

		$indexer = new LanguageIndexer($language, JPATH_SITE, false);
		$bSet    = $indexer->getLanguageStrings();

		$this->saveIndex($bSet, $language, 'site');

		return true;
	}

	/**
	 * Save the indexed language file contents into the database
	 *
	 * @param   array   $aSet      The indexed data in the format {extension: {constant: text, ...}, ...}
	 * @param   string  $language  The language code
	 * @param   string  $client    The client application name
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function saveIndex($aSet, $language, $client)
	{
		$query = $this->_db->getQuery(true);

		$query->insert($this->_db->qn('#__languages_strings'))
			->columns($this->_db->qn(array('lang_constant', 'orig_text', 'client', 'extension', 'filename')));

		foreach ($aSet as $extension => $strings)
		{
			$query->clear('values');

			foreach ($strings as $constant => $text)
			{
				$filename = $extension == 'joomla' ? $language . '.ini' : $language . '.' . $extension . '.ini';
				$values   = array($constant, $text, $client, basename($extension, '.sys'), $filename);

				$query->values(implode(', ', $this->_db->q($values)));
			}

			try
			{
				$this->_db->setQuery($query)->execute();
			}
			catch (RuntimeException $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}
		}
	}

	/**
	 * Clear the indexed language file contents from the database
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function clearIndex()
	{
		$this->_db->setQuery('TRUNCATE TABLE ' . $this->_db->qn('#__languages_strings'))->execute();
	}

	/**
	 * Set a new translated text for the given language key
	 *
	 * @param   int     $id        The language id
	 * @param   string  $value     The translated text
	 * @param   string  $language  The target language code
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function setValue($id, $value, $language)
	{
		$query = $this->_db->getQuery(true);

		$query->select('a.*')
			->from($this->_db->qn('#__languages_strings', 'a'))
			->where('a.id = ' . (int) $id);

		$object = $this->_db->setQuery($query)->loadObject();

		if (!$object)
		{
			return false;
		}

		$client = JApplicationHelper::getClientInfo($object->client, true);

		if (!$client)
		{
			return false;
		}

		$sys    = substr($object->filename, -8) === '.sys.ini' ? '.sys' : '';
		$values = LanguageHelper::load($object->extension . $sys, $language, $client->path);

		if (!is_array($values))
		{
			return false;
		}

		// In sellacious we'd not require all original strings to be copied into the override,
		// only modified keys should be saved in the override, others will still be loaded from default
		if (strlen($value))
		{
			$values[$object->lang_constant] = $value;
		}
		else
		{
			unset($values[$object->lang_constant]);
		}

		list(, $basename) = explode('.', $object->filename, 2);

		$filename = "{$client->path}/language/{$language}/{$language}.{$basename}";

		LanguageHelper::saveToIniFile($filename, $values);

		return true;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items  The items loaded from the database using the list query
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6.0
	 */
	protected function processList($items)
	{
		$items = parent::processList($items);
		$lang  = $this->getState('list.language');

		if ($lang)
		{
			$language = new LanguageText($lang);
			$clients  = JApplicationHelper::getClientInfo();
			$clients  = ArrayHelper::pivot($clients, 'name');

			foreach ($items as &$item)
			{
				$client = ArrayHelper::getValue($clients, $item->client);

				if ($client)
				{
					$sys = substr($item->filename, -8) === '.sys.ini' ? '.sys' : '';

					$language->load($item->extension . $sys, $client->path, false, false);

					$item->override = $language->getString($item->lang_constant);
				}
			}
		}

		return $items;
	}

	/**
	 * Translate missing strings using Google API.
	 *
	 * @param   int[]  The language strings to translate
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function translate($pks)
	{
		$lang = $this->getState('list.language');

		if (!$lang)
		{
			throw new Exception(JText::_('COM_LANGUAGES_STRINGS_TRANSLATE_AUTO_SELECT_LANGUAGE'));
		}

		$query = $this->_db->getQuery(true);

		$query->select('a.*')->from($this->_db->qn('#__languages_strings', 'a'))->where('id IN (' . implode(', ', $pks) . ')');

		$iterator = $this->_db->setQuery($query)->getIterator();

		$translator = new LanguageTranslator($lang);
		$clients    = JApplicationHelper::getClientInfo();
		$paths      = ArrayHelper::getColumn($clients, 'path', 'name');
		$sets       = array();

		foreach ($iterator as $item)
		{
			$clientPath = ArrayHelper::getValue($paths, $item->client);

			if ($clientPath)
			{
				$storeId = "{$item->client}:{$item->filename}";

				if (!isset($sets[$storeId]))
				{
					$sys    = substr($item->filename, -8) === '.sys.ini' ? '.sys' : '';
					$values = LanguageHelper::load($item->extension . $sys, $lang, $clientPath);

					$sets[$storeId] = $values ?: array();
				}

				$sets[$storeId][$item->lang_constant] = $translator->translate($item->orig_text);
			}
		}

		foreach ($sets as $storeId => $strings)
		{
			if ($strings)
			{
				list($client, $file) = explode(':', $storeId);

				$clientPath = ArrayHelper::getValue($paths, $client);
				list(, $basename) = explode('.', $file, 2);

				$filename = "{$clientPath}/language/{$lang}/{$lang}.{$basename}";

				LanguageHelper::saveToIniFile($filename, $strings);
			}
		}
	}

	/**
	 * Method to get extensions list by group
	 *
	 * @param   string  $name  The group name
	 *
	 * @return  string[]
	 *
	 * @since  1.6.0
	 */
	protected function getExtensionsByGroup($name)
	{
		$extensions = (array) $this->helper->config->get('extensions_group');
		$extensions = array_filter($extensions);
		$values     = array();

		foreach ($extensions as $extension => $groups)
		{
			$groups = explode(',', $groups);

			foreach ($groups as $group)
			{
				if ($name == trim($group))
				{
					$values[] = $extension;
				}
			}
		}

		return $values;
	}

	/**
	 * Import the given excel file as language files
	 *
	 * @param   string  $filename  The full file path to the import source xlsx
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function importExcel($filename)
	{
		$language = $this->getState('list.language');

		if (!$language)
		{
			throw new Exception(JText::_('COM_LANGUAGES_STRINGS_TRANSLATE_AUTO_SELECT_LANGUAGE'));
		}

		$reader      = IOFactory::createReaderForFile($filename);
		$spreadsheet = $reader->load($filename);

		$records = $spreadsheet->getActiveSheet()->toArray();
		$translations = array();

		if (count($records) < 2 || count($records[0]) != 6)
		{
			throw new Exception(JText::_('COM_LANGUAGES_STRINGS_IMPORT_FILE_INVALID_DATA'));
		}

		/**
		 * client => [folder] => filename
		 *
		 * [0] => Language Constant
		 * [1] => Native Text
		 * [2] => Translated Text
		 * [3] => Location
		 * [4] => Extension Name
		 * [5] => Language File
		 *
		 */
		foreach ($records as $record)
		{
			$client = $record[3];
			$file   = $record[5];
			$code   = $record[0];
			$value  = $record[2];

			if (($client == 'site' || $client == 'sellacious') && $file != '' && $code != '' && $value != '')
			{
				$translations[$client][$file][$code] = $value;
			}
		}

		$clients = JApplicationHelper::getClientInfo();
		$paths   = ArrayHelper::getColumn($clients, 'path', 'name');

		foreach ($translations as $clientName => $files)
		{
			$clientPath = ArrayHelper::getValue($paths, $clientName);

			foreach ($files as $file => $strings)
			{
				list(, $extension) = explode('.', basename($file, '.ini'), 2);

				$values   = LanguageHelper::load($extension, $language, $clientPath);
				$filename = "{$clientPath}/language/{$language}/{$language}.{$extension}.ini";

				if (is_array($values))
				{
					$strings = array_replace($values, $strings);
				}

				$strings = array_filter($strings, 'strlen');

				LanguageHelper::saveToIniFile($filename, $strings);
			}
		}
	}
}
