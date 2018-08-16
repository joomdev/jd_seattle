<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Transfer;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\Model\Transfer;
use FOF30\View\DataView\Html as BaseView;
use JFactory;
use JHtml;
use JText;

class Html extends BaseView
{
	/** @var   array|null  Latest backup information */
	public $latestBackup = [];

	/** @var   string  Date of the latest backup, human readable */
	public $lastBackupDate = '';

	/** @var   array  Space required on the target server */
	public $spaceRequired = [
		'size'   => 0,
		'string' => '0.00 Kb'
	];

	/** @var   string  The URL to the site we are restoring to (from the session) */
	public $newSiteUrl = '';

	/** @var   string   */
	public $newSiteUrlResult = '';

	/** @var   array  Results of support and firewall status of the known file transfer methods */
	public $ftpSupport = [
		'supported'	=> [
			'ftp'	=> false,
			'ftps'	=> false,
			'sftp'	=> false,
		],
		'firewalled'	=> [
			'ftp'	=> false,
			'ftps'	=> false,
			'sftp'	=> false
		]
	];

	/** @var   array  Available transfer options, for use by JHTML */
	public $transferOptions = [];

	/** @var   array  Available chunk options, for use by JHTML */
	public $chunkOptions = array();

	/** @var   array  Available chunk size options, for use by JHTML */
	public $chunkSizeOptions = array();

	/** @var   bool  Do I have supported but firewalled methods? */
	public $hasFirewalledMethods = false;

	/** @var   string  Currently selected transfer option */
	public $transferOption = 'manual';

	/** @var   string  Currently selected chunk option */
	public $chunkMode = 'chunked';

	/** @var   string  Currently selected chunk size */
	public $chunkSize = 5242880;

	/** @var   string  FTP/SFTP host name */
	public $ftpHost = '';

	/** @var   string  FTP/SFTP port (empty for default port) */
	public $ftpPort = '';

	/** @var   string  FTP/SFTP username */
	public $ftpUsername = '';

	/** @var   string  FTP/SFTP password – or certificate password if you're using SFTP with SSL certificates */
	public $ftpPassword = '';

	/** @var   string  SFTP public key certificate path */
	public $ftpPubKey = '';

	/** @var   string  SFTP private key certificate path */
	public $ftpPrivateKey = '';

	/** @var   string  FTP/SFTP directory to the new site's root */
	public $ftpDirectory = '';

	/** @var   string  FTP passive mode (default is true) */
	public $ftpPassive = true;

	/** @var   string  FTP passive mode workaround, for FTP/FTPS over cURL (default is true) */
	public $ftpPassiveFix = true;

	/** @var   int     Forces the transfer by skipping some checks on the target site */
	public $force = 0;

	/**
	 * Translations to pass to the view
	 *
	 * @var  array
	 */
	public $translations = [];

