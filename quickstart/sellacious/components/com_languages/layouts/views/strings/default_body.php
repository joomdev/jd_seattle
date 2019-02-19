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

$i       = 1;
$canEdit = $this->helper->access->check('core.edit', null, 'com_languages');

$listSelect = $this->state->get('list.columns');

foreach ($this->items as $item)
{
	$i++;
	$tip = $item->orig_text ?: '<span style="color: yellow">(Empty)</span>';
	?>
	<tr class="row<?php echo $i % 2; ?> overriderrow" id="overriderrow<?php echo $i; ?>">
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo $canEdit ? '' : ' disabled="disabled"' ?>/>
				<span></span>
			</label>
			<input type="hidden" class="override-input" name="override[<?php echo $item->id ?>]"
				   value="<?php echo $this->escape($item->override); ?>" data-id="<?php echo $item->id ?>"/>
		</td>

	<?php if (is_array($listSelect) && in_array('lang-key', $listSelect)): ?>
		<td data-col="lang-key" class="lang-key">
			<label title="<?php echo $this->escape($tip); ?>" class="hasTooltip w100p">
				<input value="<?php echo $this->escape($item->lang_constant); ?>" class="w100p" disabled/></label>
		</td>
	<?php endif; ?>

	<?php if (!$listSelect || in_array('lang-string', $listSelect)): ?>
		<td data-col="lang-string" class="lang-string">
			<label class="w100p">
				<input value="<?php echo $this->escape($item->orig_text); ?>" class="w100p" disabled/></label>
		</td>
	<?php endif; ?>

	<?php if (!$listSelect || in_array('lang-override', $listSelect)): ?>
		<?php if ($this->state->get('list.language')): ?>
			<td data-col="lang-override" class="lang-editor" title="<?php
				echo JText::_('COM_LANGUAGES_STRINGS_LANG_EDITOR_CLICK_TIP'); ?>">
			</td>
			<td data-col="lang-override">
			<span class="input-group">
				<span class="onoffswitch">
					<input type="checkbox" class="lang-html onoffswitch-checkbox" id="lang-html-<?php echo $i ?>">
					<label class="onoffswitch-label" for="lang-html-<?php echo $i ?>">
						<span class="onoffswitch-inner" data-swchon-text=" Yes " data-swchoff-text=" No "></span>
						<span class="onoffswitch-switch"></span>
					</label>
				</span>
			</span>
			</td>
		<?php endif; ?>
	<?php endif; ?>

	</tr>
	<?php
}
