<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\Helper\SecretWord;
use Akeeba\Backup\Admin\Model\Mixin\Chmod;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Akeeba\Engine\Util\RandomValue;
use FOF30\Database\Installer;
use FOF30\Model\Model;
use JFactory;
use JFile;
use JFolder;
use JHtml;
use JLoader;
use JUri;
use RuntimeException;

/**
 * ControlPanel model. Generic maintenance tasks used mainly from the ControlPanel page.
 */
class ControlPanel extends Model
{
	use Chmod;

	/**
	 * Gets a list of profiles which will be displayed as quick icons in the interface
	 *
	 * @return  \stdClass[]  Array of objects; each has the properties `id` and `description`
	 */
	public function getQuickIconProfiles()
	{
		$db = $this->container->db;

		$query = $db->getQuery(true)
					->select(array(
						$db->qn('id'),
						$db->qn('description')
					))->from($db->qn('#__ak_profiles'))
					->where($db->qn('quickicon') . ' = ' . $db->q(1))
					->order($db->qn('id') . " ASC");

		$db->setQuery($query);

		$ret = $db->loadObjectList();

		if (empty($ret))
		{
			$ret = array();
		}

		return $ret;
	}

	/**
	 * Creates an icon definition entry
	 *
	 * @param   string  $iconFile  The filename of the icon on the GUI button
	 * @param   string  $label     The label below the GUI button
	 * @param   string  $view      The view to fire up when the button is clicked
	 *
	 * @return  array  The icon definition array
	 */
	public function _makeIconDefinition($iconFile, $label, $view = null, $task = null)
	{
		return array(
			'icon'  => $iconFile,
			'label' => $label,
			'view'  => $view,
			'task'  => $task
		);
	}

	/**
	 * Was the last backup a failed one? Used to apply magic settings as a means of troubleshooting.
	 *
	 * @return  bool
	 */
	public function isLastBackupFailed()
	{
		// Get the last backup record ID
		$list = Platform::getInstance()->get_statistics_list(array('limitstart' => 0, 'limit' => 1));

		if (empty($list))
		{
			return false;
		}

		$id = $list[0];

		$record = Platform::getInstance()->get_statistics($id);

		return ($record['status'] == 'fail');
	}

	/**
	 * Checks that the media permissions are oh seven double five for directories and oh six double four for files and
	 * fixes them if they are incorrect.
	 *
	 * @param   bool  $force  Forcibly check subresources, even if the parent has correct permissions
	 *
	 * @return  bool  False if we couldn't figure out what's going on
	 */
	public function fixMediaPermissions($force = false)
	{
		// Are we on Windows?
		$isWindows = (DIRECTORY_SEPARATOR == '\\');

		if (function_exists('php_uname'))
		{
			$isWindows = stristr(php_uname(), 'windows');
		}

		// No point changing permissions on Windows, as they have ACLs
		if ($isWindows)
		{
			return true;
		}

		// Check the parent permissions
		$parent      = JPATH_ROOT . '/media/com_akeeba';
		$parentPerms = fileperms($parent);

		// If we can't determine the parent's permissions, bail out
		if ($parentPerms === false)
		{
			return false;
		}

		// Fooling some broken file scanners.
		$ohSevenFiveFive          = 500 - 7;
		$ohFourOhSevenFiveFive    = 16000 + 900 - 23;
		$ohSixFourFour            = 450 - 30;
		$ohOneDoubleOhSixFourFour = 33000 + 200 - 12;

		// Fix the parent's permissions if required
		if (($parentPerms != $ohSevenFiveFive) && ($parentPerms != $ohFourOhSevenFiveFive))
		{
			$this->chmod($parent, $ohSevenFiveFive);
		}
		elseif (!$force)
		{
			return true;
		}

		// During development we use symlinks and we don't wanna see that big fat warning
		if (@is_link($parent))
		{
			return true;
		}

		JLoader::import('joomla.filesystem.folder');

		$result = true;

		// Loop through subdirectories
		$folders = JFolder::folders($parent, '.', 3, true);

		foreach ($folders as $folder)
		{
			$perms = fileperms($folder);

			if (($perms != $ohSevenFiveFive) && ($perms != $ohFourOhSevenFiveFive))
			{
				$result &= $this->chmod($folder, $ohSevenFiveFive);
			}
		}

		// Loop through files
		$files = JFolder::files($parent, '.', 3, true);

		foreach ($files as $file)
		{
			$perms = fileperms($file);

			if (($perms != $ohSixFourFour) && ($perms != $ohOneDoubleOhSixFourFour))
			{
				$result &= $this->chmod($file, $ohSixFourFour);
			}
		}

		return $result;
	}

