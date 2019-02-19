<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2017 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('JPATH_PLATFORM') or die;

/**
 * Class SellaciousInstallerScript
 *
 * @since   1.0.0
 */
class SellaciousInstallerScript
{
	/**
	 * @var  SimpleXMLElement
	 *
	 * @since   1.0.0
	 */
	protected $menu;

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $installDir;

	/**
	 * Method to run before an install/update/uninstall method.
	 * Used to warn user that core package needs to be installed first before installing this one.
	 *
	 * @param   string                 $type
	 * @param   JInstallerAdapterFile  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function preFlight($type, $installer)
	{
		$this->fixInstallTarget($installer);
	}

	/**
	 * Method to install the component.
	 * Used to mark installation records for backoffice extensions and create backoffice menu.
	 *
	 * @param   JInstallerAdapterFile  $installer
	 *
	 * @since   1.0.0
	 */
	public function install($installer)
	{
		// Install backoffice extensions
		$this->installSql();

		// Create menu items for backoffice
		$root = $installer->getParent()->getPath('source');
		$file = $root . '/sellacious/menu.xml';

		$this->rebuildMenu($file, true);

		// Todo: Provide a rollback mechanism by pushing appropriate step to the JInstallerAdapter
	}

	/**
	 * Called on update
	 *
	 * @param   JInstallerAdapterFile  $installer  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0.0
	 */
	public function update($installer)
	{
		// Install missing backoffice extensions
		$this->installSql();

		// Menu items for backoffice
		// After v1.5.0 we don't touch the menu as it is customisable by the user and we must not mess it up.

		return true;
	}

	/**
	 * Method to uninstall the component.
	 * Used to remove the sellacious backoffice files in case it is missed by Joomla uninstall handler.
	 *
	 * @param   JInstallerAdapterFile  $installer
	 *
	 * @since   1.0.0
	 */
	public function uninstall($installer)
	{
		// We allow renaming of 'sellacious' folder, let the un-installer know the current name.
		$this->fixInstallTarget($installer);

		$this->uninstallSql();
	}

	/**
	 * Rebuild backoffice menu from the xml
	 *
	 * @param   string  $file   The menu xml file to be loaded
	 * @param   bool    $clear  Whether to clear all the existing menu items before recreating them.
	 *
	 * @since   1.5.0
	 */
	public function rebuildMenu($file, $clear = false)
	{
		if (!file_exists($file))
		{
			return;
		}

		$menu = simplexml_load_file($file);

		if (!($menu instanceof SimpleXMLElement))
		{
			return;
		}

		// Make sure the target menu type exists
		$menuType = JTable::getInstance('MenuType');
		$menuType->load(array('menutype' => $menu['menutype']));

		// Client id is only available with J3.7+
		if ($menuType->get('client_id') != 2)
		{
			$type = array(
				'menutype'    => $menu['menutype'],
				'title'       => 'Sellacious Backoffice Menu',
				'description' => 'Menu for Sellacious Backoffice',
			);

			if (property_exists($menuType, 'client_id'))
			{
				$type['client_id'] = 2;
			}

			$menuType->bind($type);
			$menuType->check();
			$menuType->store();
		}

		if ($clear)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('#__menu')->where('menutype = ' . $db->q($menu['menutype']));

			$db->setQuery($query)->execute();
		}

