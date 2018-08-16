<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Load FOF if not already loaded
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('This component requires FOF 3.0.');
}

class Com_AkeebaInstallerScript extends \FOF30\Utils\InstallScript
{
	/**
	 * The component's name
	 *
	 * @var   string
	 */
	protected $componentName = 'com_akeeba';

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var string
	 */
	protected $componentTitle = 'Akeeba Backup';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '5.4.0';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.3.0';

	/**
	 * Obsolete files and folders to remove from the free version only. This is used when you move a feature from the
	 * free version of your extension to its paid version. If you don't have such a distinction you can ignore this.
	 *
	 * @var   array
	 */
	protected $removeFilesFree = array(
		'files'   => array(
			// Pro component features
			'administrator/components/com_akeeba/BackupEngine/Archiver/Directftp.php',
			'administrator/components/com_akeeba/BackupEngine/Archiver/directftp.ini',
			'administrator/components/com_akeeba/BackupEngine/Archiver/Directftpcurl.php',
			'administrator/components/com_akeeba/BackupEngine/Archiver/directftpcurl.ini',
			'administrator/components/com_akeeba/BackupEngine/Archiver/Directsftp.php',
			'administrator/components/com_akeeba/BackupEngine/Archiver/directsftp.ini',
			'administrator/components/com_akeeba/BackupEngine/Archiver/Directsftpcurl.php',
			'administrator/components/com_akeeba/BackupEngine/Archiver/directsftpcurl.ini',
			'administrator/components/com_akeeba/BackupEngine/Archiver/Jps.php',
			'administrator/components/com_akeeba/BackupEngine/Archiver/jps.ini',
			'administrator/components/com_akeeba/BackupEngine/Archiver/Zipnative.php',
			'administrator/components/com_akeeba/BackupEngine/Archiver/zipnative.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/amazons3.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Amazons3.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/azure.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Azure.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/backblaze.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Backblaze.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/cloudfiles.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Cloudfiles.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/cloudme.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Cloudme.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/dreamobjects.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Dreamobjects.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/dropbox.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Dropbox.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/dropbox2.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Dropbox2.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/ftp.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Ftp.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/ftpcurl.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Ftpcurl.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/googledrive.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Googledrive.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/googlestorage.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Googlestorage.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/googlestoragejson.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Googlestoragejson.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/idrivesync.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Idrivesync.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/onedrive.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Onedrive.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/onedrivebusiness.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Onedrivebusiness.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/s3.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/S3.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/sftp.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Sftp.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/sftpcurl.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Sftpcurl.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/sugarsync.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Sugarsync.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/webdav.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Webdav.php',
			'administrator/components/com_akeeba/BackupEngine/Scan/large.ini',
			'administrator/components/com_akeeba/BackupEngine/Scan/Large.php',
			
			'administrator/components/com_akeeba/Controller/Alice.php',
			'administrator/components/com_akeeba/Controller/Discover.php',
			'administrator/components/com_akeeba/Controller/IncludeFolders.php',
			'administrator/components/com_akeeba/Controller/MultipleDatabases.php',
			'administrator/components/com_akeeba/Controller/RegExDatabaseFilters.php',
			'administrator/components/com_akeeba/Controller/RegExFileFilters.php',
			'administrator/components/com_akeeba/Controller/RemoteFiles.php',
			'administrator/components/com_akeeba/Controller/S3Import.php',
			'administrator/components/com_akeeba/Controller/Upload.php',

			'administrator/components/com_akeeba/Model/Alice.php',
			'administrator/components/com_akeeba/Model/Discover.php',
			'administrator/components/com_akeeba/Model/IncludeFolders.php',
			'administrator/components/com_akeeba/Model/MultipleDatabases.php',
			'administrator/components/com_akeeba/Model/RegExDatabaseFilters.php',
			'administrator/components/com_akeeba/Model/RegExFileFilters.php',
			'administrator/components/com_akeeba/Model/RemoteFiles.php',
			'administrator/components/com_akeeba/Model/S3Import.php',
			'administrator/components/com_akeeba/Model/Upload.php',
			
			'administrator/components/com_akeeba/BackupPlatform/Joomla3x/Filter/Components.php',
			'administrator/components/com_akeeba/BackupPlatform/Joomla3x/Filter/Extensiondirs.php',
			'administrator/components/com_akeeba/BackupPlatform/Joomla3x/Filter/Extensionfiles.php',
			'administrator/components/com_akeeba/BackupPlatform/Joomla3x/Filter/Languages.php',
			'administrator/components/com_akeeba/BackupPlatform/Joomla3x/Filter/Modules.php',
			'administrator/components/com_akeeba/BackupPlatform/Joomla3x/Filter/Plugins.php',
			'administrator/components/com_akeeba/BackupPlatform/Joomla3x/Filter/Templates.php',

			// Additional ANGIE installers which are not used in Core
			'administrator/components/com_akeeba/Master/Installers/abi.ini',
			'administrator/components/com_akeeba/Master/Installers/abi.jpa',
			'administrator/components/com_akeeba/Master/Installers/angie-generic.ini',
			'administrator/components/com_akeeba/Master/Installers/angie-generic.jpa',
		),
		'folders' => array(
			// Pro component features
			'administrator/components/com_akeeba/Alice',
			'administrator/components/com_akeeba/BackupPlatform/Joomla3x/Config/Pro',
			'administrator/components/com_akeeba/View/Alice',
			'administrator/components/com_akeeba/View/Discover',
			'administrator/components/com_akeeba/View/IncludeFolders',
			'administrator/components/com_akeeba/View/MultipleDatabases',
			'administrator/components/com_akeeba/View/RegExDatabaseFilters',
			'administrator/components/com_akeeba/View/RegExFileFilter',
			'administrator/components/com_akeeba/View/RemoteFiles',
			'administrator/components/com_akeeba/View/S3Import',
			'administrator/components/com_akeeba/View/Upload',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Connector',
		)
	);

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = array(
		'files'   => array(
            // Outdated CLI scripts
            'cli/akeeba-update.php',

			// Outdated media files
			'media/com_akeeba/icons/akeeba-48.png',
			'media/com_akeeba/icons/akeeba-warning-48.png',
			'media/com_akeeba/icons/arrow_small.png',
			'media/com_akeeba/icons/error_small.png',
			'media/com_akeeba/icons/ok_small.png',
			'media/com_akeeba/icons/reload.png',
			'media/com_akeeba/icons/scheduling-32.png',
			'media/com_akeeba/icons/update.png',

			'media/com_akeeba/js/akeebajq.js',
			'media/com_akeeba/js/akeebajqui.js',
			'media/com_akeeba/js/akeebaui.js',
			'media/com_akeeba/js/akeebauipro.js',
			'media/com_akeeba/js/alice.js',
			'media/com_akeeba/js/backup.js',
			'media/com_akeeba/js/configuration.js',
			'media/com_akeeba/js/confwiz.js',
			'media/com_akeeba/js/dbef.js',
			'media/com_akeeba/js/eff.js',
			'media/com_akeeba/js/encryption.js',
			'media/com_akeeba/js/fsfilter.js',
			'media/com_akeeba/js/gui-helpers.js',
			'media/com_akeeba/js/jquery.js',
			'media/com_akeeba/js/jquery-ui.js',
			'media/com_akeeba/js/multidb.js',
			'media/com_akeeba/js/regexdbfilter.js',
			'media/com_akeeba/js/regexfsfilter.js',
			'media/com_akeeba/js/restore.js',
			'media/com_akeeba/js/stepper.js',
			'media/com_akeeba/js/system.js',
			'media/com_akeeba/js/transfer.js',

			// Old CLI backup scripts, obsolete since 3.5.0, removed in 4.0.0
			'administrator/components/com_akeeba/backup.php',
			'administrator/components/com_akeeba/altbackup.php',

			// Files used in version 4.2, but before 5.0
			// -- Back-end
			'administrator/components/com_akeeba/dispatcher.php',
			'administrator/components/com_akeeba/toolbar.php',
			'administrator/components/com_akeeba/views/backup/view.html.php',
			'administrator/components/com_akeeba/views/backup/tmpl/default.php',
			'administrator/components/com_akeeba/views/restore/view.html.php',
			'administrator/components/com_akeeba/views/restore/tmpl/default.php',
			'administrator/components/com_akeeba/views/restore/tmpl/restore.php',
			'administrator/components/com_akeeba/views/transfer/view.html.php',
			'administrator/components/com_akeeba/views/transfer/tmpl/default.php',
			'administrator/components/com_akeeba/views/transfer/tmpl/default_dialogs.php',
			'administrator/components/com_akeeba/views/transfer/tmpl/default_manualtransfer.php',
			'administrator/components/com_akeeba/views/transfer/tmpl/default_prerequisites.php',
			'administrator/components/com_akeeba/views/transfer/tmpl/default_remoteconnection.php',
			'administrator/components/com_akeeba/views/transfer/tmpl/default_upload.php',

			// -- Front-end
			'components/com_akeeba/dispatcher.php',

			// Integrity check (obsolete)
			'administrator/components/com_akeeba/fileslist.php',

			// Blade files (replaced by regular PHP view files)
			"administrator/components/com_akeeba/View/Alice/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Backup/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Backup/tmpl/script.blade.php",
			"administrator/components/com_akeeba/View/Browser/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/CommonTemplates/tmpl/ErrorModal.blade.php",
			"administrator/components/com_akeeba/View/CommonTemplates/tmpl/FolderBrowser.blade.php",
			"administrator/components/com_akeeba/View/CommonTemplates/tmpl/FTPBrowser.blade.php",
			"administrator/components/com_akeeba/View/CommonTemplates/tmpl/FTPConnectionTest.blade.php",
			"administrator/components/com_akeeba/View/CommonTemplates/tmpl/ProfileName.blade.php",
			"administrator/components/com_akeeba/View/CommonTemplates/tmpl/SFTPBrowser.blade.php",
			"administrator/components/com_akeeba/View/Configuration/tmpl/confwiz_modal.blade.php",
			"administrator/components/com_akeeba/View/Configuration/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/ConfigurationWizard/tmpl/wizard.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/footer.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/icons_advanced.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/icons_basic.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/icons_includeexclude.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/icons_troubleshooting.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/oneclick.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/profile.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/sidebar_backup.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/sidebar_status.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/warning_phpversion.blade.php",
			"administrator/components/com_akeeba/View/ControlPanel/tmpl/warnings.blade.php",
			"administrator/components/com_akeeba/View/DatabaseFilters/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/DatabaseFilters/tmpl/tabular.blade.php",
			"administrator/components/com_akeeba/View/Discover/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Discover/tmpl/discover.blade.php",
			"administrator/components/com_akeeba/View/FileFilters/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/FileFilters/tmpl/tabular.blade.php",
			"administrator/components/com_akeeba/View/IncludeFolders/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Log/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Manage/tmpl/comment.blade.php",
			"administrator/components/com_akeeba/View/Manage/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Manage/tmpl/howtorestore_modal.blade.php",
			"administrator/components/com_akeeba/View/Manage/tmpl/manage_column.blade.php",
			"administrator/components/com_akeeba/View/MultipleDatabases/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Profiles/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Profiles/tmpl/form.blade.php",
			"administrator/components/com_akeeba/View/RegExDatabaseFilters/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/RegExFileFilter/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/RemoteFiles/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/RemoteFiles/tmpl/dlprogress.blade.php",
			"administrator/components/com_akeeba/View/Restore/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Restore/tmpl/restore.blade.php",
			"administrator/components/com_akeeba/View/S3Import/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/S3Import/tmpl/downloading.blade.php",
			"administrator/components/com_akeeba/View/Schedule/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Schedule/tmpl/default_checkbackups.blade.php",
			"administrator/components/com_akeeba/View/Schedule/tmpl/default_runbackups.blade.php",
			"administrator/components/com_akeeba/View/Transfer/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Transfer/tmpl/default_manualtransfer.blade.php",
			"administrator/components/com_akeeba/View/Transfer/tmpl/default_prerequisites.blade.php",
			"administrator/components/com_akeeba/View/Transfer/tmpl/default_remoteconnection.blade.php",
			"administrator/components/com_akeeba/View/Transfer/tmpl/default_upload.blade.php",
			"administrator/components/com_akeeba/View/Upload/tmpl/default.blade.php",
			"administrator/components/com_akeeba/View/Upload/tmpl/done.blade.php",
			"administrator/components/com_akeeba/View/Upload/tmpl/error.blade.php",
			"administrator/components/com_akeeba/View/Upload/tmpl/uploading.blade.php",

			// Dropbox v1 integration
			'administrator/components/com_akeeba/BackupEngine/Postproc/dropbox.ini',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Dropbox.php',
			'administrator/components/com_akeeba/BackupEngine/Postproc/Connector/Dropbox.php',

			// Obsolete Azure files
			'administrator/components/com_akeeba/BackupEngine/Postproc/Connector/Azure/Credentials/Sharedsignature.php',

            // Obsolete AES-128 CTR implementation in Javascript
			'media/com_akeeba/js/Encryption.min.js',
			'media/com_akeeba/js/Encryption.min.map',

            // PHP 7.2 compatibility
            'administrator/components/com_akeeba/BackupEngine/Base/Object.php',

			// Obsolete media files
            'media/com_akeeba/icons/akeeba-ui-32.png',
            'media/com_akeeba/changelog.png',
		),
		'folders' => array(
			// Directories used up to version 4.1 (inclusive)
			// -- Back-end
			'administrator/components/com_akeeba/akeeba',
			'administrator/components/com_akeeba/plugins',
			// -- Front-end
			'components/com_akeeba/views',

			// Directories used in version 4.2, but before 5.0
			// -- Back-end
			'administrator/components/com_akeeba/alice',
			'administrator/components/com_akeeba/assets',
			'administrator/components/com_akeeba/controllers',
			'administrator/components/com_akeeba/engine',
			'administrator/components/com_akeeba/helpers',
			'administrator/components/com_akeeba/models',
			'administrator/components/com_akeeba/platform',
			'administrator/components/com_akeeba/tables',
            'administrator/components/com_akeeba/views/alices',
			'administrator/components/com_akeeba/views/browser',
			'administrator/components/com_akeeba/views/buadmin',
			'administrator/components/com_akeeba/views/config',
			'administrator/components/com_akeeba/views/confwiz',
			'administrator/components/com_akeeba/views/cpanel',
			'administrator/components/com_akeeba/views/dbef',
			'administrator/components/com_akeeba/views/discover',
			'administrator/components/com_akeeba/views/eff',
			'administrator/components/com_akeeba/views/fsfilter',
			'administrator/components/com_akeeba/views/log',
			'administrator/components/com_akeeba/views/multidb',
			'administrator/components/com_akeeba/views/profiles',
			'administrator/components/com_akeeba/views/regexdbfilter',
			'administrator/components/com_akeeba/views/regexfsfilter',
			'administrator/components/com_akeeba/views/remotefiles',
			'administrator/components/com_akeeba/views/s3import',
			'administrator/components/com_akeeba/views/schedule',
			'administrator/components/com_akeeba/views/updates',
			'administrator/components/com_akeeba/views/upload',
			// -- Front-end
			'components/com_akeeba/controllers',
			'components/com_akeeba/models',

			// Outdated media directories
			'media/com_akeeba/theme',

			// Obsolete Plugins
			'plugins/system/aklazy',
			'plugins/system/srp',

			// Obsolete Modules
			'administrator/modules/mod_akadmin',

			// Dropbox v1 integration
			'administrator/components/com_akeeba/BackupEngine/Postproc/Connector/Dropbox',
		)
	);

