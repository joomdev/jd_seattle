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
        <h3><?php echo \JText::_('COM_AKEEBA_CPANEL_LABEL_STATUSSUMMARY'); ?></h3>
    </header>

    <div>
    	<?php /* Backup status summary */ ?>
    	<?php echo $this->statusCell ?>

    	<?php /* Warnings */ ?>
    	<?php if ($this->countWarnings): ?>
    	<div>
    		<?php echo $this->detailsCell ?>
    	</div>
    	<hr/>
    	<?php endif; ?>

    	<?php /* Version */ ?>
    	<p class="ak_version">
    		<?php echo \JText::_('COM_AKEEBA'); ?> <?php echo AKEEBA_PRO ? 'Professional ' : 'Core'; ?> <?php echo AKEEBA_VERSION; ?> (<?php echo AKEEBA_DATE; ?>)
    	</p>

    	<?php /* Changelog */ ?>
    	<a href="#" id="btnchangelog" class="akeeba-btn--primary">CHANGELOG</a>

    	<div id="akeeba-changelog" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
            <div class="akeeba-renderer-fef">
                <div class="akeeba-panel--info">
                    <header class="akeeba-block-header">
                        <h3>
		                    <?php echo \JText::_('CHANGELOG'); ?>
                        </h3>
                    </header>
                    <div id="DialogBody">
		                <?php echo $this->formattedChangelog; ?>
                    </div>
                </div>
            </div>
    	</div>

    	<?php /* Donation CTA */ ?>
    	<?php if ( ! (AKEEBA_PRO)): ?>
    		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display: inline-block">
    			<input type="hidden" name="cmd" value="_s-xclick" />
    			<input type="hidden" name="hosted_button_id" value="10903325" />
    			<input type="submit" class="akeeba-btn--green" value="Donate via PayPal" />
    			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
    		</form>
    	<?php endif; ?>

    	<?php /* Reload update information */ ?>
    	<a href="index.php?option=com_akeeba&view=ControlPanel&task=reloadUpdateInformation" class="akeeba-btn--dark">
    		<?php echo \JText::_('COM_AKEEBA_CPANEL_MSG_RELOADUPDATE'); ?>
    	</a>
    </div>
</div>
