<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

/** @var  \Akeeba\Backup\Admin\View\Log\Html  $this */

?>
<?php if(isset($this->logs) && count($this->logs)): ?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-form--inline">
    <div class="akeeba-form-group">
        <label for="tag"><?php echo \JText::_('COM_AKEEBA_LOG_CHOOSE_FILE_TITLE'); ?></label>
        <?php echo \JHtml::_('select.genericlist', $this->logs, 'tag', 'onchange="submitform();" class="advancedSelect"', 'value', 'text', $this->tag); ?>
    </div>

	<?php if (!empty($this->tag)): ?>
        <div class="akeeba-form-group--actions">
            <a class="akeeba-btn--primary" href="<?php echo $this->escape(JUri::base()); ?>index.php?option=com_akeeba&view=Log&task=download&tag=<?php echo $this->escape($this->tag); ?>">
                <span class="akion-ios-download"></span>
		        <?php echo \JText::_('COM_AKEEBA_LOG_LABEL_DOWNLOAD'); ?>
            </a>
        </div>
	<?php endif; ?>

    <div class="akeeba-hidden-fields-container">
        <input name="option" value="com_akeeba" type="hidden" />
        <input name="view" value="Log" type="hidden" />
        <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1" />
    </div>

</form>
<?php endif; ?>

<?php if (!empty($this->tag)): ?>
    <?php if ($this->logTooBig):?>
        <div class="akeeba-block--warning">
            <p>
                <?php echo JText::sprintf('COM_AKEEBA_LOG_SIZE_WARNING', number_format($this->logSize / (1024 * 1024), 2))?>
            </p>
            <a class="akeeba-btn--dark" id="showlog" href="#">
                <?php echo JText::_('COM_AKEEBA_LOG_SHOW_LOG')?>
            </a>
        </div>
    <?php endif; ?>

    <div id="iframe-holder" class="akeeba-panel--primary" style="display: <?php echo $this->logTooBig ? 'none' : 'block' ?>;">
		<?php if (!$this->logTooBig):?>
            <iframe
                src="index.php?option=com_akeeba&view=Log&task=iframe&format=raw&tag=<?php echo urlencode($this->tag); ?>"
                width="99%" height="400px">
            </iframe>
		<?php endif;?>
    </div>
<?php endif; ?>

<?php if ( ! (isset($this->logs) && count($this->logs))): ?>
<div class="akeeba-block--failure">
	<?php echo JText::_('COM_AKEEBA_LOG_NONE_FOUND') ?>
</div>
<?php endif; ?>
