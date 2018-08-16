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
<section class="akeeba-panel--info">
    <header class="akeeba-block-header">
        <h3><?php echo \JText::_('COM_AKEEBA_CPANEL_HEADER_TROUBLESHOOTING'); ?></h3>
    </header>

    <div class="akeeba-grid--small">
	    <?php if ($this->permissions['backup']): ?>
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Log">
                <span class="akion-ios-search-strong"></span>
	            <?php echo \JText::_('COM_AKEEBA_LOG'); ?>
            </a>
	    <?php endif; ?>

	    <?php if (AKEEBA_PRO && $this->permissions['configure']): ?>
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Alice">
                <span class="akion-medkit"></span>
	            <?php echo \JText::_('COM_AKEEBA_ALICE'); ?>
            </a>
	    <?php endif; ?>
    </div>
</section>
