<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if (!$this->helper->access->check('core.create', null, 'com_menus') && !$this->helper->access->check('core.edit', null, 'com_menus'))
{
	return;
}

$published = $this->state->get('filter.published');
$clientId  = $this->state->get('filter.client_id');
$menuType  = JFactory::getApplication()->getUserState('com_menus.items.menutype');
?>
<?php if (strlen($menuType) && $menuType != '*'): ?>
	<?php if ($published >= 0): ?>
		<div class="row-fluid">
		<table>
			<tr>
				<td colspan="3" class="padding-10">
					<label id="batch-choose-action-lbl" class="control-label" for="batch-menu-id">
						<?php echo JText::_('COM_MENUS_BATCH_MENU_LABEL'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td class="padding-10">
					<div id="batch-choose-action" class="combo control-group">
						<div class="controls">
							<select name="batch[menu_id]" id="batch-menu-id" style="width: 400px;">
								<option value=""><?php echo JText::_('JLIB_HTML_BATCH_NO_CATEGORY'); ?></option>
								<?php
								$opts     = array(
									'published' => $published,
									'checkacl'  => (int) $this->state->get('menutypeid'),
									'clientid'  => (int) $clientId,
								);
								echo JHtml::_('select.options', JHtml::_('menu.menuitems', $opts));
								?>
							</select>
						</div>
					</div>
				</td>
				<td class="padding-10">
					<div id="batch-copy-move" class="btn-group" data-toggle="buttons">
						<label for="batch_move_copy_c" class="btn btn-default">
							<input type="radio" id="batch_move_copy_c" name="batch[move_copy]" value="c"/>
							<span><?php echo JText::_('JLIB_HTML_BATCH_COPY') ?></span>
						</label>
						<label for="batch_move_copy_m" class="btn btn-default">
							<input type="radio" id="batch_move_copy_m" name="batch[move_copy]" value="m"/>
							<span><?php echo JText::_('JLIB_HTML_BATCH_MOVE') ?></span>
						</label>
					</div>
				</td>
				<td class="padding-10">
					<a class="btn hidden" type="button" onclick="document.getElementById('batch-menu-id').value='';"><?php echo JText::_('JCANCEL'); ?></a>
					<button class="btn btn-success" type="submit" onclick="Joomla.submitform('item.batch', this.form);">
						<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
					</button>
				</td>
			</tr>
		</table>
		</div>
	<?php else: ?>
		<p><?php echo JText::_('COM_MENUS_SELECT_MENU_FILTER_NOT_TRASHED'); ?></p>
	<?php endif; ?>
<?php else: ?>
	<div class="row-fluid"><p><?php echo JText::_('COM_MENUS_SELECT_MENU_FIRST'); ?></p></div>
<?php endif; ?>

