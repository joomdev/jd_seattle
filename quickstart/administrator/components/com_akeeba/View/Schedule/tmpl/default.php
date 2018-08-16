<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

?>
<?php echo \JHtml::_('bootstrap.startTabSet', 'akeebabackup-scheduling', array('active' => 'akeebabackup-scheduling-backups')); ?>
<?php echo \JHtml::_('bootstrap.addTab', 'akeebabackup-scheduling', 'akeebabackup-scheduling-backups', JText::_('COM_AKEEBA_SCHEDULE_LBL_RUN_BACKUPS', true)); ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/Schedule/default_runbackups'); ?>
<?php echo \JHtml::_('bootstrap.endTab'); ?>
<?php echo \JHtml::_('bootstrap.addTab', 'akeebabackup-scheduling', 'akeebabackup-scheduling-checkbackups', JText::_('COM_AKEEBA_SCHEDULE_LBL_CHECK_BACKUPS', true)); ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/Schedule/default_checkbackups'); ?>
<?php echo \JHtml::_('bootstrap.endTab'); ?>
<?php echo \JHtml::_('bootstrap.endTabSet'); ?>