		$this->createMenu($menu);
	}

	/**
	 * Create the menu for sellacious Backoffice
	 *
	 * @param   SimpleXMLElement  $menu       The menu node to process
	 * @param   array             $inherited  A menu table object for the parent menu reference
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function createMenu(SimpleXMLElement $menu, array $inherited = array())
	{
		// Merge given attributes from parent menu item into the current
		$menuAttr   = $menu->attributes();
		$properties = $inherited;

		// Merge doesn't work with attributes iterator
		foreach ($menuAttr as $key => $property)
		{
			$properties[$key] = (string) $property;
		}

		$attributes = $menu->xpath('attributes');
		$attributes = array_merge($properties, array_map('strval', (array) $attributes[0]));

		// If we have an id use it, else create the menu with given properties
		if (empty($attributes['id']))
		{
			/** @var  JTableMenu  $table */
			$table = JTable::getInstance('Menu');
			$keys  = array(
				'link'      => $attributes['link'],
				'client_id' => $attributes['client_id'],
			);

			if ($attributes['link'] == '' || $attributes['link'] == '#')
			{
				$keys['alias'] = $attributes['alias'];
			}

			$table->load($keys);

			if ($table->get('id'))
			{
				$attributes['id'] = $table->get('id');
			}

			if (isset($attributes['component']))
			{
				$component = JComponentHelper::getComponent($attributes['component']);

				$attributes['component_id'] = $component->id;

				unset($attributes['component']);
			}

			$table->bind($attributes);
			$table->setLocation($table->get('parent_id'), 'last-child');
			$table->check();
			$table->store();

			$attributes['id'] = $table->get('id');
		}

		$properties['parent_id'] = $attributes['id'];

		// Now create the child menu items for this menu item
		foreach ($menu->xpath('menu') as $child)
		{
			$this->createMenu($child, $properties);
		}
	}

	/**
	 * Get the base path for sellacious backend directory. Tries to automatically resolve and update any change in path
	 *
	 * @param   bool  $absolute  Whether to return an absolute physical path
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function getAppPath($absolute = false)
	{
		static $appPath;

		// If sellacious directory is not already identified, evaluate it.
		if (!isset($appPath))
		{
			jimport('joomla.filesystem.folder');

			$folders = JFolder::folders(JPATH_SITE);

			foreach ($folders as $folder)
			{
				$filename = JPATH_SITE . '/' . $folder . '/sellacious.xml';

				if (file_exists($filename) && ($xml = simplexml_load_file($filename)) && ($xml instanceof SimpleXMLElement))
				{
					if ($xml['type'] == 'application' && strtolower($xml->name) == 'sellacious')
					{
						$appPath = $folder;

						break;
					}
				}
			}

			// WARNING: Make sure we are in a subdirectory, prevent accidental deletion of entire site root.
			if (strlen($appPath) == 0 || !is_dir(JPATH_SITE . '/' . $appPath))
			{
				$appPath = false;
			}
		}

		return $appPath ? ($absolute ? JPATH_SITE . '/' . $appPath : $appPath) : false;
	}

	/**
	 * Install extensions for the backoffice
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function installSql()
	{
		$queries = array();

		$this->getExtensionInstall($queries);
		$this->getTemplateInstall($queries);
		$this->getModuleInstall($queries);

		$db = JFactory::getDbo();

		foreach ($queries as $query)
		{
			if ($query)
			{
				$db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Remove all SQL records that were inserted for this installation
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function uninstallSql()
	{
		$queries = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$queries[] = (string) $query->clear()->delete('#__extensions')->where('client_id = 2');
		$queries[] = (string) $query->clear()->delete('#__template_styles')->where('client_id = 2');
		$queries[] = (string) $query->clear()->delete('#__menu')->where('client_id = 2');
		$queries[] = (string) $query->clear()->delete('#__menu_types')->where("menutype = 'sellacious-menu'");
		$queries[] = (string) $query->clear()->delete('#__modules')->where('client_id = 2');
		$queries[] = (string) $query->clear()->delete('#__modules_menu')->where('moduleid NOT IN (SELECT id FROM #__modules)');

		foreach ($queries as $query)
		{
			$db->setQuery($query)->execute();
		}
	}

	/**
	 * Install backoffice extensions
	 *
	 * @param   array  $queries  The array to be populated
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function getExtensionInstall(array &$queries)
	{
		$columns    = 'name, type, element, folder, client_id, enabled, access, protected, manifest_cache';
		$extensions = array(
			array('sellacious', 'template', 'sellacious', '', 2, 1, 1, 1, '{"creationDate":"November 28, 2015","author":"Izhar Aazmi","copyright":"Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.","authorEmail":"info@bhartiy.com","authorUrl":"www.bhartiy.com","version":"1.4.0"}'),
			array('mod_custom', 'module', 'mod_custom', '', 2, 1, 1, 1, '{"creationDate":"November 28, 2015","author":"Izhar Aazmi","copyright":"Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.","authorEmail":"info@bhartiy.com","authorUrl":"www.bhartiy.com","version":"1.4.0"}'),
			array('mod_login', 'module', 'mod_login', '', 2, 1, 1, 1, '{"creationDate":"November 28, 2015","author":"Izhar Aazmi","copyright":"Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.","authorEmail":"info@bhartiy.com","authorUrl":"www.bhartiy.com","version":"1.4.0"}'),
			array('mod_toolbar', 'module', 'mod_toolbar', '', 2, 1, 1, 1, '{"creationDate":"November 28, 2015","author":"Izhar Aazmi","copyright":"Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.","authorEmail":"info@bhartiy.com","authorUrl":"www.bhartiy.com","version":"1.4.0"}'),
			array('mod_smartymenu', 'module', 'mod_smartymenu', '', 2, 1, 2, 1, '{"creationDate":"November 28, 2015","author":"Izhar Aazmi","copyright":"Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.","authorEmail":"info@bhartiy.com","authorUrl":"www.bhartiy.com","version":"1.4.0"}'),
			array('mod_title', 'module', 'mod_title', '', 2, 1, 1, 1, '{"creationDate":"November 28, 2015","author":"Izhar Aazmi","copyright":"Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.","authorEmail":"info@bhartiy.com","authorUrl":"www.bhartiy.com","version":"1.4.0"}'),
			array('mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', 2, 1, 1, 1, '{"creationDate":"November 28, 2015","author":"Izhar Aazmi","copyright":"Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.","authorEmail":"info@bhartiy.com","authorUrl":"www.bhartiy.com","version":"1.4.0"}'),
			array('mod_footer', 'module', 'mod_footer', '', 2, 1, 1, 1, '{"creationDate":"March 12, 2016","author":"Izhar Aazmi","copyright":"Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.","authorEmail":"info@bhartiy.com","authorUrl":"www.bhartiy.com","version":"1.4.0"}'),
		);

		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$records = array();

		$query->select('extension_id')->from('#__extensions');

		foreach ($extensions as $extension)
		{
			$where = array(
				'type = ' . $db->q($extension[1]),
				'element = ' . $db->q($extension[2]),
				'folder = ' . $db->q($extension[3]),
				'client_id = ' . $db->q($extension[4]),
			);

			$query->clear('where')->where($where);
			$exists = $db->setQuery($query)->loadResult();

			if (!$exists)
			{
				$records[] = $extension;
			}
		}

		$queries[] = $this->getInsertQuery('#__extensions', $columns, $records);
	}

	/**
	 * Install backoffice template styles
	 *
	 * @param   array  $queries
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function getTemplateInstall(array &$queries)
	{
		$columns = 'template, client_id, home, title';
		$styles  = array(
			array('sellacious', 2, 1, 'Sellacious - Backoffice'),
		);

		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$records = array();

		$query->select('id')->from('#__template_styles');

		foreach ($styles as $style)
		{
			$where = array(
				'template = ' . $db->q($style[0]),
				'client_id = ' . $db->q($style[1]),
			);

			$query->clear('where')->where($where);
			$exists = $db->setQuery($query)->loadResult();

			if (!$exists)
			{
				$records[] = $style;
			}
		}

		$queries[] = $this->getInsertQuery('#__template_styles', $columns, $records);
	}

	/**
	 * Install backoffice modules
	 *
	 * @param   array  $queries  The array to be populated
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function getModuleInstall(array &$queries)
	{
		$columns = 'title, content, ordering, position, published, module, access, showtitle, params, client_id, language';
		$modules = array(
			array('Title', '', 1, 'title', 1, 'mod_title', 3, 1, '', 2, '*'),
			array('Toolbar', '', 1, 'toolbar', 1, 'mod_toolbar', 1, 1, '', 2, '*'),
			array('Smarty Menu', '', 1, 'menu', 1, 'mod_smartymenu', 1, 0, '{"menutype":"sellacious-menu","layout":"_:default","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 2, '*'),
			array('Breadcrumbs', '', 1, 'ribbon-left', 1, 'mod_breadcrumbs', 1, 1, '{"moduleclass_sfx":"","showHome":"1","homeText":"","showComponent":"1","separator":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 2, '*'),
			array('Footer', '', 1, 'footer', 1, 'mod_footer', 1, 1, '{"moduleclass_sfx":""}', 2, '*'),
		);

		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$records = array();

		$query->select('id')->from('#__modules');

		foreach ($modules as $module)
		{
			$where = array(
				'module = ' . $db->q($module[5]),
				'client_id = ' . $db->q($module[9]),
			);

			$query->clear('where')->where($where);
			$exists = $db->setQuery($query)->loadResult();

			if (!$exists)
			{
				$records[] = $module;
			}
		}

		$query = $this->getInsertQuery('#__modules', $columns, $records);

		if ($query)
		{
			$queries[] = $query;

			$subQuery  = $db->getQuery(true)->select('id, 0')->from('#__modules')->where('client_id = 2');
			$queries[] = 'INSERT IGNORE ' . 'INTO #__modules_menu (moduleid, menuid) ' . (string) $subQuery;
		}
	}

	/**
	 * Get insert query for the table with given column names and data rows
	 *
	 * @param   string  $table    The table name
	 * @param   mixed   $columns  The comma separated column names, or an array of column names
	 * @param   array   $records  The data rows
	 *
	 * @return  string  The insert query
	 *
	 * @since   1.0.0
	 */
	protected function getInsertQuery($table, $columns, $records)
	{
		if (count($records))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->insert($table)->columns($columns);

			foreach ($records as $record)
			{
				$query->values(implode(', ', $db->q($record)));
			}

			return $query;
		}

		return false;
	}

	/**
	 * We allow renaming of 'sellacious' folder, let the un-installer know the current name.
	 *
	 * @param   \JInstallerAdapterFile  $installer
	 *
	 * @since   1.5.2
	 */
	protected function fixInstallTarget($installer)
	{
		try
		{
			$xml = $installer->getManifest();

			if ($installDir = $this->getAppPath(false))
			{
				foreach ($xml->fileset->files as $file)
				{
					if ($file['target'] == 'sellacious')
					{
						$file['target'] = $installDir;
					}
				}
			}
		}
		catch (Exception $e)
		{
		}
	}
}
