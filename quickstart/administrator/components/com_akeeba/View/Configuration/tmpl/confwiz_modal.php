<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var \FOF30\View\DataView\Html $this */

// Make sure we only ever add this HTML and JS once per page
if (defined('AKEEBA_VIEW_JAVASCRIPT_CONFWIZ_MODAL'))
{
	return;
}

define('AKEEBA_VIEW_JAVASCRIPT_CONFWIZ_MODAL', 1);

$js = <<< JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
function akeeba_confwiz_modal_open()
{
	akeeba.System.configurationWizardModal = akeeba.Modal.open({
		inherit: '#akeeba-config-confwiz-bubble',
		width: '80%'
	});
};

akeeba.System.documentReady(function(){
	setTimeout('akeeba_confwiz_modal_open();', 500);
});

JS;

$this->getContainer()->template->addJSInline($js);
?>

<div id="akeeba-config-confwiz-bubble" class="modal fade" role="dialog"
     aria-labelledby="DialogLabel" aria-hidden="true" style="display: none;">
    <div class="akeeba-renderer-fef">
        <h4>
		    <?php echo \JText::_('COM_AKEEBA_CONFIG_HEADER_CONFWIZ'); ?>
        </h4>
        <div>
            <p>
			    <?php echo \JText::_('COM_AKEEBA_CONFIG_LBL_CONFWIZ_INTRO'); ?>
            </p>
            <p>
                <a href="index.php?option=com_akeeba&view=ConfigurationWizard" class="akeeba-btn--green akeeba-btn--big">
                    <span class="akion-flash"></span>
				    <?php echo \JText::_('COM_AKEEBA_CONFWIZ'); ?>
                </a>
            </p>
            <p>
			    <?php echo \JText::_('COM_AKEEBA_CONFIG_LBL_CONFWIZ_AFTER'); ?>
            </p>
        </div>
        <div>
            <a href="#" class="akeeba-btn--ghost akeeba-btn--small" onclick="akeeba.System.configurationWizardModal.close();">
                <span class="akion-close"></span>
			    <?php echo \JText::_('JCANCEL'); ?>
            </a>
        </div>
    </div>
</div>
