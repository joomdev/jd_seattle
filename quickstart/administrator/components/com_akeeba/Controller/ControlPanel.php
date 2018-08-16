<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Controller;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\Controller\Mixin\CustomACL;
use Akeeba\Backup\Admin\Controller\Mixin\PredefinedTaskList;
use Akeeba\Backup\Admin\Model\ConfigurationWizard;
use Akeeba\Backup\Admin\Model\Updates;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\Container\Container;
use FOF30\Controller\Controller;
use JUri, JFactory, JText;

/**
 * The Control Panel controller class
 */
class ControlPanel extends Controller
{
	use CustomACL, PredefinedTaskList;

	public function __construct(Container $container, array $config = array())
	{
		parent::__construct($container, $config);

		$this->setPredefinedTaskList([
			'main', 'SwitchProfile', 'UpdateInfo', 'applydlid', 'resetSecretWord', 'reloadUpdateInformation', 'forceUpdateDb'
		]);
	}

	protected function onBeforeMain()
	{
		/** @var \Akeeba\Backup\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		$engineConfig = Factory::getConfiguration();

		// Invalidate stale backups
		$params = $this->container->params;

		Factory::resetState(array(
			'global' => true,
			'log'    => false,
			'maxrun' => $params->get('failure_timeout', 180)
		));

		// Just in case the reset() loaded a stale configuration...
		Platform::getInstance()->load_configuration();
		Platform::getInstance()->apply_quirk_definitions();

		// Let's make sure the temporary and output directories are set correctly and writable...
		/** @var ConfigurationWizard $wizmodel */
		$wizmodel = $this->container->factory->model('ConfigurationWizard')->tmpInstance();
		$wizmodel->autofixDirectories();

		// Check if we need to toggle the settings encryption feature
		$model->checkSettingsEncryption();

		// Run the automatic update site refresh
		/** @var Updates $updateModel */
		$updateModel = $this->container->factory->model('Updates')->tmpInstance();
		$updateModel->refreshUpdateSite();
	}

	public function SwitchProfile()
	{
		// CSRF prevention
		$this->csrfProtection();

		$newProfile = $this->input->get('profileid', -10, 'int');

		if (!is_numeric($newProfile) || ($newProfile <= 0))
		{
			$this->setRedirect(JUri::base() . 'index.php?option=com_akeeba', JText::_('COM_AKEEBA_CPANEL_PROFILE_SWITCH_ERROR'), 'error');

			return;
		}

		$this->container->platform->setSessionVar('profile', $newProfile, 'akeeba');
		$url       = '';
		$returnurl = $this->input->get('returnurl', '', 'base64');

		if (!empty($returnurl))
		{
			$url = base64_decode($returnurl);
		}

		if (empty($url))
		{
			$url = JUri::base() . 'index.php?option=com_akeeba';
		}

		$this->setRedirect($url, JText::_('COM_AKEEBA_CPANEL_PROFILE_SWITCH_OK'));
	}

	public function UpdateInfo()
	{
		/** @var Updates $updateModel */
		$updateModel = $this->container->factory->model('Updates')->tmpInstance();
		$infoArray   = $updateModel->getUpdates();
		$updateInfo  = (object)$infoArray;

		$result = '';

		if ($updateInfo->hasUpdate)
		{
			$strings = array(
				'header'  => JText::sprintf('COM_AKEEBA_CPANEL_MSG_UPDATEFOUND', $updateInfo->version),
				'button'  => JText::sprintf('COM_AKEEBA_CPANEL_MSG_UPDATENOW', $updateInfo->version),
				'infourl' => $updateInfo->infoURL,
				'infolbl' => JText::_('COM_AKEEBA_CPANEL_MSG_MOREINFO'),
			);

			$result = <<<HTML
	<div class="akeeba-block--warning">
		<h3>
			<span class="icon icon-exclamation-sign glyphicon glyphicon-exclamation-sign"></span>
			{$strings['header']}
		</h3>
		<p>
			<a href="index.php?option=com_installer&view=update" class="akeeba-btn--primary">
				{$strings['button']}
			</a>
			<a href="{$strings['infourl']}" target="_blank" class="akeeba-btn--ghost akeeba-btn--small">
				{$strings['infolbl']}
			</a>
		</p>
	</div>
HTML;
		}

		echo '###' . $result . '###';

		// Cut the execution short
		$this->container->platform->closeApplication();
	}

	/**
	 * Applies the Download ID when the user is prompted about it in the Control Panel
	 */
	public function applydlid()
	{
		// CSRF prevention
		$this->csrfProtection();

		$msg     = JText::_('COM_AKEEBA_CPANEL_ERR_INVALIDDOWNLOADID');
		$msgType = 'error';
		$dlid    = $this->input->getString('dlid', '');

		// If the Download ID seems legit let's apply it
		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			$msg     = null;
			$msgType = null;

			$this->container->params->set('update_dlid', $dlid);
			$this->container->params->save();
		}

		// Redirect back to the control panel
		$url       = '';
		$returnurl = $this->input->get('returnurl', '', 'base64');

		if (!empty($returnurl))
		{
			$url = base64_decode($returnurl);
		}

		if (empty($url))
		{
			$url = JUri::base() . 'index.php?option=com_akeeba';
		}

		$this->setRedirect($url, $msg, $msgType);
	}

	/**
	 * Reset the Secret Word for front-end and remote backup
	 *
	 * @return  void
	 */
	public function resetSecretWord()
	{
		// CSRF prevention
		$this->csrfProtection();

		$newSecret = $this->container->platform->getSessionVar('newSecretWord', null, 'akeeba.cpanel');

		if (empty($newSecret))
		{
			$random    = new \Akeeba\Engine\Util\RandomValue();
			$newSecret = $random->generateString(32);
			$this->container->platform->setSessionVar('newSecretWord', $newSecret, 'akeeba.cpanel');
		}

		$this->container->params->set('frontend_secret_word', $newSecret);
		$this->container->params->save();

		$msg = JText::sprintf('COM_AKEEBA_CPANEL_MSG_FESECRETWORD_RESET', $newSecret);

		$url = 'index.php?option=com_akeeba';
		$this->setRedirect($url, $msg);
	}

	public function reloadUpdateInformation()
	{
		$msg = null;

		/** @var Updates $model */
		$model = $this->container->factory->model('Updates')->tmpInstance();
		$model->getUpdates(true);

		$msg = JText::_('COM_AKEEBA_COMMON_UPDATE_INFORMATION_RELOADED');
		$url = 'index.php?option=com_akeeba';

		$this->setRedirect($url, $msg);
	}

	/**
	 * Resets the "updatedb" flag and forces the database updates
	 */
	public function forceUpdateDb()
	{
		// Reset the flag so the updates could take place
		$this->container->params->set('updatedb', null);
		$this->container->params->save();

		/** @var \Akeeba\Backup\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		try
		{
			$model->checkAndFixDatabase();
		}
		catch (\RuntimeException $e)
		{
			// This should never happen, since we reset the flag before execute the update, but you never know
		}

		$this->setRedirect('index.php?option=com_akeeba');
	}
}
