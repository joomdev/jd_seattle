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
 * Callback function for changing the active root in Filesystem Filters
 */
function akeeba_active_root_changed()
{
	var elRoot = document.getElementById('active_root');
	akeeba.Dbfilters.loadTab(elRoot.options[elRoot.selectedIndex].value);
}

akeeba.System.documentReady(function(){
    akeeba.System.params.AjaxURL = '$ajaxUrl';
	var data = JSON.parse('{$this->json}');
    akeeba.Dbfilters.renderTab(data);
});

JS;

$this->getContainer()->template->addJSInline($js);

?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ErrorModal'); ?>

<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ProfileName'); ?>

<div class="akeeba-form--inline akeeba-panel--info">
    <div class="akeeba-form-group">
		<label><?php echo \JText::_('COM_AKEEBA_DBFILTER_LABEL_ROOTDIR'); ?></label>
		<span><?php echo $this->root_select; ?></span>
	</div>
	<div id="addnewfilter" class="akeeba-form-group--actions">
		<label>
            <?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_ADDNEWFILTER'); ?>
        </label>

		<button class="akeeba-btn--grey" onclick="akeeba.Dbfilters.addNew('tables'); return false;">
			<?php echo \JText::_('COM_AKEEBA_DBFILTER_TYPE_TABLES'); ?>
		</button>

        <button class="akeeba-btn--grey" onclick="akeeba.Dbfilters.addNew('tabledata'); return false;">
			<?php echo \JText::_('COM_AKEEBA_DBFILTER_TYPE_TABLEDATA'); ?>
		</button>
	</div>
</div>


<div class="akeeba-panel--primary">
	<div id="ak_list_container">
		<table id="ak_list_table" class="akeeba-table--striped">
			<thead>
				<tr>
					<td width="250px"><?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_TYPE'); ?></td>
					<td><?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_FILTERITEM'); ?></td>
				</tr>
			</thead>
			<tbody id="ak_list_contents">
			</tbody>
		</table>
	</div>
</div>
