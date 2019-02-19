<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access

defined('JPATH_PLATFORM') or die;

/**
 * This is a workaround against the php time limit which causes installation to stop abruptly.
 * This is so far observed only on windows platform, but can occur anywhere.
 *
 * We should fix the issue soon as why the simple install sql is taking so much time.
 */
set_time_limit(600);

/**
 * @package   Sellacious
 *
 * @since     1.4.4
 */
class com_sellaciousInstallerScript
{
	/**
	 * @var  string
	 *
	 * @since   1.5.0
	 */
	protected $version;

	/**
	 * Method to run before process start
	 *
	 * @param   string                    $route
	 * @param   JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.4
	 */
	public function preflight($route, $installer)
	{
		if ($route == 'update')
		{
			/** @var  JTableExtension $extension */
			$extension = JTable::getInstance('Extension');
			$extension->load(array('element' => 'com_sellacious', 'type' => 'component', 'client_id' => 1));

			if (!$extension->get('extension_id'))
			{
				return;
			}

			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$extId = $extension->get('extension_id');

				$query->select('version_id')
					->from('#__schemas')
					->where('extension_id = ' . (int) $extId);

				$version = $db->setQuery($query)->loadResult();

				$this->version = $this->fixVersion($extId, $version);
			}
			catch (RuntimeException $e)
			{
			}
		}
	}

	/**
	 * Method to run after process finish
	 *
	 * @param   string                    $route
	 * @param   JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function postflight($route, $installer)
	{
		if ($this->version && version_compare($this->version, '1.5.0', '<'))
		{
			$this->fixFields();
		}
	}

	/**
	 * Update the fields table to use Json only for non string values. This must run after update as we need the new database structure.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	protected function fixFields()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.is_json, a.field_value, a.field_html')
			->from($db->qn('#__sellacious_field_values', 'a'))
			->order('a.id');

		$iterator = $db->setQuery($query)->getIterator();

		foreach ($iterator as $obj)
		{
			if (!empty($obj->field_value))
			{
				$value = json_decode($obj->field_value);

				// We concern only a valid JSON, others would be assumed to be non-json values.
				if (json_last_error() == JSON_ERROR_NONE)
				{
					if (is_scalar($value))
					{
						$obj->is_json = 0;
						$obj->field_value = $value;
					}
					else
					{
						$obj->is_json = 1;
					}

					$db->updateObject('#__sellacious_field_values', $obj, array('id'));
				}
			}
		}
	}

	/**
	 * Insert the missing version number in sellacious 1.4.1 before we attempt to update it.
	 *
	 * @param   int     $extId    The sellacious component extension id
	 * @param   string  $version  The current version number
	 *
	 * @return  string  The current or modified version number as the case may be.
	 *
	 * @since   1.5.0
	 */
	protected function fixVersion($extId, $version)
	{
		// Only v1.4.1 in public release has this issue.
		if (!$version)
		{
			$version = '1.4.1';
			$db      = JFactory::getDbo();
			$query   = $db->getQuery(true);

			$query->insert('#__schemas')
				->columns('extension_id, version_id')
				->values((int) $extId . ', ' . $db->q($version));

			$db->setQuery($query)->execute();
		}

		return $version;
	}
}