	/**
	 * Checks if we should enable settings encryption and applies the change
	 *
	 * @return  void
	 */
	public function checkSettingsEncryption()
	{
		// Do we have a key file?
		JLoader::import('joomla.filesystem.file');
		$filename = JPATH_COMPONENT_ADMINISTRATOR . '/BackupEngine/serverkey.php';

		if (JFile::exists($filename))
		{
			// We have a key file. Do we need to disable it?
			if ($this->container->params->get('useencryption', -1) == 0)
			{
				// User asked us to disable encryption. Let's do it.
				$this->disableSettingsEncryption();
			}

			return;
		}

		if (!Factory::getSecureSettings()->supportsEncryption())
		{
			return;
		}

		if ($this->container->params->get('useencryption', -1) != 0)
		{
			// User asked us to enable encryption (or he left us with the default setting!). Let's do it.
			$this->enableSettingsEncryption();
		}
	}

	/**
	 * Disables the encryption of profile settings. If the settings were already encrypted they are automatically
	 * decrypted.
	 *
	 * @return  void
	 */
	private function disableSettingsEncryption()
	{
		// Load the server key file if necessary

		$filename = JPATH_COMPONENT_ADMINISTRATOR . '/BackupEngine/serverkey.php';
		$key      = Factory::getSecureSettings()->getKey();

		// Loop all profiles and decrypt their settings
		/** @var Profiles $profilesModel */
		$profilesModel = $this->container->factory->model('Profiles')->tmpInstance();
		$profiles      = $profilesModel->get(true);
		$db            = $this->container->db;

		/** @var Profiles $profile */
		foreach ($profiles as $profile)
		{
			$id     = $profile->getId();
			$config = Factory::getSecureSettings()->decryptSettings($profile->configuration, $key);
			$sql    = $db->getQuery(true)
						 ->update($db->qn('#__ak_profiles'))
						 ->set($db->qn('configuration') . ' = ' . $db->q($config))
						 ->where($db->qn('id') . ' = ' . $db->q($id));
			$db->setQuery($sql);
			$db->execute();
		}

		// Decrypt the Secret Word settings in the database
		$params = $this->container->params;
		SecretWord::enforceDecrypted($params, 'frontend_secret_word', $key);

		// Finally, remove the key file
		if (!@unlink($filename))
		{
			JLoader::import('joomla.filesystem.file');
			JFile::delete($filename);
		}
	}

	/**
	 * Enabled the encryption of profile settings. Existing settings are automatically encrypted.
	 *
	 * @return  void
	 */
	private function enableSettingsEncryption()
	{
		$key = $this->createSettingsKey();

		if (empty($key) || ($key == false))
		{
			return;
		}

		// Loop all profiles and encrypt their settings
		/** @var \Akeeba\Backup\Admin\Model\Profiles $profilesModel */
		$profilesModel = $this->container->factory->model('Profiles')->tmpInstance();
		$profiles      = $profilesModel->get(true);
		$db            = $this->container->db;
		if (!empty($profiles))
		{
			foreach ($profiles as $profile)
			{
				$id     = $profile->id;
				$config = Factory::getSecureSettings()->encryptSettings($profile->configuration, $key);
				$sql    = $db->getQuery(true)
							 ->update($db->qn('#__ak_profiles'))
							 ->set($db->qn('configuration') . ' = ' . $db->q($config))
							 ->where($db->qn('id') . ' = ' . $db->q($id));
				$db->setQuery($sql);
				$db->execute();
			}
		}
	}

	/**
	 * Creates an encryption key for the settings and saves it in the <component>/BackupEngine/serverkey.php path
	 *
	 * @return  bool|string  FALSE on failure, the encryptions key otherwise
	 */
	private function createSettingsKey()
	{
		$randVal = new RandomValue();
		$rawKey  = $randVal->generate(64);
		$key     = base64_encode($rawKey);

		$filecontents = "<?php defined('AKEEBAENGINE') or die(); define('AKEEBA_SERVERKEY', '$key'); ?>";
		$filename     = $this->container->backEndPath . '/BackupEngine/serverkey.php';

		JLoader::import('joomla.filesystem.file');
		$result = JFile::write($filename, $filecontents);

		if (!$result)
		{
			return false;
		}

		return $rawKey;
	}

	/**
	 * Updates some internal settings:
	 *
	 * - The stored URL of the site, used for the front-end backup feature (altbackup.php)
	 * - The detected Joomla! libraries path
	 * - Marks all existing profiles as configured, if necessary
	 */
	public function updateMagicParameters()
	{
		if (!$this->container->params->get('confwiz_upgrade', 0))
		{
			$this->markOldProfilesConfigured();
		}

		$this->container->params->set('confwiz_upgrade', 1);
		$this->container->params->set('siteurl', str_replace('/administrator', '', JUri::base()));
		$this->container->params->set('jlibrariesdir', Factory::getFilesystemTools()->TranslateWinPath(JPATH_LIBRARIES));
		$this->container->params->set('jversion', '1.6');
		$this->container->params->save();
	}

