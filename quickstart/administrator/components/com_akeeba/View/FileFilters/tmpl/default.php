<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

$ajaxUrl = addslashes('index.php?option=com_akeeba&view=FileFilters&task=ajax');
$loadingUrl = addslashes($this->container->template->parsePath('media://com_akeeba/icons/loading.gif'));
$this->json = addcslashes($this->json, "'");
$js = <<< JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
akeeba.System.documentReady(function() {
    akeeba.System.params.AjaxURL = '$ajaxUrl';
    akeeba.Fsfilters.loadingGif = '$loadingUrl';

	// Bootstrap the page display
	var data = eval({$this->json});
    akeeba.Fsfilters.render(data);
});

JS;

$this->getContainer()->template->addJSInline($js);

?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ErrorModal'); ?>

<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ProfileName'); ?>

<div class="akeeba-form--inline akeeba-panel--info">
	<div class="akeeba-form-group">
		<label>
            <?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_ROOTDIR'); ?>
        </label>
		<span><?php echo $this->root_select; ?></span>
    </div>
    <div class="akeeba-form-group--actions">
        <button class="akeeba-btn--red" onclick="akeeba.Fsfilters.nuke(); return false;">
			<span class="akion-ios-trash"></span>
			<?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_NUKEFILTERS'); ?>
		</button>

        <a class="akeeba-btn--grey" href="index.php?option=com_akeeba&view=FileFilters&task=tabular">
			<span class="akion-ios-list-outline"></span>
			<?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_VIEWALL'); ?>
		</a>
	</div>
</div>

<div id="ak_crumbs_container" class="akeeba-panel--100 akeeba-panel--information">
    <div>
        <ul id="ak_crumbs" class="akeeba-breadcrumb"></ul>
    </div>
</div>


<div id="ak_main_container" class="akeeba-container--50-50">
	<div>
        <div class="akeeba-panel--info">
            <header class="akeeba-block-header">
                <h3>
			        <?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_DIRS'); ?>
                </h3>
            </header>
            <div id="folders"></div>
        </div>
	</div>

	<div>
        <div class="akeeba-panel--info">
            <header class="akeeba-block-header">
                <h3>
			        <?php echo \JText::_('COM_AKEEBA_FILEFILTERS_LABEL_FILES'); ?>
                </h3>
            </header>
            <div id="files"></div>
        </div>
	</div>
</div>
