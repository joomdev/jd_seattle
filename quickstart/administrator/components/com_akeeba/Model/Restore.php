<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\Model\Model;
use JFile;
use JLoader;
use JText;

class Restore extends Model
{
	private $data;

	private $extension;

	private $path;

	public $password;

	public $id;

	/**
	 * Gets the list of IDs from the request data
	 *
	 * @return array
	 */
	protected function getIDsFromRequest()
	{
		// Get the ID or list of IDs from the request or the configuration
		$cid = $this->input->get('cid', array(), 'array');
		$id  = $this->input->getInt('id', 0);

		$ids = array();

		if (is_array($cid) && !empty($cid))
		{
			$ids = $cid;
		}
		elseif (!empty($id))
		{
			$ids = array($id);
		}

		return $ids;
	}

	/**
	 * Generates a pseudo-random password
	 *
	 * @param   int $length The length of the password in characters
	 *
	 * @return  string  The requested password string
	 */
	function makeRandomPassword($length = 32)
	{
		\JLoader::import('joomla.user.helper');

		return \JUserHelper::genRandomPassword($length);
	}

	/**
	 * Validates the data passed to the request.
	 *
	 * @return  mixed  True if all is OK, an error string if something is wrong
	 */
	public function validateRequest()
	{
		// Is this a valid backup entry?
		$ids       = $this->getIDsFromRequest();
		$id        = array_pop($ids);
		$profileID = $this->input->getInt('profileid', 0);

		// No backup IDs in the request and no backup profile (which means I should use its latest backup record) is found.
		if (empty($id) && ($profileID <= 0))
		{
			return JText::_('COM_AKEEBA_RESTORE_ERROR_INVALID_RECORD');
		}

		if (empty($id))
		{
			try
			{
				$id = $this->getLatestBackupForProfile($profileID);
			}
			catch (\RuntimeException $e)
			{
				return $e->getMessage();
			}
		}

		$data = Platform::getInstance()->get_statistics($id);

		if (empty($data))
		{
			return JText::_('COM_AKEEBA_RESTORE_ERROR_INVALID_RECORD');
		}

		if ($data['status'] != 'complete')
		{
			return JText::_('COM_AKEEBA_RESTORE_ERROR_INVALID_RECORD');
		}

		// Load the profile ID (so that we can find out the output directory)
		$profile_id = $data['profile_id'];
		Platform::getInstance()->load_configuration($profile_id);

		$path   = $data['absolute_path'];
		$exists = @file_exists($path);

		if (!$exists)
		{
			// Let's try figuring out an alternative path
			$config = Factory::getConfiguration();
			$path   = $config->get('akeeba.basic.output_directory', '') . '/' . $data['archivename'];
			$exists = @file_exists($path);
		}

		if (!$exists)
		{
			return JText::_('COM_AKEEBA_RESTORE_ERROR_ARCHIVE_MISSING');
		}

		$filename  = basename($path);
		$lastdot   = strrpos($filename, '.');
		$extension = strtoupper(substr($filename, $lastdot + 1));

		if (!in_array($extension, array('JPS', 'JPA', 'ZIP')))
		{
			return JText::_('COM_AKEEBA_RESTORE_ERROR_INVALID_TYPE');
		}

		$this->data      = $data;
		$this->path      = $path;
		$this->extension = $extension;

		return true;
	}

	/**
	 * Finds the latest backup for a given backup profile with an "OK" status (the archive file exists on your server).
	 * If none is found a RuntimeException is thrown.
	 *
	 * This method uses the code from the Transfer model for DRY reasons.
	 *
	 * @param   int  $profileID  The profile in which to locate the latest valid backup
	 *
	 * @return  int
	 *
	 * @throws  \RuntimeException
	 *
	 * @since   5.3.0
	 */
	public function getLatestBackupForProfile($profileID)
	{
		/** @var Transfer $transferModel */
		$transferModel = $this->container->factory->model('Transfer')->tmpInstance();
		$latestBackup  = $transferModel->getLatestBackupInformation($profileID);

		if (empty($latestBackup))
		{
			throw new \RuntimeException(JText::sprintf('COM_AKEEBA_RESTORE_ERROR_NO_LATEST', $profileID));
		}

		return $latestBackup['id'];
	}

