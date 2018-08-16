<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Restore;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\Model\Restore;
use Akeeba\Engine\Platform;
use FOF30\View\DataView\Html as BaseView;
use JFactory;
use JHtml;
use JText;

class Html extends BaseView
{
	public $password;
	public $id;
	public $ftpparams;
	public $extractionmodes;
	public $extension;

	protected function onBeforeMain()
	{
		$this->loadCommonJavascript();

		/** @var Restore $model */
		$model = $this->getModel();

		$this->id              = $model->getState('id');
		$this->ftpparams       = $this->getFTPParams();
		$this->extractionmodes = $this->getExtractionModes();

		$backup = Platform::getInstance()->get_statistics($this->id);
		$this->extension       = strtolower(substr($backup['absolute_path'], -3));
	}

	protected function onBeforeStart()
	{
		$this->loadCommonJavascript();

		/** @var Restore $model */
		$model = $this->getModel();

		$password       = $model->getState('password');
		$this->password = $password;
		$this->setLayout('restore');
	}

	/**
	 * Returns the available extraction modes for use by JHtml
	 *
	 * @return  array
	 */
	private function getExtractionModes()
	{
		$options   = array();
		$options[] = JHtml::_('select.option', 'hybrid', JText::_('COM_AKEEBA_RESTORE_LABEL_EXTRACTIONMETHOD_HYBRID'));
		$options[] = JHtml::_('select.option', 'direct', JText::_('COM_AKEEBA_RESTORE_LABEL_EXTRACTIONMETHOD_DIRECT'));
		$options[] = JHtml::_('select.option', 'ftp', JText::_('COM_AKEEBA_RESTORE_LABEL_EXTRACTIONMETHOD_FTP'));

		return $options;
	}

	/**
	 * Returns the FTP parameters from the Global Configuration
	 *
	 * @return  array
	 */
	private function getFTPParams()
	{
		$config = $this->container->platform->getConfig();

		return array(
			'procengine' => $config->get('ftp_enable', 0) ? 'hybrid' : 'direct',
			'ftp_host'   => $config->get('ftp_host', 'localhost'),
			'ftp_port'   => $config->get('ftp_port', '21'),
			'ftp_user'   => $config->get('ftp_user', ''),
			'ftp_pass'   => $config->get('ftp_pass', ''),
			'ftp_root'   => $config->get('ftp_root', ''),
			'tempdir'    => $config->get('tmp_path', '')
		);
	}

	private function loadCommonJavascript()
	{
		$this->addJavascriptFile('media://com_akeeba/js/Encryption.min.js');
		$this->addJavascriptFile('media://com_akeeba/js/Configuration.min.js');
		$this->addJavascriptFile('media://com_akeeba/js/Restore.min.js');

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
		JText::script('COM_AKEEBA_BACKUP_TEXT_LASTRESPONSE');
	}
}