	protected function onBeforeMain()
	{
		$this->addJavascriptFile('media://com_akeeba/js/Transfer.min.js');

		/** @var Transfer $model */
		$model   = $this->getModel();

		$this->latestBackup     = $model->getLatestBackupInformation();
		$this->spaceRequired    = $model->getApproximateSpaceRequired();
		$this->newSiteUrl       = $this->container->platform->getSessionVar('transfer.url', '', 'akeeba');
		$this->newSiteUrlResult = $this->container->platform->getSessionVar('transfer.url_status', '', 'akeeba');
		$this->ftpSupport       = $this->container->platform->getSessionVar('transfer.ftpsupport', null, 'akeeba');
		$this->transferOption   = $this->container->platform->getSessionVar('transfer.transferOption', null, 'akeeba');
		$this->chunkMode        = $this->container->platform->getSessionVar('transfer.chunkMode', 'chunked', 'akeeba');
		$this->chunkSize        = $this->container->platform->getSessionVar('transfer.uploadLimit', 5242880, 'akeeba');
		$this->ftpHost          = $this->container->platform->getSessionVar('transfer.ftpHost', null, 'akeeba');
		$this->ftpPort          = $this->container->platform->getSessionVar('transfer.ftpPort', null, 'akeeba');
		$this->ftpUsername      = $this->container->platform->getSessionVar('transfer.ftpUsername', null, 'akeeba');
		$this->ftpPassword      = $this->container->platform->getSessionVar('transfer.ftpPassword', null, 'akeeba');
		$this->ftpPubKey        = $this->container->platform->getSessionVar('transfer.ftpPubKey', null, 'akeeba');
		$this->ftpPrivateKey    = $this->container->platform->getSessionVar('transfer.ftpPrivateKey', null, 'akeeba');
		$this->ftpDirectory     = $this->container->platform->getSessionVar('transfer.ftpDirectory', null, 'akeeba');
		$this->ftpPassive       = $this->container->platform->getSessionVar('transfer.ftpPassive', 1, 'akeeba');
		$this->ftpPassiveFix    = $this->container->platform->getSessionVar('transfer.ftpPassiveFix', 1, 'akeeba');

		// We get this option from the request
		$this->force = $this->input->getInt('force', 0);

		if (!empty($this->latestBackup))
		{
			$lastBackupDate       = $this->getContainer()->platform->getDate($this->latestBackup['backupstart'], 'UTC');
			$tz                  = new \DateTimeZone($this->container->platform->getUser()->getParam('timezone', $this->container->platform->getConfig()->get('offset')));
			$lastBackupDate->setTimezone($tz);

			$this->lastBackupDate = $lastBackupDate->format(JText::_('DATE_FORMAT_LC2'), true);

			$this->container->platform->setSessionVar('transfer.lastBackup', $this->latestBackup, 'akeeba');
		}

		if (empty($this->ftpSupport))
		{
			$this->ftpSupport = $model->getFTPSupport();
			$this->container->platform->setSessionVar('transfer.ftpsupport', $this->ftpSupport, 'akeeba');
		}

		$this->transferOptions = $this->getTransferMethodOptions();
		$this->chunkOptions     = $this->getChunkOptions();
		$this->chunkSizeOptions = $this->getChunkSizeOptions();

		/*
		foreach ($this->ftpSupport['firewalled'] as $method => $isFirewalled)
		{
			if ($isFirewalled && $this->ftpSupport['supported'][$method])
			{
				$this->hasFirewalledMethods = true;

				break;
			}
		}
		*/

		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		JText::script('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_FAIL');

		$js = <<< JS
akeeba.System.documentReady(function(){
	// AJAX URL endpoint
	akeeba.System.params.AjaxURL = 'index.php?option=com_akeeba&view=Transfer&format=raw&force={$this->force}';

	// Last results of new site URL processing
	akeeba.Transfer.lastUrl = '{$this->newSiteUrl}';
	akeeba.Transfer.lastResult = '{$this->newSiteUrlResult}';

	// Auto-process URL change event
	if (document.getElementById('akeeba-transfer-url').value)
	{
		akeeba.Transfer.onUrlChange();
	}
	
	// Remote connection hooks
	if (document.getElementById('akeeba-transfer-ftp-method'))
	{
		akeeba.System.addEventListener(document.getElementById('akeeba-transfer-ftp-method'), 'change', akeeba.Transfer.onTransferMethodChange);
		//akeeba.System.addEventListener(document.getElementById('akeeba-transfer-ftp-directory-browse'), 'click', akeeba.Transfer.initFtpSftpBrowser);
		akeeba.System.addEventListener(document.getElementById('akeeba-transfer-btn-apply'), 'click', akeeba.Transfer.applyConnection);
		akeeba.System.addEventListener(document.getElementById('akeeba-transfer-err-url-notexists-btn-ignore'), 'click', akeeba.Transfer.showConnectionDetails);
	}
});
JS;

		$this->addJavascriptInline($js);
	}

	/**
	 * Returns the JHTML options for a transfer methods drop-down, filtering out the unsupported and firewalled methods
	 *
	 * @return   array
	 */
	private function getTransferMethodOptions()
	{
		$options = [];

		foreach ($this->ftpSupport['supported'] as $method => $supported)
		{
			if (!$supported)
			{
				continue;
			}

			$methodName = JText::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMETHOD_' . $method);

			if ($this->ftpSupport['firewalled'][$method])
			{
				$methodName = '&#128274; ' . $methodName;
			}

			$options[] = JHtml::_('select.option', $method, $methodName);
		}

		$options[] = JHtml::_('select.option', 'manual', JText::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMETHOD_MANUALLY'));

		return $options;
	}

	/**
	 * Returns the JHTML options for a chunk methods drop-down
	 *
	 * @return   array
	 */
	private function getChunkOptions()
	{
		$options = array();

		$options[] = array('value' => 'chunked', 'text' => JText::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMODE_CHUNKED'));
		$options[] = array('value' => 'post', 'text' => JText::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMODE_POST'));

		return $options;
	}

	/**
	 * Returns the JHTML options for a chunk size drop-down
	 *
	 * @return   array
	 */
	private function getChunkSizeOptions()
	{
		$options    = array();
		$multiplier = 1048576;

		$options[] = array('value' => 0.5 * $multiplier, 'text' => '512 KB');
		$options[] = array('value' => 1 * $multiplier, 'text' => '1 MB');
		$options[] = array('value' => 2 * $multiplier, 'text' => '2 MB');
		$options[] = array('value' => 5 * $multiplier, 'text' => '5 MB');
		$options[] = array('value' => 10 * $multiplier, 'text' => '10 MB');
		$options[] = array('value' => 20 * $multiplier, 'text' => '20 MB');
		$options[] = array('value' => 30 * $multiplier, 'text' => '30 MB');
		$options[] = array('value' => 50 * $multiplier, 'text' => '50 MB');
		$options[] = array('value' => 100 * $multiplier, 'text' => '100 MB');

		return $options;
	}
}
