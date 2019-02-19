<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Installer script base class for sellacious module.
 *
 * @since   1.6.0
 */
class SellaciousInstallerModule extends SellaciousInstallerAdapter
{
	/**
	 * @var   SellaciousHelper
	 *
	 * @since   1.6.0
	 */
	protected $helper;

	/**
	 * The list of current files that are installed and is read from the manifest on disk in the update
	 * area to handle doing a diff and deleting files that are in the old files list and not in the new files list.
	 *
	 * @var   SimpleXMLElement
	 *
	 * @since   1.6.0
	 */
	protected $oldFiles;

	/**
	 * SellaciousInstallerModule constructor.
	 *
	 * @since   1.6.0
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
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function install($installer)
	{
		$result = $this->parseFiles($installer);

		if ($result === false)
		{
			throw new RuntimeException(
				JText::sprintf('LIB_SELLACIOUS_INSTALLER_ABORT_MODULE_COPY_FILES_FAILED', JText::_('JLIB_INSTALLER_INSTALL'))
			);
		}
	}

	/**
	 * This method is called after a component is uninstalled.
	 *
	 * @param   JInstaller  $installer  Parent object calling this method.
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function uninstall($installer)
	{
		// JPATH_SELLACIOUS is not available when upgrading from v1.5.3 or lower
		JFolder::delete(JPATH_ROOT . '/' . JPATH_SELLACIOUS_DIR . '/joomla/manifests/modules/' . $installer->get('element'));
	}

	/**
	 * This method is called after a component is updated.
	 *
	 * @param   JInstaller  $installer  Parent object calling object.
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function update($installer)
	{
		if (false === $this->parseFiles($installer))
		{
			throw new RuntimeException(
				JText::sprintf('LIB_SELLACIOUS_INSTALLER_ABORT_MODULE_COPY_FILES_FAILED', JText::_('JLIB_INSTALLER_UPDATE'))
			);
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
	 * @since   1.6.0
	 */
	public function preflight($type, $installer)
	{
	}

	/**
	 * Runs right after any installation action is preformed on the component.
	 *
	 * @param   string      $type       Type of PostFlight action. Possible values are: install, update, discover_install
	 * @param   JInstaller  $installer  Parent object calling object.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function postflight($type, $installer)
	{
	}

	/**
	 * Method to parse through a files element of the installation manifest and take appropriate action.
	 *
	 * @param   JInstaller        $installer  List of old files
	 *
	 * @return  boolean  True on success
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function parseFiles($installer)
	{
		$copyFiles   = array();
		$destination = JPATH_ROOT . '/' . JPATH_SELLACIOUS_DIR . '/joomla/manifests/modules/' . $installer->get('element');
		$source      = $installer->get('parent')->getPath('source');

		if (!JFolder::exists($destination))
		{
			JFolder::create($destination);
		}

		$file         = 'config.xml';
		$path['src']  = $source . '/' . $file;
		$path['dest'] = $destination . '/' . $file;
		$path['type'] = 'file';

		// Add the file to the copy files array
		$copyFiles[] = $path;

		return $installer->get('parent')->copyFiles($copyFiles);
	}
}
