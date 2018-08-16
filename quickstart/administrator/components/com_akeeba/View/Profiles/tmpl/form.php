<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

?>
    <form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form--horizontal--with-hidden akeeba-panel--information">
        <div class="akeeba-form-group">
        </div>

        <div class="akeeba-form-group">
            <label for="description">
				<?php echo \JHtml::_('tooltip', JText::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION_TOOLTIP'), '', '', JText::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION')); ?>
            </label>
            <input type="text" name="description" class="span6" id="description" value="<?php echo $this->escape($this->item->description); ?>" />
        </div>

        <div class="akeeba-hidden-fields-container">
            <input type="hidden" name="option" value="com_akeeba" />
            <input type="hidden" name="view" value="Profiles" />
            <input type="hidden" name="boxchecked" id="boxchecked" value="0" />
            <input type="hidden" name="task" id="task" value="save" />
            <input type="hidden" name="id" id="id" value="<?php echo (int)$this->item->id; ?>" />
            <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1" />
        </div>
    </form>
</div>