	/**
	 * Runs on installation
	 *
	 * @param   JInstallerAdapterComponent $parent The parent object
	 *
	 * @return  void
	 */
	public function install($parent)
	{
		if (!defined('AKEEBA_THIS_IS_INSTALLATION_FROM_SCRATCH'))
		{
			define('AKEEBA_THIS_IS_INSTALLATION_FROM_SCRATCH', 1);
		}
	}

	/**
	 * Joomla! pre-flight event. This runs before Joomla! installs or updates the component. This is our last chance to
	 * tell Joomla! if it should abort the installation.
	 *
	 * @param   string                     $type   Installation type (install, update, discover_install)
	 * @param   JInstallerAdapterComponent $parent Parent object
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function preflight($type, $parent)
	{
		$this->isPaid = is_dir($parent->getParent()->getPath('source') . '/backend/AliceEngine');

		$result = parent::preflight($type, $parent);

		if (!$result)
		{
			return $result;
		}

		// Move the server key file from /akeeba or /engine to /BackupEngine
		$componentPath = JPATH_ADMINISTRATOR . '/components/com_akeeba';
		$fromFile      = $componentPath . '/akeeba/serverkey.php';
		$toFile        = $componentPath . '/BackupEngine/serverkey.php';

		if (!file_exists($fromFile))
		{
			$fromFile = $componentPath . '/engine/serverkey.php';
		}

		if (@file_exists($fromFile) && !@file_exists($toFile))
		{
			$toPath = $componentPath . '/BackupEngine';

			if (class_exists('JLoader') && method_exists('JLoader', 'import'))
			{
				JLoader::import('joomla.filesystem.folder');
				JLoader::import('joomla.filesystem.file');
			}

			if (@is_dir($componentPath) && !@is_dir($toPath))
			{
				JFolder::create($toPath);
			}

			if (@is_dir($toPath))
			{
				JFile::copy($fromFile, $toFile);
			}
		}

		return $result;
	}

	/**
	 * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
	 * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
	 * database updates and similar housekeeping functions.
	 *
	 * @param   string                     $type   install, update or discover_update
	 * @param   JInstallerAdapterComponent $parent Parent object
	 */
	function postflight($type, $parent)
	{
		// Let's install common tables
		$container = null;
		$model     = null;

		if (class_exists('FOF30\\Container\\Container'))
		{
			try
			{
				$container = \FOF30\Container\Container::getInstance('com_akeeba');
			}
			catch (\Exception $e)
			{
				$container = null;
			}
		}

		if (is_object($container) && class_exists('FOF30\\Container\\Container') && ($container instanceof \FOF30\Container\Container))
		{
			/** @var \Akeeba\Backup\Admin\Model\UsageStatistics $model */
			try
			{
				$model = $container->factory->model('UsageStatistics')->tmpInstance();
			}
			catch (\Exception $e)
			{
				$model = null;
			}
		}

		if (is_object($model) && class_exists('Akeeba\\Backup\\Admin\\Model\\UsageStatistics')
			&& ($model instanceof Akeeba\Backup\Admin\Model\UsageStatistics)
			&& method_exists($model, 'checkAndFixCommonTables'))
		{
			try
			{
				$model->checkAndFixCommonTables();
			}
			catch (Exception $e)
			{
				// Do nothing if that failed.
			}
		}

		// Parent method
		parent::postflight($type, $parent);

		// Add ourselves to the list of extensions depending on Akeeba FEF
		$this->addDependency('file_fef', $this->componentName);

		// Uninstall post-installation messages we are no longer using
		$this->uninstallObsoletePostinstallMessages();

		// Remove the update sites for this component on installation. The update sites are now handled at the package
		// level.
		$this->removeObsoleteUpdateSites($parent);

		// Remove the FOF 2.x update sites (annoying leftovers)
		$this->removeFOFUpdateSites();

		// If this is a new installation tell it to NOT mark the backup profiles as configured.
		if (defined('AKEEBA_THIS_IS_INSTALLATION_FROM_SCRATCH'))
		{
			$this->markProfilesAsNotConfiguredYet();
		}

		// This is an update of an existing installation
		if (!defined('AKEEBA_THIS_IS_INSTALLATION_FROM_SCRATCH'))
		{
			// Migrate profiles if necessary
			$this->migrateProfiles();
		}
	}

