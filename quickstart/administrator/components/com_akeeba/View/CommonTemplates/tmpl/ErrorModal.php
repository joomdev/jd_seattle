<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();
?>
<?php /* Error modal */ ?>
<div id="errorDialog" tabindex="-1" role="dialog" aria-labelledby="errorDialogLabel" aria-hidden="true"
     style="display:none;">
    <div class="akeeba-renderer-fef">
        <h4 id="errorDialogLabel">
			<?php echo \JText::_('COM_AKEEBA_CONFIG_UI_AJAXERRORDLG_TITLE'); ?>
        </h4>

        <p>
			<?php echo \JText::_('COM_AKEEBA_CONFIG_UI_AJAXERRORDLG_TEXT'); ?>
        </p>
        <pre id="errorDialogPre">
    </div>
</div>
