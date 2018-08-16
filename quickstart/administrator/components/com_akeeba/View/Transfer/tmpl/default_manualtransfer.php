<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\Helper\Utils;

/** @var  $this  \Akeeba\Backup\Admin\View\Transfer\Html */

$dotPos = strrpos($this->latestBackup['archivename'], '.');
$extension = substr($this->latestBackup['archivename'], $dotPos + 1);
$bareName = basename($this->latestBackup['archivename'], '.' . $extension);

?>
<div id="akeeba-transfer-manualtransfer" class="akeeba-panel--primary" style="display: none;">
	<header class="akeeba-block-header">
		<h3>
			<?php echo \JText::_('COM_AKEEBA_TRANSFER_HEAD_MANUALTRANSFER'); ?>
        </h3>
	</header>

	<div class="akeeba-block--info">
		<?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_MANUALTRANSFER_INFO'); ?>
	</div>

	<p style="text-align: center">
		<iframe width="640" height="480" src="https://www.youtube.com/embed/Ok_6CV2JSYg" frameborder="0" allowfullscreen></iframe>
	</p>

	<h4><?php echo \JText::_('COM_AKEEBA_BUADMIN_LBL_BACKUPINFO'); ?></h4>

    <h5>
	    <?php echo \JText::_('COM_AKEEBA_BUADMIN_LBL_ARCHIVENAME'); ?>
    </h5>

	<p>
		<?php if($this->latestBackup['multipart'] < 2): ?>
		<?php echo $this->escape($this->latestBackup['archivename']); ?>
		<?php else: ?>
		<?php echo \JText::sprintf('COM_AKEEBA_TRANSFER_LBL_MANUALTRANSFER_MULTIPART', $this->latestBackup['multipart']); ?>
		<?php endif; ?>
	</p>

	<?php if ($this->latestBackup['multipart'] >= 2): ?>
    <ul>
		<?php for($i = 1; $i < $this->latestBackup['multipart']; $i++): ?>
            <li><?php echo $this->escape($bareName . '.' . substr($extension, 0, 1) . sprintf('%02u', $i)); ?></li>
		<?php endfor; ?>
        <li>
			<?php echo $this->escape($this->latestBackup['archivename']); ?>

        </li>
    </ul>
    <?php endif; ?>

    <h5>
	    <?php echo JText::_('COM_AKEEBA_BUADMIN_LBL_ARCHIVEPATH') ?>
    </h5>
	<p>
		<?php echo $this->escape(Utils::getRelativePath(JPATH_SITE, dirname($this->latestBackup['absolute_path']))); ?>
	</p>
</div>
