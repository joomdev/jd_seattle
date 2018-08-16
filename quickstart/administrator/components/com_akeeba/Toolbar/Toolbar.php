<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Toolbar;

// Protect from unauthorized access
defined('_JEXEC') or die();

use FOF30\Toolbar\Toolbar as BaseToolbar;
use JToolbar;
use JToolbarHelper;
use JText;
use JFactory;

class Toolbar extends BaseToolbar
{
	public function onAlices()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA_TITLE_ALICES'),'akeeba');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_akeeba&view=ControlPanel');
	}

	public function onBackupsMain()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').':: <small>'.JText::_('COM_AKEEBA_BACKUP').'</small>','akeeba');

		if (!$this->container->input->getBool('akeeba_hide_toolbar', false))
		{
			JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');

			JToolbarHelper::spacer();
			JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/backup-now.html');
		}
	}

	public function onConfigurations()
	{
		$bar = JToolbar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_AKEEBA').':: <small>'.JText::_('COM_AKEEBA_CONFIG').'</small>','akeeba');
		JToolbarHelper::preferences('com_akeeba', '500', '660');
		JToolbarHelper::spacer();
		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::spacer();
		JToolbarHelper::custom('savenew', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		JToolbarHelper::cancel();
		JToolbarHelper::spacer();

		// Configuration wizard button. We apply styling to it.
		$bar->appendButton('Link', 'lightning', '<strong>' . JText::_('COM_AKEEBA_CONFWIZ') . '</strong>', 'index.php?option=com_akeeba&view=ConfigurationWizard');

		JToolbarHelper::spacer();

		$bar->appendButton('Link', 'calendar', JText::_('COM_AKEEBA_SCHEDULE'), 'index.php?option=com_akeeba&view=Schedule');

		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/configuration.html');

		$js = <<< JS
;;

jQuery(document).ready(function(){
	jQuery('#toolbar-lightning>button').addClass('btn-primary');
});

JS;
		JFactory::getDocument()->addScriptDeclaration($js);
	}

	public function onConfigurationWizardsMain()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').':: <small>'.JText::_('COM_AKEEBA_CONFWIZ').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/configuration-wizard.html');
	}

	public function onControlPanelsMain()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').' :: <small>'.JText::_('COM_AKEEBA_CONTROLPANEL').'</small>','akeeba');
		JToolbarHelper::preferences('com_akeeba', '500', '660');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/using-akeeba-backup-component.html#control-panel');
	}

	public function onDatabaseFiltersMain()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_DBFILTER').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/database-tables-exclusion.html');
	}

	public function onDiscovers()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_DISCOVER').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/ch03s02s05s03.html');
	}

	public function onFileFiltersMain()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_FILEFILTERS').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/exclude-data-from-backup.html#files-and-directories-exclusion');
	}

	public function onIncludeFoldersMain()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_INCLUDEFOLDER').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/off-site-directories-inclusion.html');
	}

	public function onLogs()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_LOG').'</small>','akeeba');

		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/view-log.html');
	}

	public function onManagesDefault()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA') . ': <small>' . JText::_('COM_AKEEBA_BUADMIN') . '</small>', 'akeeba');

		if (AKEEBA_PRO)
		{
			$bar = JToolbar::getInstance('toolbar');
			$bar->appendButton('Link', 'restore', JText::_('COM_AKEEBA_DISCOVER'), 'index.php?option=com_akeeba&view=Discover');
		}

		$user        = $this->container->platform->getUser();
		$permissions = [
			'configure' => $user->authorise('akeeba.configure', 'com_akeeba'),
			'backup'    => $user->authorise('akeeba.backup', 'com_akeeba'),
		];

		if ($permissions['configure'])
		{
			JToolbarHelper::publish('restore', JText::_('COM_AKEEBA_BUADMIN_LABEL_RESTORE'));
		}

		if ($permissions['backup'])
		{
			JToolbarHelper::editList('showcomment', JText::_('COM_AKEEBA_BUADMIN_LOG_EDITCOMMENT'));
		}

		if ($permissions['configure'] || $permissions['backup'])
		{
			JToolbarHelper::spacer();
		}

		if ($permissions['backup'])
		{
			JToolbarHelper::deleteList();
			JToolbarHelper::custom('deletefiles', 'delete.png', 'delete_f2.png', JText::_('COM_AKEEBA_BUADMIN_LABEL_DELETEFILES'), true);
			JToolbarHelper::spacer();
		}

		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/adminsiter-backup-files.html');
	}

	public function onManagesShowcomment()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_BUADMIN').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::save();
		JToolbarHelper::cancel();
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/adminsiter-backup-files.html');
	}

	public function onMultipleDatabasesMain()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_MULTIDB').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/include-data-to-archive.html#multiple-db-definitions');
	}

	public function onProfilesAdd()
	{
		parent::onAdd();
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_PROFILES_PAGETITLE_NEW').'</small>','akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/using-basic-operations.html#profiles-management');
	}

	public function onProfilesBrowse()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_PROFILES').'</small>','akeeba');

		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::addNew();
		JToolbarHelper::custom('copy', 'copy.png', 'copy_f2.png', 'COM_AKEEBA_LBL_BATCH_COPY', false);
		JToolbarHelper::spacer();
		JToolbarHelper::deleteList();
		JToolbarHelper::spacer();
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/using-basic-operations.html#profiles-management');
	}

	public function onProfilesEdit()
	{
		parent::onEdit();
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_PROFILES_PAGETITLE_EDIT').'</small>','akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/using-basic-operations.html#profiles-management');
	}

	public function onRegExDatabaseFiltersMain()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_REGEXDBFILTERS').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/regex-database-tables-exclusion.html');
	}

	public function onRegExFileFiltersMain()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_REGEXFSFILTERS').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/regex-files-directories-exclusion.html');
	}

	public function onRemoteFilesDownloadToServer()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA_REMOTEFILES'),'akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/ch03s02s05s02.html');
	}

	public function onRemoteFilesListActions()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA_REMOTEFILES'),'akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/ch03s02s05s02.html');
	}

	public function onRestores()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_RESTORE').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/adminsiter-backup-files.html#integrated-restoration');
	}

	public function onS3ImportsMain()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_S3IMPORT').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/ch03s02s05s03.html');
	}

	public function onS3ImportsDltoserver()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_S3IMPORT').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/ch03s02s05s03.html');
	}

	public function onSchedules()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').':: <small>'.JText::_('COM_AKEEBA_SCHEDULE').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');
		JToolbarHelper::spacer();
		JToolbarHelper::help(null, false, 'https://www.akeebabackup.com/documentation/akeeba-backup-documentation/automating-your-backup.html');
	}

	public function onStatisticsMain()
	{
		$this->onManagesDefault();
	}

	public function onTransfers()
	{
		JToolbarHelper::title(JText::_('COM_AKEEBA').': <small>'.JText::_('COM_AKEEBA_TRANSFER').'</small>','akeeba');
		JToolbarHelper::back('COM_AKEEBA_CONTROLPANEL', 'index.php?option=com_akeeba');

		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Link', 'loop', 'COM_AKEEBA_TRANSFER_BTN_RESET', 'index.php?option=com_akeeba&view=Transfer&task=reset');
	}
}