	/**
	 * Override this method to display a custom component installation message if you so wish
	 *
	 * @param  \JInstallerAdapterComponent  $parent  Parent class calling us
	 */
	protected function renderPostInstallation($parent)
	{
		try
		{
			$this->warnAboutJSNPowerAdmin();
		}
		catch (Exception $e)
		{
			// Don't sweat if the site's db croaks while I'm checking for 3PD software that causes trouble
		}

		// Load the version file
		if (!defined('AKEEBA_PRO'))
		{
			@include_once JPATH_ADMINISTRATOR . '/components/com_akeeba/version.php';
		}

		if (!defined('AKEEBA_PRO'))
		{
			define('AKEEBA_PRO', '0');
		}

		$videoTutorialURL = 'https://www.akeebabackup.com/videos/1212-akeeba-backup-core.html';

		if (AKEEBA_PRO)
		{
			$videoTutorialURL = 'https://www.akeebabackup.com/videos/1213-akeeba-backup-for-joomla-pro.html';
		}

		?>
		<img src="../media/com_akeeba/icons/logo-48.png" width="48" height="48" alt="Akeeba Backup" align="right"/>

		<h2>Welcome to Akeeba Backup!</h2>

		<div style="margin: 1em; font-size: 14pt; background-color: #fffff9; color: black">
			You can download translation files <a href="http://cdn.akeebabackup.com/language/akeebabackup/index.html">directly
				from our CDN page</a>.
		</div>

		<fieldset>
			<p>
				We strongly recommend watching our
				<a href="<?php echo $videoTutorialURL ?>">video
				tutorials</a> before using this component.
			</p>

			<p>
				If this is the first time you install Akeeba Backup on your site please run the
				<a href="index.php?option=com_akeeba&view=ConfigurationWizard">Configuration Wizard</a>. Akeeba Backup will
				configure itself optimally for your site.
			</p>

			<p>
				By installing this component you are implicitly accepting
				<a href="https://www.akeebabackup.com/license.html">its license (GNU GPLv3)</a> and our
				<a href="https://www.akeebabackup.com/privacy-policy.html">Terms of Service</a>,
				including our Support Policy.
			</p>
		</fieldset>
	<?php
		// Let's install common tables
		$container = null;
		$model     = null;

		if (class_exists('FOF30\\Container\\Container'))
		{
			try
			{
				$container = \FOF30\Container\Container::getInstance('com_akeeba');
			}
			catch (\Exception $e)
			{
				$container = null;
			}
		}

		if (is_object($container) && class_exists('FOF30\\Container\\Container') && ($container instanceof \FOF30\Container\Container))
		{
			/** @var \Akeeba\Backup\Admin\Model\UsageStatistics $model */
			try
			{
				$model = $container->factory->model('UsageStatistics')->tmpInstance();
			}
			catch (\Exception $e)
			{
				$model = null;
			}
		}

		/** @var \Akeeba\Backup\Admin\Model\UsageStatistics $model */
		try
		{
			if (is_object($model) && class_exists('Akeeba\\Backup\\Admin\\Model\\UsageStatistics')
				&& ($model instanceof Akeeba\Backup\Admin\Model\UsageStatistics)
				&& method_exists($model, 'collectStatistics'))
			{
				$iframe = $model->collectStatistics(true);

				if ($iframe)
				{
					echo $iframe;
				}
			}
		}
		catch (\Exception $e)
		{
		}
	}

