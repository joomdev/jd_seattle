<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

$this->loadHelper('escape');

$escapedPassword = addslashes($this->password);
$escapedJuriBase = addslashes(JUri::base());
$js = <<< JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
akeeba.Restore.password = '$escapedPassword';
akeeba.Restore.ajaxURL = '{$escapedJuriBase}/components/com_akeeba/restore.php';
akeeba.Restore.mainURL = '{$escapedJuriBase}/index.php';

akeeba.System.documentReady(function(){
    akeeba.Restore.pingRestoration();

    akeeba.System.addEventListener(document.getElementById('restoration-runinstaller'), 'click', akeeba.Restore.runInstaller);
    akeeba.System.addEventListener(document.getElementById('restoration-finalize'), 'click', akeeba.Restore.finalize);
});


JS;

$this->getContainer()->template->addJSInline($js);

?>
<div class="akeeba-block--info">
	<p>
		<?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_DONOTCLOSE'); ?>
    </p>
</div>


<div id="restoration-progress">
	<h4><?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_INPROGRESS'); ?></h4>

	<table class="akeeba-table--striped">
		<tr>
			<td width="25%">
				<?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_BYTESREAD'); ?>
			</td>
			<td>
				<span id="extbytesin"></span>
			</td>
		</tr>
		<tr>
			<td width="25%">
				<?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_BYTESEXTRACTED'); ?>
			</td>
			<td>
				<span id="extbytesout"></span>
			</td>
		</tr>
		<tr>
			<td width="25%">
				<?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_FILESEXTRACTED'); ?>
			</td>
			<td>
				<span id="extfiles"></span>
			</td>
		</tr>
	</table>

	<div id="response-timer">
		<div class="color-overlay"></div>
		<div class="text"></div>
	</div>
</div>

<div id="restoration-error" style="display:none">
	<div class="akeeba-block--failure">
		<h4>
            <?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_FAILED'); ?>
        </h4>
		<div id="errorframe">
			<p><?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_FAILED_INFO'); ?></p>
			<p id="backup-error-message">
			</p>
		</div>
	</div>
</div>

<div id="restoration-extract-ok" style="display:none">
	<div class="akeeba-block--success">
		<h4>
            <?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_SUCCESS'); ?>
        </h4>

        <p>
			<?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_SUCCESS_INFO2'); ?>
		</p>
		<p>
			<?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_SUCCESS_INFO2B'); ?>
		</p>
	</div>

    <p>
		<button class="akeeba-btn--primary" id="restoration-runinstaller" onclick="return false;">
			<span class="akion-android-share"></span>
			<?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_RUNINSTALLER'); ?>
		</button>
	</p>
	<p>
		<button class="akeeba-btn--green" id="restoration-finalize" style="display: none" onclick="return false;">
			<span class="akion-android-exit"></span>
			<?php echo \JText::_('COM_AKEEBA_RESTORE_LABEL_FINALIZE'); ?>
		</button>
	</p>
</div>
