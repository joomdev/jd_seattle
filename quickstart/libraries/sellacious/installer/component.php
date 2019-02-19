<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Installer script base class for sellacious component.
 *
 * @since   1.5.0
 */
class SellaciousInstallerComponent extends SellaciousInstallerAdapter
{
	/**
	 * @var   SellaciousHelper
	 *
	 * @since   1.5.0
	 */
	protected $helper;

	/**
	 * The list of current files that are installed and is read from the manifest on disk in the update
	 * area to handle doing a diff and deleting files that are in the old files list and not in the new files list.
	 *
	 * @var   SimpleXMLElement
	 *
	 * @since   1.5.0
	 */
	protected $oldFiles;

	/**
	 * SellaciousInstallerComponent constructor.
	 *
	 * @since   1.5.0
	 */
	public function __construct()
	{
		try
		{
			$this->helper = SellaciousHelper::getInstance();
		}
		catch (Exception $e)
		{
		}

		$this->detectPath();
	}

	/**
	 * This method is called after a component is installed.
	 *
	 * @param   JInstaller  $installer  Parent object calling this method.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function install($installer)
	{
		if ($files = $installer->getManifest()->sellacious->files)
		{
			$result = $this->parseFiles($files, $installer);

			if ($result === false)
			{
				throw new RuntimeException(
					JText::sprintf('LIB_SELLACIOUS_INSTALLER_ABORT_COMPONENT_COPY_FILES_FAILED', JText::_('JLIB_INSTALLER_INSTALL'))
				);
			}
		}
	}

	/**
	 * This method is called after a component is uninstalled.
	 *
	 * @param   JInstaller  $installer  Parent object calling this method.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function uninstall($installer)
	{
		// JPATH_SELLACIOUS is not available when upgrading from v1.5.3 or lower
		JFolder::delete(JPATH_ROOT . '/' . JPATH_SELLACIOUS_DIR . '/components/' . $installer->get('element'));
	}

	/**
	 * This method is called after a component is updated.
	 *
	 * @param   JInstaller  $installer  Parent object calling object.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function update($installer)
	{
		if ($files = $installer->getManifest()->sellacious->files)
		{
			if (false === $this->parseFiles($files, $installer))
			{
				throw new RuntimeException(
					JText::sprintf('LIB_SELLACIOUS_INSTALLER_ABORT_COMPONENT_COPY_FILES_FAILED', JText::_('JLIB_INSTALLER_UPDATE'))
				);
			}
		}
	}

	/**
	 * Runs just before any installation action is preformed on the component.
	 * Verifications and pre-requisites should run in this function.
	 *
	 * @param   string      $type       Type of PreFlight action. Possible values are: install, update, discover_install
	 * @param   JInstaller  $installer  Parent object calling object.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function preflight($type, $installer)
	{
		if ($type == 'update')
		{
			$path = $installer->get('parent')->getPath('extension_administrator');

			// Use a temporary instance due to side effects
			$tmpInstaller = new JInstaller;
			$tmpInstaller->setPath('source', $path);

			if ($tmpInstaller->findManifest() && ($manifest = $tmpInstaller->getManifest()))
			{
				$this->oldFiles = $manifest->sellacious->files;
			}
		}
	}

	/**
	 * Runs right after any installation action is preformed on the component.
	 *
	 * @param   string      $type       Type of PostFlight action. Possible values are: install, update, discover_install
	 * @param   JInstaller  $installer  Parent object calling object.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function postflight($type, $installer)
	{
	}

	/**
	 * Method to parse through a files element of the installation manifest and take appropriate action.
	 *
	 * @param   SimpleXMLElement  $newFiles   The XML node to process
	 * @param   JInstaller        $installer  List of old files
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function parseFiles($newFiles, $installer)
	{
		// Get the array of file nodes to process; we checked whether this had children above.
		$newEntries = $newFiles instanceOf SimpleXMLElement ? $newFiles->children() : array();

		if (count($newEntries) == 0)
		{
			return 0;
		}

		$copyFiles   = array();
		$destination = JPATH_ROOT . '/' . JPATH_SELLACIOUS_DIR . '/components/' . $installer->get('element');

		$folder = (string) $newFiles->attributes()->folder;

		if ($folder && file_exists($installer->get('parent')->getPath('source') . '/' . $folder))
		{
			$source = $installer->get('parent')->getPath('source') . '/' . $folder;
		}
		else
		{
			$source = $installer->get('parent')->getPath('source');
		}

		// Work out what files have been deleted
		if ($this->oldFiles && ($this->oldFiles instanceof SimpleXMLElement))
		{
			/** @var  SimpleXMLElement[] $oldEntries */
			$oldEntries = $this->oldFiles->children();

			if (count($oldEntries))
			{
				$deletions = $installer->get('parent')->findDeletedFiles($oldEntries, $newEntries);

				foreach ($deletions['folders'] as $deleted_folder)
				{
					JFolder::delete($destination . '/' . $deleted_folder);
				}

				foreach ($deletions['files'] as $deleted_file)
				{
					JFile::delete($destination . '/' . $deleted_file);
				}
			}
		}

		$path = array();

		// Process each file in the $files array (children of $tagName).
		foreach ($newEntries as $file)
		{
			/** @var  SimpleXMLElement  $file */
			$path['src']  = $source . '/' . $file;
			$path['dest'] = $destination . '/' . $file;
			$path['type'] = ($file->getName() == 'folder') ? 'folder' : 'file';

			 // We need to ensure that the folder we are copying our file to exists
			if (basename($path['dest']) != $path['dest'])
			{
				$dir = dirname($path['dest']);

				if (!JFolder::create($dir))
				{
					JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_CREATE_DIRECTORY', $dir), JLog::WARNING, 'jerror');

					return false;
				}
			}

			// Add the file to the copy files array
			$copyFiles[] = $path;
		}

		return $installer->get('parent')->copyFiles($copyFiles);
	}
}
