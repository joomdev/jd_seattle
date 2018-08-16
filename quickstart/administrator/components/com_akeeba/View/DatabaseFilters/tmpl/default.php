<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$ajaxUrl = addslashes('index.php?option=com_akeeba&view=DatabaseFilters&task=ajax');
$this->json = addcslashes($this->json, "'\\");
$js = <<< JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
/**
 * Callback function for changing the active root in Database Table filters
 */
function akeeba_active_root_changed()
{
	var elRoot = document.getElementById('active_root');
	var data = {
		'root': elRoot.options[elRoot.selectedIndex].value
	};
    akeeba.Dbfilters.load(data);
}

akeeba.System.documentReady(function(){
    akeeba.System.params.AjaxURL = '$ajaxUrl';
	var data = JSON.parse('{$this->json}');
    akeeba.Dbfilters.render(data);
});

JS;

$this->getContainer()->template->addJSInline($js);

?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ErrorModal'); ?>

<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ProfileName'); ?>

<div class="akeeba-form--inline akeeba-panel--info">
    <div class="akeeba-form-group">
        <label><?php echo \JText::_('COM_AKEEBA_DBFILTER_LABEL_ROOTDIR'); ?></label>
	    <?php echo $this->root_select; ?>
    </div>
    <div class="akeeba-form-group--actions">
        <button class="akeeba-btn--green" onclick="akeeba.Dbfilters.excludeNonCMS(); return false;">
            <span class="akion-ios-flag"></span>
			<?php echo \JText::_('COM_AKEEBA_DBFILTER_LABEL_EXCLUDENONCORE'); ?>
        </button>
        <button class="akeeba-btn--red" onclick="akeeba.Dbfilters.nuke(); return false;">
            <span class="akion-ios-loop-strong"></span>
			<?php echo \JText::_('COM_AKEEBA_DBFILTER_LABEL_NUKEFILTERS'); ?>
        </button>
    </div>
</div>

<div id="ak_main_container" class="akeeba-container--100">
</div>

<div class="akeeba-panel--info">
    <header class="akeeba-block-header">
        <h3>
            <?php echo \JText::_('COM_AKEEBA_DBFILTER_LABEL_TABLES'); ?>
        </h3>
    </header>
	<div id="tables"></div>
</div>