	/**
	 * Override this method to display a custom component uninstallation message if you so wish
	 *
	 * @param  \JInstallerAdapterComponent  $parent  Parent class calling us
	 */
	protected function renderPostUninstallation($parent)
	{
		?>
		<h2>Akeeba Backup Uninstallation Status</h2>
		<p>We are sorry that you decided to uninstall Akeeba Backup. Please let us know why by using the <a
			href="https://www.akeebabackup.com/contact-us.html" target="_blank">Contact Us form on our site</a>. We
			appreciate your feedback; it helps us develop better software!</p>
		<?php
	}

	private function uninstallObsoletePostinstallMessages()
	{
		$db = JFactory::getDbo();

		$obsoleteTitleKeys = array(
			// Remove "Upgrade profiles to ANGIE"
			'AKEEBA_POSTSETUP_LBL_ANGIEUPGRADE',
			// Remove "Enable System Restore Points"
			'AKEEBA_POSTSETUP_LBL_SRP',
			'AKEEBA_POSTSETUP_LBL_BACKUPONUPDATE',
			'AKEEBA_POSTSETUP_LBL_CONFWIZ',
			'AKEEBA_POSTSETUP_LBL_ACCEPTLICENSE',
			'AKEEBA_POSTSETUP_LBL_ACCEPTSUPPORT',
			'AKEEBA_POSTSETUP_LBL_ACCEPTBACKUPTEST',
		);

		foreach ($obsoleteTitleKeys as $obsoleteKey)
		{

			// Remove the "Upgrade profiles to ANGIE" post-installation message
			$query = $db->getQuery(true)
						->delete($db->qn('#__postinstall_messages'))
						->where($db->qn('title_key') . ' = ' . $db->q($obsoleteKey));
			try
			{
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				// Do nothing
			}
		}
	}

