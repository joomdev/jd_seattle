<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  \Akeeba\Backup\Admin\View\Manage\Html  $this */

?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-form--horizontal">
	<div class="akeeba-form-group">
		<label for="description">
			<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_DESCRIPTION'); ?>
		</label>
        <input type="text" name="description" id="description" maxlength="255" size="50"
               value="<?php echo $this->escape($this->record['description']); ?>" />
	</div>

	<div class="akeeba-form-group">
		<label for="comment">
			<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_COMMENT'); ?>
		</label>
		<?php echo JEditor::getInstance($this->container->platform->getConfig()->get('editor', 'tinymce'))->display('comment',  $this->record['comment'], '100%', '400', '60', '20', array()); ?>
	</div>

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="option" value="com_akeeba" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="view" value="Manage" />
        <input type="hidden" name="id" value="<?php echo (int)$this->record['id']; ?>" />
        <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1" />
    </div>

</form>
