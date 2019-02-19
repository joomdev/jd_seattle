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

/**
 * Languages Installer Model
 *
 * @since  1.6.0
 */
class LanguagesModelInstall extends SellaciousModel
{
	/**
	 * Install an extension from either folder, URL or upload.
	 *
	 * @return  void  Result of install.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function install()
	{
		$package   = $this->getPackageFromUrl();
		$langCode  = $this->app->input->getString('lang_code');
		$installer = JInstaller::getInstance();

		$installer->setPath('source', $package['dir']);

		// Install the package.
		$installed = $installer->install($package['dir']);

		$this->app->setUserState('com_installer.message', $installer->message);
		$this->app->setUserState('com_installer.extension_message', $installer->get('extension_message'));

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		if (!$installed)
		{
			throw new Exception(JText::_('COM_LANGUAGES_INSTALL_INSTALL_ERROR'));
		}

		try
		{
			$query = $this->_db->getQuery(true);
			$query->update($this->_db->qn('#__languages'))
				->set('published = 1')
				->where('lang_code = ' . $this->_db->q($langCode));
			$this->_db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// Ignore, content language will be unpublished
		}

		$this->installClientLanguage($langCode);

		// Clear the cached extension data and menu cache
		$this->cleanCache('_system', 0);
		$this->cleanCache('_system', 1);
		$this->cleanCache('com_modules', 0);
		$this->cleanCache('com_modules', 1);
		$this->cleanCache('com_plugins', 0);
		$this->cleanCache('com_plugins', 1);
		$this->cleanCache('mod_menu', 0);
		$this->cleanCache('mod_menu', 1);
	}

	/**
	 * Install the required files for application client for language pack installation
	 *
	 * @param   string  $langCode  The target language code
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 *
	 * @see     JApplicationHelper::getClientInfo()
	 */
	protected function installClientLanguage($langCode)
	{
		$languages = JLanguageHelper::getInstalledLanguages(1, true, true);

		if (!isset($languages[$langCode]))
		{
			return false;
		}

		$files = array(
			"language/$langCode/install.xml",
			"language/$langCode/$langCode.localise.php",
			"language/$langCode/$langCode.xml",
			"language/$langCode/$langCode.ini",
			"language/$langCode/$langCode.lib_joomla.ini",
		);

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		if (!is_dir(JPATH_BASE . "/language/$langCode"))
		{
			JFolder::create(JPATH_BASE . "/language/$langCode");
		}

		foreach ($files as $file)
		{
			// We should replace 1 => 2 and 'administrator' => 'sellacious'. Hold for later
			if (is_file(JPATH_ADMINISTRATOR . '/' . $file) && !is_file(JPATH_BASE . '/' . $file))
			{
				JFile::copy(JPATH_ADMINISTRATOR . '/' . $file, JPATH_BASE . '/' . $file);
			}
		}

		// Install into the database
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.extension_id')
			->from($db->qn('#__extensions', 'a'))
			->where('a.client_id = 2')
			->where('a.type = ' . $db->q('language'))
			->where('a.element = ' . $db->q($langCode));

		$extId = $db->setQuery($query)->loadResult();

		if (!$extId)
		{
			$language    = $languages[$langCode];
			$extension   = (object) array(
				'extension_id'     => null,
				'package_id'       => 0,
				'name'             => $language->metadata['name'],
				'type'             => 'language',
				'element'          => !empty($language->metadata['tag']) ? $language->metadata['tag'] : $langCode,
				'folder'           => '',
				'client_id'        => '2',
				'enabled'          => '1',
				'access'           => '1',
				'protected'        => '0',
				'manifest_cache'   => json_encode(array(
					'name'         => !empty($language->manifest['name']) ? $language->manifest['name'] : '',
					'type'         => !empty($language->manifest['type']) ? $language->manifest['type'] : '',
					'creationDate' => !empty($language->manifest['creationDate']) ? $language->manifest['creationDate'] : '',
					'author'       => !empty($language->manifest['author']) ? $language->manifest['author'] : '',
					'copyright'    => !empty($language->manifest['copyright']) ? $language->manifest['copyright'] : '',
					'authorEmail'  => !empty($language->manifest['authorEmail']) ? $language->manifest['authorEmail'] : '',
					'authorUrl'    => !empty($language->manifest['authorUrl']) ? $language->manifest['authorUrl'] : '',
					'version'      => !empty($language->manifest['version']) ? $language->manifest['version'] : '',
					'description'  => !empty($language->manifest['description']) ? $language->manifest['description'] : '',
					'group'        => !empty($language->manifest['group']) ? $language->manifest['group'] : '',
				)),
				'params'           => '',
				'custom_data'      => '',
				'system_data'      => '',
				'checked_out'      => '0',
				'checked_out_time' => '0000-00-00 00:00:00',
				'ordering'         => '0',
				'state'            => '0',
			);

			$db->insertObject('#__extensions', $extension, 'extension_id');
		}

		return true;
	}

	/**
	 * Install an extension from a URL.
	 *
	 * @return  array  Package details or false on failure.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function getPackageFromUrl()
	{
		$url = $this->app->input->getString('install_url');

		if (!$url)
		{
			throw new Exception(JText::sprintf('COM_LANGUAGES_INSTALL_MSG_INSTALL_INVALID_URL', ''));
		}

		// Handle updater XML file case:
		if (preg_match('/\.xml\s*$/', $url))
		{
			$update = new JUpdate;
			$update->loadFromXml($url);

			$package_url = trim($update->get('downloadurl', false)->_data);

			if ($package_url)
			{
				$url = $package_url;
			}

			unset($update);
		}

		// Download the package at the URL given.
		$pkgFile = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$pkgFile)
		{
			throw new Exception(JText::sprintf('COM_LANGUAGES_INSTALL_MSG_INSTALL_INVALID_URL', $url));
		}

		// Unpack the downloaded package file.
		$package = JInstallerHelper::unpack($this->app->get('tmp_path') . '/' . $pkgFile, true);

		if (!is_array($package) || !isset($package['dir']) || !is_dir($package['dir']))
		{
			throw new Exception(JText::_('COM_LANGUAGES_INSTALL_UNABLE_TO_FIND_INSTALL_PACKAGE'));
		}

		// We only allow language packs (but we can check only if its a package)
		if (!isset($package['type']) || ($package['type'] !== 'package' && $package['type'] !== 'language'))
		{
			JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

			throw new Exception(JText::_('COM_LANGUAGES_INSTALL_UNABLE_TO_FIND_INSTALL_PACKAGE'));
		}

		return $package;
	}
}