	/**
	 * The PowerAdmin extension makes menu items disappear. People assume it's our fault. JSN PowerAdmin authors don't
	 * own up to their software's issue. I have no choice but to warn our users about the faulty third party software.
	 */
	private function warnAboutJSNPowerAdmin()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q('com_poweradmin'))
			->where($db->qn('enabled') . ' = ' . $db->q('1'));
		$hasPowerAdmin = $db->setQuery($query)->loadResult();

		if (!$hasPowerAdmin)
		{
			return;
		}

		$query = $db->getQuery(true)
					->select('manifest_cache')
					->from($db->qn('#__extensions'))
					->where($db->qn('type') . ' = ' . $db->q('component'))
					->where($db->qn('element') . ' = ' . $db->q('com_poweradmin'))
					->where($db->qn('enabled') . ' = ' . $db->q('1'));
		$paramsJson = $db->setQuery($query)->loadResult();

		$className = class_exists('JRegistry') ? 'JRegistry' : '\Joomla\Registry\Registry';

		/** @var \Joomla\Registry\Registry $jsnPAManifest */
		$jsnPAManifest = new $className();
		$jsnPAManifest->loadString($paramsJson, 'JSON');
		$version = $jsnPAManifest->get('version', '0.0.0');

		if (version_compare($version, '2.1.2', 'ge'))
		{
			return;
		}

		echo <<< HTML