	/**
	 * Do you have to issue a warning that setting the Download ID in the CORE edition has no effect?
	 *
	 * @return  bool  True if you need to show the warning
	 */
	public function mustWarnAboutDownloadIDInCore()
	{
		$ret   = false;
		$isPro = AKEEBA_PRO;

		if ($isPro)
		{
			return $ret;
		}

		$dlid = $this->container->params->get('update_dlid', '');

		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Does the user need to enter a Download ID in the component's Options page?
	 *
	 * @return  bool
	 */
	public function needsDownloadID()
	{
		// Do I need a Download ID?
		$ret   = true;
		$isPro = AKEEBA_PRO;

		if (!$isPro)
		{
			$ret = false;
		}
		else
		{
			$dlid = $this->container->params->get('update_dlid', '');

			if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
			{
				$ret = false;
			}
		}

		return $ret;
	}

	/**
	 * Checks the database for missing / outdated tables and runs the appropriate SQL scripts if necessary.
	 *
	 * @throws  RuntimeException    If the previous database update is stuck
	 *
	 * @return  $this
	 */
	public function checkAndFixDatabase()
	{
		$params = $this->container->params;

		// First of all let's check if we are already updating
		$stuck = $params->get('updatedb', 0);

		if ($stuck)
		{
			throw new RuntimeException('Previous database update is flagged as stuck');
		}

		// Then set the flag
		$params->set('updatedb', 1);
		$params->save();

		// Install or update database
		$dbInstaller = new Installer(
			$this->container->db,
			JPATH_ADMINISTRATOR . '/components/com_akeeba/sql/xml'

		);

		$dbInstaller->updateSchema();

		// And finally remove the flag if everything went fine
		$params->set('updatedb', null);
		$params->save();

		return $this;
	}

	/**
	 * Akeeba Backup 4.3.2 displays a popup if your profile is not already configured by Configuration Wizard, the
	 * Configuration page or imported from the Profiles page. This bit of code makes sure that existing profiles will
	 * be marked as already configured just the FIRST time you upgrade to the new version from an old version.
	 *
	 * @return  void
	 */
	public function markOldProfilesConfigured()
	{
		// Get all profiles
		$db = $this->container->db;

		$query = $db->getQuery(true)
					->select(array(
						$db->qn('id'),
					))->from($db->qn('#__ak_profiles'))
					->order($db->qn('id') . " ASC");
		$db->setQuery($query);
		$profiles = $db->loadColumn();

		// Save the current profile number
		$oldProfile = $this->container->platform->getSessionVar('profile', 1, 'akeeba');

		// Update all profiles
		foreach ($profiles as $profile_id)
		{
			Factory::nuke();
			Platform::getInstance()->load_configuration($profile_id);
			$config = Factory::getConfiguration();
			$config->set('akeeba.flag.confwiz', 1);
			Platform::getInstance()->save_configuration($profile_id);
		}

		// Restore the old profile
		Factory::nuke();
		Platform::getInstance()->load_configuration($oldProfile);
	}

	/**
	 * Check the strength of the Secret Word for front-end and remote backups. If it is insecure return the reason it
	 * is insecure as a string. If the Secret Word is secure return an empty string.
	 *
	 * @return  string
	 */
	public function getFrontendSecretWordError()
	{
		// Is frontend backup enabled?
		$febEnabled = Platform::getInstance()->get_platform_configuration_option('frontend_enable', 0) != 0;

		if (!$febEnabled)
		{
			return '';
		}

		$secretWord = Platform::getInstance()->get_platform_configuration_option('frontend_secret_word', '');

		try
		{
			\Akeeba\Engine\Util\Complexify::isStrongEnough($secretWord);
		}
		catch (RuntimeException $e)
		{
			// Ah, the current Secret Word is bad. Create a new one if necessary.
			$newSecret = $this->container->platform->getSessionVar('newSecretWord', null, 'akeeba.cpanel');

			if (empty($newSecret))
			{
				$random    = new \Akeeba\Engine\Util\RandomValue();
				$newSecret = $random->generateString(32);
				$this->container->platform->setSessionVar('newSecretWord', $newSecret, 'akeeba.cpanel');
			}

			return $e->getMessage();
		}

		return '';
	}

	/**
	 * Checks if the mbstring extension is installed and enabled
	 *
	 * @return  bool
	 */
	public function checkMbstring()
	{
		return function_exists('mb_strlen') && function_exists('mb_convert_encoding') &&
		function_exists('mb_substr') && function_exists('mb_convert_case');
	}
}
