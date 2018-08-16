<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  $this  \Akeeba\Backup\Admin\View\Transfer\Html */

$classModifier = empty($this->latestBackup) ? 'red' : 'information';

?>
<div class="akeeba-panel--<?php echo $classModifier ?>">
    <header class="akeeba-block-header">
        <h3>
			<?php echo \JText::_('COM_AKEEBA_TRANSFER_HEAD_PREREQUISITES'); ?>
        </h3>
    </header>

    <table class="akeeba-table akeeba-table--striped" width="100%">
        <tbody>
        <tr>
            <td>
                <strong>
					<?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_COMPLETEBACKUP'); ?>
                </strong>

                <br/>
                <small>
					<?php if (empty($this->latestBackup)): ?>
						<?php echo \JText::_('COM_AKEEBA_TRANSFER_ERR_COMPLETEBACKUP'); ?>
					<?php else: ?>
						<?php echo \JText::sprintf('COM_AKEEBA_TRANSFER_LBL_COMPLETEBACKUP_INFO', $this->lastBackupDate); ?>
					<?php endif; ?>
                </small>
            </td>
            <td width="20%">
				<?php if (empty($this->latestBackup)): ?>
                    <a href="index.php?option=com_akeeba&view=Backup" class="akeeba-btn--green"
                       id="akeeba-transfer-btn-backup">
						<?php echo \JText::_('COM_AKEEBA_BACKUP_LABEL_START'); ?>
                    </a>
				<?php endif; ?>
            </td>
        </tr>
		<?php if (!(empty($this->latestBackup))): ?>
            <tr>
                <td>
                    <strong>
						<?php echo \JText::sprintf('COM_AKEEBA_TRANSFER_LBL_SPACE', $this->spaceRequired['string']); ?>
                    </strong>
                    <br/>
                    <small id="akeeba-transfer-err-space" style="display: none">
						<?php echo \JText::_('COM_AKEEBA_TRANSFER_ERR_SPACE'); ?>
                    </small>
                </td>
                <td>
                </td>
            </tr>
		<?php endif; ?>
        </tbody>
    </table>
</div>

