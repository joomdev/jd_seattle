<?php

/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

$doc = JFactory::getDocument();
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));
?>

<form action="<?php echo JRoute::_('index.php?option=com_spsimpleportfolio&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
  <div class="form-horizontal">
    <div class="row-fluid">
      <div class="span9">
        <?php echo $this->form->renderFieldset('basic'); ?>
      </div>

      <div class="span3">
        <fieldset class="form-vertical">
          <?php echo $this->form->renderFieldset('sidebar'); ?>
        </fieldset>
      </div>
    </div>
  </div>

  <input type="hidden" name="task" value="item.edit" />
  <?php echo JHtml::_('form.token'); ?>
</form>
