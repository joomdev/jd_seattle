<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Configuration;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\View\DataView\Html as BaseView;
use JText;

class Html extends BaseView
{
	use ProfileIdAndName;

	/**
	 * The configuration definition and values in JSON format, for use by the configuration GUI renderer
	 *
	 * @var  string
	 */
	public $json = '';

	/**
	 * Status of the settings encryption: -1 disabled by user, 0 not available, 1 enabled and active
	 *
	 * @var  int
	 */
	public $securesettings = 0;

	/**
	 * Should I show the Configuration Wizard popup prompt?
	 *
	 * @var  bool
	 */
	public $promptForConfigurationWizard = false;

	/**
	 * Executes when displaying the page
	 */
	public function onBeforeMain()
	{
		$this->addJavascriptFile('media://com_akeeba/js/Configuration.min.js');

		// Get a JSON representation of GUI data
		$json       = Factory::getEngineParamsProvider()->getJsonGuiDefinition();
		$this->json = $json;

		$this->getProfileIdAndName();

		// Are the settings secured?
		$this->securesettings = $this->getSecureSettingsOption();

		// Should I show the Configuration Wizard popup prompt?
		$this->promptForConfigurationWizard = Factory::getConfiguration()->get('akeeba.flag.confwiz', 0) != 1;

		// Push translations
		JText::script('COM_AKEEBA_CONFIG_UI_BROWSE');
		JText::script('COM_AKEEBA_CONFIG_UI_CONFIG');
		JText::script('COM_AKEEBA_CONFIG_UI_REFRESH');
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		JText::script('COM_AKEEBA_CONFIG_UI_FTPBROWSER_TITLE');
		JText::script('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_OK');
		JText::script('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_FAIL');
		JText::script('COM_AKEEBA_CONFIG_DIRECTSFTP_TEST_OK');
		JText::script('COM_AKEEBA_CONFIG_DIRECTSFTP_TEST_FAIL');
	}

	/**
	 * Returns the support status of settings encryption. The possible values are:
	 * -1 Disabled by the user
	 *  0 Enabled by inactive (not supported by the server)
	 *  1 Enabled and active
	 *
	 * @return  int
	 */
	private function getSecureSettingsOption()
	{
		// Encryption is disabled by the user
		if (Platform::getInstance()->get_platform_configuration_option('useencryption', -1) == 0)
		{
			return -1;
		}

		// Encryption is not supported by this server
		if (!Factory::getSecureSettings()->supportsEncryption())
		{
			return 0;
		}

		$filename = JPATH_COMPONENT_ADMINISTRATOR . '/BackupEngine/serverkey.php';

		// Encryption enabled, supported and a key file is present: encryption enabled
		if (is_file($filename))
		{
			return 1;
		}

		// Encryption enabled, supported but and a key file is NOT present: encryption not available
		return 0;
	}
}
