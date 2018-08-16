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
        <h3><?php echo \JText::_('COM_AKEEBA_CPANEL_HEADER_INCLUDEEXCLUDE'); ?></h3>
    </header>

    <div class="akeeba-grid--small">
        <?php if(AKEEBA_PRO): ?>
            <a class="akeeba-action--green"
                href="index.php?option=com_akeeba&view=MultipleDatabases">
                <span class="akion-arrow-swap"></span>
	            <?php echo \JText::_('COM_AKEEBA_MULTIDB'); ?>
            </a>

            <a class="akeeba-action--green"
                href="index.php?option=com_akeeba&view=IncludeFolders">
                <span class="akion-folder"></span>
	            <?php echo \JText::_('COM_AKEEBA_INCLUDEFOLDER'); ?>
            </a>
        <?php endif; ?>

        <a class="akeeba-action--red"
            href="index.php?option=com_akeeba&view=FileFilters">
            <span class="akion-filing"></span>
	        <?php echo \JText::_('COM_AKEEBA_FILEFILTERS'); ?>
        </a>

        <a class="akeeba-action--red"
            href="index.php?option=com_akeeba&view=DatabaseFilters">
            <span class="akion-ios-grid-view"></span>
	        <?php echo \JText::_('COM_AKEEBA_DBFILTER'); ?>
        </a>

        <?php if(AKEEBA_PRO): ?>
            <a class="akeeba-action--red"
                href="index.php?option=com_akeeba&view=RegExFileFilters">
                <span class="akion-ios-folder"></span>
	            <?php echo \JText::_('COM_AKEEBA_REGEXFSFILTERS'); ?>
            </a>

            <a class="akeeba-action--red"
                href="index.php?option=com_akeeba&view=RegExDatabaseFilters">
                <span class="akion-ios-box"></span>
	            <?php echo \JText::_('COM_AKEEBA_REGEXDBFILTERS'); ?>
            </a>
        <?php endif; ?>

    </div></section>
