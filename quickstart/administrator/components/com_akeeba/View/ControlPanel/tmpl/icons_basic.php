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
        <h3><?php echo \JText::_('COM_AKEEBA_CPANEL_HEADER_BASICOPS'); ?></h3>
    </header>

    <div class="akeeba-grid--small">
	    <?php if ($this->permissions['backup']): ?>
            <a class="akeeba-action--green"
               href="index.php?option=com_akeeba&view=Backup">
                <span class="akion-play"></span>
	            <?php echo \JText::_('COM_AKEEBA_BACKUP'); ?>
            </a>
	    <?php endif; ?>

	    <?php if ($this->permissions['download']): ?>
            <a class="akeeba-action--green"
                href="index.php?option=com_akeeba&view=Transfer">
                <span class="akion-android-open"></span>
	            <?php echo \JText::_('COM_AKEEBA_TRANSFER'); ?>
            </a>
	    <?php endif; ?>

        <a class="akeeba-action--teal"
            href="index.php?option=com_akeeba&view=Manage">
            <span class="akion-ios-list"></span>
	        <?php echo \JText::_('COM_AKEEBA_BUADMIN'); ?>
        </a>

	    <?php if ($this->permissions['configure']): ?>
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Configuration">
                <span class="akion-ios-gear"></span>
	            <?php echo \JText::_('COM_AKEEBA_CONFIG'); ?>
            </a>
	    <?php endif; ?>

	    <?php if ($this->permissions['configure']): ?>
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Profiles">
                <span class="akion-person-stalker"></span>
	            <?php echo \JText::_('COM_AKEEBA_PROFILES'); ?>
            </a>
	    <?php endif; ?>
    </div>
</section>
