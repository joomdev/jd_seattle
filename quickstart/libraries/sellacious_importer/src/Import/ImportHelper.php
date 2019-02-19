<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import;

// no direct access
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Import utility class
 *
 * @since   1.5.2
 */
class ImportHelper
{
	/**
	 * @var    array
	 *
	 * @since   1.5.2
	 */
	private static $instances = array();

	/**
	 * Get instance of the selected importer
	 *
	 * @param   string  $name
	 *
	 * @return  AbstractImporter
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	public static function getImporter($name)
	{
		$class = 'Sellacious\\Import\\' . ucfirst($name) . 'Importer';

		if (!isset(static::$instances[$name]))
		{
			if (!class_exists($class))
			{
				throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_IMPORTER_PREMIUM_FEATURE_NOTICE_UNAVAILABLE', $name));
			}

			static::$instances[$name] = new $class;
		}

		return static::$instances[$name];
	}

	/**
	 * Method to get a list of import templates
	 *
	 * @param   string  $handler  The type of import
	 * @param   int     $userId   User id to filter the list for (sellacious >= v2.0.0)
	 * @param   bool    $extra    Whether to include global templates in addition to selected user
	 *
	 * @return  \stdClass[]
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	public static function getTemplates($handler = null, $userId = null, $extra = false)
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->qn('#__importer_templates', 'a'))
			->where('a.state = 1');

		if ($handler)
		{
			$query->where('a.import_type = ' . $db->q($handler));
		}

		/**
		 * Filter by $userId not supporter until Sellacious v2.0.0
		 */
		$templates = (array) $db->setQuery($query)->loadObjectList('id');

		foreach ($templates as $template)
		{
			$template->mapping = json_decode($template->mapping, true) ?: array();
			$template->params  = json_decode($template->params, true) ?: array();
		}

		return $templates;
	}

	/**
	 * Method to get a selected import template by id
	 *
	 * @param   int  $id  The import template id
	 *
	 * @return  \stdClass
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	public static function getTemplate($id)
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->qn('#__importer_templates', 'a'))
			->where('a.id = ' . (int) $id);

		$template = $db->setQuery($query)->loadObject();

		if (isset($template, $template->mapping))
		{
			$template->mapping = json_decode($template->mapping, true) ?: array();
			$template->params  = json_decode($template->params, true) ?: array();
		}

		return $template;
	}

	/**
	 * Method to get a selected import job by id
	 *
	 * @param   int  $id  The import job id
	 *
	 * @return  ImportRecord
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	public static function getImport($id)
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')
		      ->from($db->qn('#__importer_imports', 'a'))
		      ->where('a.id = ' . (int) $id);

		$result = $db->setQuery($query)->loadObject();

		$import = new ImportRecord($result);

		return $import;
	}
}
