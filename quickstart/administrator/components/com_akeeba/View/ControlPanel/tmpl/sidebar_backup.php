<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') or die();

?>
<div class="akeeba-panel">
    <header class="akeeba-block-header">
        <h3><?php echo \JText::_('COM_AKEEBA_BACKUP_STATS'); ?></h3>
    </header>
    <div><?php echo $this->latestBackupCell ?></div>
</div>
