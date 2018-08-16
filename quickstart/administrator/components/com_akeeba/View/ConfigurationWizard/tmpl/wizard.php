<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

?>

<div id="akeeba-confwiz">

    <div id="backup-progress-pane">
        <div class="akeeba-block--warning">
                <?php echo \JText::_('COM_AKEEBA_CONFWIZ_INTROTEXT'); ?>
        </div>

        <fieldset id="backup-progress-header">
            <h3>
	            <?php echo \JText::_('COM_AKEEBA_CONFWIZ_PROGRESS'); ?>
            </h3>
            <div id="backup-progress-content">
                <div id="backup-steps">
                    <div id="step-ajax" class="akeeba-label--grey">
                        <?php echo \JText::_('COM_AKEEBA_CONFWIZ_AJAX'); ?>
                    </div>
                    <div id="step-minexec" class="akeeba-label--grey">
                        <?php echo \JText::_('COM_AKEEBA_CONFWIZ_MINEXEC'); ?>
                    </div>
                    <div id="step-directory" class="akeeba-label--grey">
                        <?php echo \JText::_('COM_AKEEBA_CONFWIZ_DIRECTORY'); ?>
                    </div>
                    <div id="step-dbopt" class="akeeba-label--grey">
                        <?php echo \JText::_('COM_AKEEBA_CONFWIZ_DBOPT'); ?>
                    </div>
                    <div id="step-maxexec" class="akeeba-label--grey">
                        <?php echo \JText::_('COM_AKEEBA_CONFWIZ_MAXEXEC'); ?>
                    </div>
                    <div id="step-splitsize" class="akeeba-label--grey">
                        <?php echo \JText::_('COM_AKEEBA_CONFWIZ_SPLITSIZE'); ?>
                    </div>
                </div>
                <div class="backup-steps-container">
                    <div id="backup-substep">&nbsp;</div>
                </div>
            </div>
            <span id="ajax-worker"></span>
        </fieldset>

    </div>

    <div id="error-panel" class="akeeba-block--failure" style="display:none">
        <h2 class="alert-heading"><?php echo \JText::_('COM_AKEEBA_CONFWIZ_HEADER_FAILED'); ?></h2>
        <div id="errorframe">
            <p id="backup-error-message">
            </p>
        </div>
    </div>

    <div id="backup-complete" style="display: none">
        <div class="akeeba-block--success">
            <h2 class="alert-heading"><?php echo \JText::_('COM_AKEEBA_CONFWIZ_HEADER_FINISHED'); ?></h2>
            <div id="finishedframe">
                <p>
                    <?php echo \JText::_('COM_AKEEBA_CONFWIZ_CONGRATS'); ?>
                </p>
                <p>
                    <a
                            class="akeeba-btn--primary akeeba-btn--big"
                            href="<?php echo $this->escape(JUri::base()); ?>index.php?option=com_akeeba&view=Backup">
                        <span class="akion-play"></span>
			            <?php echo \JText::_('COM_AKEEBA_BACKUP'); ?>
                    </a>
                    <a
                            class="akeeba-btn--ghost" href="<?php echo $this->escape(JUri::base()); ?>index.php?option=com_akeeba&view=Configuration">
                        <span class="akion-wrench"></span>
			            <?php echo \JText::_('COM_AKEEBA_CONFIG'); ?>
                    </a>
                    <a
                            class="akeeba-btn--ghost"
                            href="<?php echo $this->escape(JUri::base()); ?>index.php?option=com_akeeba&view=Schedule">
                        <span class="akion-calendar"></span>
			            <?php echo \JText::_('COM_AKEEBA_SCHEDULE'); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
