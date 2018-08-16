<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$ajaxUrl = addslashes(JUri::base().'index.php?option=com_akeeba&view=RegExFileFilters&task=ajax');
$loadingUrl = addslashes($this->container->template->parsePath('media://com_akeeba/icons/loading.gif'));
$this->json = addcslashes($this->json, "'\\");
$js = <<< JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
akeeba.System.documentReady(function(){
    akeeba.System.params.AjaxURL = '$ajaxUrl';
	var data = JSON.parse('{$this->json}');
    akeeba.Regexfsfilters.render(data);
});

JS;

$this->getContainer()->template->addJSInline($js);

?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ErrorModal'); ?>

<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ProfileName'); ?>

<div class="akeeba-panel--information">
    <div class="akeeba-form-section">
        <div class="akeeba-form--inline">
            <label><?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_ROOTDIR'); ?></label>
            <span id="ak_roots_container_tab">
		<?php echo $this->root_select; ?>
	    </span>
        </div>
    </div>
</div>

<div class="akeeba-container--primary">
	<div id="ak_list_container">
        <table id="table-container" class="akeeba-table--striped--dynamic-line-editor">
			<thead>
				<tr>
					<th width="120px">&nbsp;</th>
					<th width="250px"><?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_TYPE'); ?></th>
					<th><?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_FILTERITEM'); ?></th>
				</tr>
			</thead>
			<tbody id="ak_list_contents" class="table-container">
			</tbody>
		</table>
	</div>
</div>
