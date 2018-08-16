<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  \Akeeba\Backup\Admin\View\Manage\Html  $this */

// Make sure we only ever add this HTML and JS once per page
if (defined('AKEEBA_VIEW_JAVASCRIPT_HOWTORESTORE'))
{
	return;
}

define('AKEEBA_VIEW_JAVASCRIPT_HOWTORESTORE', 1);

$js = <<< JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
akeeba.System.documentReady(function(){
	setTimeout(function(){
		akeeba.System.howToRestoreModal = akeeba.Modal.open({
		inherit: '#akeeba-config-howtorestore-bubble',
		width: '80%'
	});
	}, 500);
});

JS;

$this->getContainer()->template->addJSInline($js);
?>

<div id="akeeba-config-howtorestore-bubble">
    <div class="akeeba-renderer-fef">
        <h4>
		    <?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_LEGEND'); ?>
        </h4>

        <p>
		    <?php echo \JText::sprintf('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_TEXT_PRO',
			    'https://www.akeebabackup.com/videos/1212-akeeba-backup-core/1618-abtc04-restore-site-new-server.html',
			    'index.php?option=com_akeeba&view=Transfer',
			    'https://www.akeebabackup.com/latest-kickstart-core.zip'
		    ); ?>
        </p>
        <div>
            <a href="#" class="akeeba-btn--primary" onclick="akeeba.System.howToRestoreModal.close(); document.getElementById('akeeba-config-howtorestore-bubble').style.display = 'none'">
                <span class="akion-close"></span>
			    <?php echo \JText::_('COM_AKEEBA_BUADMIN_BTN_REMINDME'); ?>
            </a>
            <a href="index.php?option=com_akeeba&view=Manage&task=hidemodal" class="akeeba-btn--green">
                <span class="akion-checkmark-circled"></span>
			    <?php echo \JText::_('COM_AKEEBA_BUADMIN_BTN_DONTSHOWTHISAGAIN'); ?>
            </a>
        </div>
    </div>
</div>
