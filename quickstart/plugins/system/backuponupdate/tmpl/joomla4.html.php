<?php
defined('_JEXEC') or die();
/**
 * @package    AkeebaBackup
 * @subpackage backuponupdate
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3, or later
 *
 * @since      5.4.1
 *
 * This file contains the CSS for rendering the status (footer) icon for the Backup on Update plugin. The icon is only
 * rendered in the administrator backend of the site.
 *
 * You can override this file WITHOUT overwriting it. Copy this file into:
 *
 * administrator/templates/YOUR_TEMPLATE/html/plg_system_backuponupdate/default.html.php
 *
 * where YOUR_TEMPLATE is the folder of the administrator template you are using. Modify that copy. It will be loaded
 * instead of the file in plugins/system/backuponupdate.
 */

$token = urlencode(JFactory::getSession()->getToken());
$js = <<< JS
; // Work around broken third party Javascript

function akeeba_backup_on_update_toggle()
{
    window.jQuery.get('index.php?_akeeba_backup_on_update_toggle=$token', function() {
        location.reload(true);
    });
}


JS;

$document = JFactory::getApplication()->getDocument();

if (empty($document))
{
	$document = JFactory::getDocument();
}

if (empty($document))
{
	return;
}

$document->addScriptDeclaration($js);

?>
<div class="ml-auto">
	<ul class="nav text-center">

		<li class="nav-item">
			<a class="nav-link" href="javascript:akeeba_backup_on_update_toggle()" class="hasPopover"
			   title="<?php echo JText::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_POPOVER_CONTENT_' . ($params['active'] ? 'ACTIVE' : 'INACTIVE')) ?>"
			   data-title="<?php echo JText::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_POPOVER_TITLE') ?>"
			   data-content="<p><?php echo JText::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_POPOVER_CONTENT_' . ($params['active'] ? 'ACTIVE' : 'INACTIVE')) ?></p><p class='small'><?php echo JText::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_POPOVER_CONTENT_COMMON') ?></p>">
				<span class="fa fa-akeebastatus" aria-hidden="true"></span>
				<span class="sr-only">
					<?php echo JText::_('PLG_SYSTEM_BACKUPONUPDATE_LBL_' . ($params['active'] ? 'ACTIVE' : 'INACTIVE')) ?>
				</span>
				<span class="badge badge-pill badge-<?php echo $params['active'] ? 'success' : 'danger' ?>">&nbsp;</span>
			</a>
		</li>

	</ul>
</div>