	/**
	 * Creates the restoration.php file which is used to configure Akeeba Restore (restore.php). Without it, resotre.php
	 * is completely inert, preventing abuse.
	 *
	 * @return  bool
	 */
	function createRestorationINI()
	{
		// Get a password
		$this->password = $this->makeRandomPassword(32);
		$this->setState('password', $this->password);

		// Do we have to use FTP?
		$procengine = $this->getState('procengine', 'direct');

		// Get the absolute path to site's root
		$siteroot = JPATH_SITE;

		// Get the JPS password
		$password = addslashes($this->getState('jps_key'));

		$data = "<?php\ndefined('_AKEEBA_RESTORATION') or die();\n";
		$data .= '$restoration_setup = array(' . "\n";
		$data .= <<<ENDDATA
	'kickstart.security.password' => '{$this->password}',
	'kickstart.tuning.max_exec_time' => '5',
	'kickstart.tuning.run_time_bias' => '75',
	'kickstart.tuning.min_exec_time' => '0',
	'kickstart.procengine' => '$procengine',
	'kickstart.setup.sourcefile' => '{$this->path}',
	'kickstart.setup.destdir' => '$siteroot',
	'kickstart.setup.restoreperms' => '0',
	'kickstart.setup.filetype' => '{$this->extension}',
	'kickstart.setup.dryrun' => '0',
	'kickstart.jps.password' => '$password'
ENDDATA;

		if ($procengine == 'ftp')
		{
			$ftp_host = $this->getState('ftp_host', '');
			$ftp_port = $this->getState('ftp_port', '21');
			$ftp_user = $this->getState('ftp_user', '');
			$ftp_pass = addcslashes($this->getState('ftp_pass', ''), "'\\");
			$ftp_root = $this->getState('ftp_root', '');
			$ftp_ssl  = $this->getState('ftp_ssl', 0);
			$ftp_pasv = $this->getState('ftp_root', 1);
			$tempdir  = $this->getState('tmp_path', '');
			$data .= <<<ENDDATA
	,
	'kickstart.ftp.ssl' => '$ftp_ssl',
	'kickstart.ftp.passive' => '$ftp_pasv',
	'kickstart.ftp.host' => '$ftp_host',
	'kickstart.ftp.port' => '$ftp_port',
	'kickstart.ftp.user' => '$ftp_user',
	'kickstart.ftp.pass' => '$ftp_pass',
	'kickstart.ftp.dir' => '$ftp_root',
	'kickstart.ftp.tempdir' => '$tempdir'
ENDDATA;
		}

		$data .= ');';

		// Remove the old file, if it's there...
		JLoader::import('joomla.filesystem.file');
		$configpath = JPATH_COMPONENT_ADMINISTRATOR . '/restoration.php';
		clearstatcache(true, $configpath);

		if (@file_exists($configpath))
		{
			if (!@unlink($configpath))
			{
				JFile::delete($configpath);
			}
		}

		// Write new file
		$result = JFile::write($configpath, $data);

		// Clear opcode caches for the generated .php file
		if (function_exists('opcache_invalidate'))
		{
			opcache_invalidate($configpath);
		}

		if (function_exists('apc_compile_file'))
		{
			apc_compile_file($configpath);
		}

		if (function_exists('wincache_refresh_if_changed'))
		{
			wincache_refresh_if_changed(array($configpath));
		}

		if (function_exists('xcache_asm'))
		{
			xcache_asm($configpath);
		}

		return $result;
	}

	/**
	 * Handles an AJAX request
	 *
	 * @return  mixed
	 */
	public function doAjax()
	{
		$ajax = $this->getState('ajax');
		switch ($ajax)
		{
			// FTP Connection test for DirectFTP
			case 'testftp':
				// Grab request parameters
				$config = array(
					'host'    => $this->input->get('host', '', 'none', 2),
					'port'    => $this->input->get('port', 21, 'int'),
					'user'    => $this->input->get('user', '', 'none', 2),
					'pass'    => $this->input->get('pass', '', 'none', 2),
					'initdir' => $this->input->get('initdir', '', 'none', 2),
					'usessl'  => $this->input->get('usessl', 'cmd') == 'true',
					'passive' => $this->input->get('passive', 'cmd') == 'true'
				);

				// Perform the FTP connection test
				$test = new \Akeeba\Engine\Archiver\Directftp();
				$test->initialize('', $config);
				$errors = $test->getError();
				if (empty($errors))
				{
					$result = true;
				}
				else
				{
					$result = $errors;
				}
				break;

			// Unrecognized AJAX task
			default:
				$result = false;
				break;
		}

		return $result;
	}
}
