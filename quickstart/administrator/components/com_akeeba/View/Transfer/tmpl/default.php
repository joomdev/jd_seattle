<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  $this  \Akeeba\Backup\Admin\View\Transfer\Html */
?>

<?php if ($this->force):?>
	<div class="akeeba-block--warning">
		<h3><?php echo JText::_('COM_AKEEBA_TRANSFER_FORCE_HEADER')?></h3>
		<p><?php echo JText::_('COM_AKEEBA_TRANSFER_FORCE_BODY')?></p>
	</div>
<?php endif; ?>

<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/FTPBrowser'); ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/SFTPBrowser'); ?>

<?php echo $this->loadAnyTemplate('admin:com_akeeba/transfer/default_prerequisites'); ?>

<?php if ( ! (empty($this->latestBackup))): ?>
	<?php echo $this->loadAnyTemplate('admin:com_akeeba/transfer/default_remoteconnection'); ?>
	<?php echo $this->loadAnyTemplate('admin:com_akeeba/transfer/default_manualtransfer'); ?>
	<?php echo $this->loadAnyTemplate('admin:com_akeeba/transfer/default_upload'); ?>
<?php endif; ?>
