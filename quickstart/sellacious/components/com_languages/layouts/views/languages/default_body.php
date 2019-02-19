<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('script', 'com_languages/view.languages.js', array('version' => S_VERSION_CORE, 'relative' => true));

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'a.ordering';

$langDefault = JFactory::getLanguage()->getDefault();

foreach ($this->items as $i => $item)
{
	$canEdit   = $this->helper->access->check('core.edit', null, 'com_languages') && $item->id > 0;
	$canDelete = $this->helper->access->check('core.delete', null, 'com_languages') && $item->id > 0;
	$tUrl      = JRoute::_('index.php?option=com_languages&view=strings&language=' . $item->lang_code);
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="nowrap text-center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canDelete) ? '' : ' disabled="disabled"' ?>/>
				<span></span>
			</label>
		</td>
		<td class="text-center">
			<?php if ($item->state == -1): ?>
				<button type="button" class="btn btn-primary btn-xs btn-install w100p"
						data-url="<?php echo htmlspecialchars($item->params['install_url']); ?>"
						data-lang="<?php echo $this->escape($item->lang_code); ?>"><?php
					echo JText::_('COM_LANGUAGES_LANGUAGES_INSTALL_BUTTON'); ?></button>
			<?php elseif (isset($item->params['install_url']) && (!$item->site || !$item->administrator || !$item->sellacious)): ?>
				<button type="button" class="btn btn-warning btn-xs btn-install w100p"
						data-url="<?php echo htmlspecialchars($item->params['install_url']); ?>"
						data-lang="<?php echo $this->escape($item->lang_code); ?>"><?php
					echo JText::_('COM_LANGUAGES_LANGUAGES_REINSTALL_BUTTON'); ?></button>
			<?php else: ?>
				<a href="<?php echo $tUrl ?>" class="btn btn-success btn-xs w100p"><i class="fa fa-language"></i> <?php
					echo JText::_('COM_LANGUAGES_LANGUAGES_MANAGE_TRANSLATION_BUTTON'); ?></a>
			<?php endif; ?>
		</td>
		<td class="center nowrap hidden" style="width: 65px;">
			<div class="btn-group" style="width: 60px; float: none;">
				<a type="button" class="hasTooltip btn btn-xs <?php
					echo $item->site ? 'btn-primary active' : 'btn-default' ?>" title="<?php echo JText::_('JSITE') ?>">F</a>
				<a type="button" class="hasTooltip btn btn-xs <?php
					echo $item->administrator ? 'btn-primary active' : 'btn-default' ?>" title="<?php echo JText::_('JADMINISTRATOR') ?>">A</a>
				<a type="button" class="hasTooltip btn btn-xs <?php
					echo $item->sellacious ? 'btn-primary active' : 'btn-default' ?>" title="<?php echo JText::_('APP_SELLACIOUS') ?>">S</a>
			</div>
		</td>
		<td>
			<span class="editlinktip hasTooltip"
				  title="<?php echo JHtml::_('tooltipText', JText::_('JGLOBAL_EDIT_ITEM'), $item->title, 0); ?>">
			<?php if ($canEdit) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_languages&task=language.edit&id=' . (int) $item->id); ?>"><?php
					echo $this->escape($item->title); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
			</span>
		</td>
		<td class="hidden-phone hidden-tablet">
			<?php echo $this->escape($item->title_native); ?>
		</td>
		<td class="text-center">
			<?php echo $this->escape($item->lang_code); ?>
		</td>
		<td class="text-center">
			<?php echo $this->escape($item->sef); ?>
		</td>
		<td class="hidden-phone text-center">
			<?php if ($item->image) : ?>
				<?php echo JHtml::_('image', 'mod_languages/' . $item->image . '.gif', $item->image, null, true); ?>&nbsp;<?php
				echo $this->escape($item->image); ?>
			<?php else : ?>
				<?php echo JText::_('JNONE'); ?>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}