<div class="well" style="margin: 2em 0;">
<h1 style="font-size: 32pt; line-height: 120%; color: red; margin-bottom: 1em">WARNING: Menu items for {$this->componentName} might not be displayed on your site.</h1>
<p style="font-size: 18pt; line-height: 150%; margin-bottom: 1.5em">
	We have detected that you are using JSN PowerAdmin on your site. This software ignores Joomla! standards and
	<b>hides</b> the Component menu items to {$this->componentName} in the administrator backend of your site. Unfortunately we
	can't provide support for third party software. Please contact the developers of JSN PowerAdmin for support
	regarding this issue.
</p>
<p style="font-size: 18pt; line-height: 120%; color: green;">
	Tip: You can disable JSN PowerAdmin to see the menu items to Akeeba Backup.
</p>
</div>

HTML;

	}

	/**
	 * Loads the Akeeba Engine if it's not already loaded
	 */
	private function loadAkeebaEngine()
	{
		if (class_exists('\\Akeeba\\Engine\\Platform'))
		{
			return;
		}

		// Load the language files
		$paths	 = array(JPATH_ADMINISTRATOR, JPATH_ROOT);
		$jlang	 = JFactory::getLanguage();
		$jlang->load('com_akeeba', $paths[0], 'en-GB', true);
		$jlang->load('com_akeeba', $paths[1], 'en-GB', true);
		$jlang->load('com_akeeba' . '.override', $paths[0], 'en-GB', true);
		$jlang->load('com_akeeba' . '.override', $paths[1], 'en-GB', true);

		// Load the version file
		@include_once JPATH_ADMINISTRATOR . '/components/com_akeeba/version.php';

		if (!defined('AKEEBA_PRO'))
		{
			define('AKEEBA_PRO', '0');
		}

		// Enable Akeeba Engine
		if (!defined('AKEEBAENGINE'))
		{
			define('AKEEBAENGINE', 1);
		}

		// Load the engine
		$factoryPath = JPATH_ADMINISTRATOR . '/components/com_akeeba/BackupEngine/Factory.php';
		define('AKEEBAROOT', JPATH_ADMINISTRATOR . '/components/com_akeeba/BackupEngine');

		require_once $factoryPath;

		// Assign the correct platform
		\Akeeba\Engine\Platform::addPlatform('joomla3x', JPATH_ADMINISTRATOR . '/components/com_akeeba/BackupPlatform/Joomla3x');
	}

	/**
	 * Migrates existing backup profiles. The changes currently made are:
	 * – Change post-processing from "s3" (legacy) to "amazons3" (current version)
	 * – Fix profiles with invalid embedded installer settings
	 *
	 * @return  void
	 */
	private function migrateProfiles()
	{
		$this->loadAkeebaEngine();

		// Get a list of backup profiles
		$db = JFactory::getDbo();

		try
		{
			$query = $db->getQuery(true)
						->select($db->qn('id'))
						->from($db->qn('#__ak_profiles'));
			$profiles = $db->setQuery($query)->loadColumn();
		}
		catch (Exception $e)
		{
			// Eh, we couldn't load the profiles. Something's broken in the database. It will be fixed when the
			// installation continues but for now we have to just return without doing anything.
			return;
		}

		// Normally this should never happen as we're supposed to have at least profile #1
		if (empty($profiles))
		{
			return;
		}

		// Migrate each profile
		foreach ($profiles as $profile)
		{
			// Initialization
			$dirty = false;

			// Load the profile configuration
			try
			{
				\Akeeba\Engine\Platform::getInstance()->load_configuration($profile);
				$config = \Akeeba\Engine\Factory::getConfiguration();
			}
			catch (Exception $e)
			{
				// Your database is broken :(
				continue;
			}

			// -- Migrate obsolete "s3" engine to "amazons3"
			$postProcType = $config->get('akeeba.advanced.postproc_engine', '');

			if ($postProcType == 's3')
			{
				$config->setKeyProtection('akeeba.advanced.postproc_engine', false);
				$config->setKeyProtection('engine.postproc.amazons3.signature', false);
				$config->setKeyProtection('engine.postproc.amazons3.accesskey', false);
				$config->setKeyProtection('engine.postproc.amazons3.secretkey', false);
				$config->setKeyProtection('engine.postproc.amazons3.usessl', false);
				$config->setKeyProtection('engine.postproc.amazons3.bucket', false);
				$config->setKeyProtection('engine.postproc.amazons3.directory', false);
				$config->setKeyProtection('engine.postproc.amazons3.rrs', false);
				$config->setKeyProtection('engine.postproc.amazons3.customendpoint', false);
				$config->setKeyProtection('engine.postproc.amazons3.legacy', false);

				$config->set('akeeba.advanced.postproc_engine', 'amazons3');
				$config->set('engine.postproc.amazons3.signature', 's3');
				$config->set('engine.postproc.amazons3.accesskey', $config->get('engine.postproc.s3.accesskey'));
				$config->set('engine.postproc.amazons3.secretkey', $config->get('engine.postproc.s3.secretkey'));
				$config->set('engine.postproc.amazons3.usessl', $config->get('engine.postproc.s3.usessl'));
				$config->set('engine.postproc.amazons3.bucket', $config->get('engine.postproc.s3.bucket'));
				$config->set('engine.postproc.amazons3.directory', $config->get('engine.postproc.s3.directory'));
				$config->set('engine.postproc.amazons3.rrs', $config->get('engine.postproc.s3.rrs'));
				$config->set('engine.postproc.amazons3.customendpoint', $config->get('engine.postproc.s3.customendpoint'));
				$config->set('engine.postproc.amazons3.legacy', $config->get('engine.postproc.s3.legacy'));

				$dirty = true;
			}

			// Fix profiles with invalid embedded installer settings
			$embeddedInstaller = $config->get('akeeba.advanced.embedded_installer');

			if (empty($embeddedInstaller) || ($embeddedInstaller == 'angie-joomla') || (
					(substr($embeddedInstaller, 0, 5) != 'angie') && ($embeddedInstaller != 'none')
				))
			{
				$config->setKeyProtection('akeeba.advanced.embedded_installer', false);
				$config->set('akeeba.advanced.embedded_installer', 'angie');
				$dirty = true;
			}

			// Save dirty records
			if ($dirty)
			{
				try
				{
					\Akeeba\Engine\Platform::getInstance()->save_configuration($profile);
				}
				catch (Exception $e)
				{
					// Your database is broken!
					continue;
				}
			}
		}
	}

	/**
	 * Remove FOF 2.x update sites
	 */
	private function removeFOFUpdateSites()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
					->delete($db->qn('#__update_sites'))
					->where($db->qn('location') . ' = ' . $db->q('http://cdn.akeebabackup.com/updates/fof.xml'));
		try
		{
			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			// Do nothing on failure
		}

	}

	private function markProfilesAsNotConfiguredYet()
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select($db->qn('params'))
						->from($db->qn('#__extensions'))
						->where($db->qn('type') . ' = ' . $db->q('component'))
						->where($db->qn('element') . ' = ' . $db->q('com_akeeba'));

			$jsonData = $db->setQuery($query)->loadResult();

			if (class_exists('JRegistry'))
			{
				$reg = new JRegistry($jsonData);
			}
			else
			{
				$reg = new \Joomla\Registry\Registry($jsonData);
			}

			$reg->set('confwiz_upgrade', 1);
			$jsonData = $reg->toString('JSON');

			$query = $db->getQuery()
						->update($db->qn('#__extensions'))
						->set($db->qn('params') . ' = ' . $db->q($jsonData))
						->where($db->qn('type') . ' = ' . $db->q('component'))
						->where($db->qn('element') . ' = ' . $db->q('com_akeeba'));
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			// If that fails it's not the end of the world. The component is still usable, so just swallow any
			// exception.
		}
	}

	/**
	 * Removes obsolete update sites created for the component (we are now using an update site for the package, not the
	 * component).
	 *
	 * @param   JInstallerAdapterComponent  $parent  The parent installer
	 */
	protected function removeObsoleteUpdateSites($parent)
	{
		$db = $parent->getParent()->getDbo();

		$query = $db->getQuery(true)
					->select($db->qn('extension_id'))
					->from($db->qn('#__extensions'))
					->where($db->qn('type') . ' = ' . $db->q('component'))
					->where($db->qn('name') . ' = ' . $db->q($this->componentName));

		try
		{
			$extensionId = $db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			// Your database is broken.
			return;
		}

		if (!$extensionId)
		{
			return;
		}

		$query = $db->getQuery(true)
					->select($db->qn('update_site_id'))
					->from($db->qn('#__update_sites_extensions'))
					->where($db->qn('extension_id') . ' = ' . $db->q($extensionId));

		try
		{
			$ids = $db->setQuery($query)->loadColumn(0);
		}
		catch (Exception $e)
		{
			// Your database is broken.
			return;
		}

		if (!is_array($ids) && empty($ids))
		{
			return;
		}

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true)
						->delete($db->qn('#__update_sites'))
						->where($db->qn('update_site_id') . ' = ' . $db->q($id));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\Exception $e)
			{
				// Do not fail in this case
			}
		}
	}
}
