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

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$listSelect = $this->state->get('list.columns');
?>
<tr>
	<th class="center nowrap hidden-phone" style="width: 10px !important;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this, 'cb');" />
			<span></span>
		</label>
	</th>

	<?php if (is_array($listSelect) && in_array('lang-key', $listSelect)): ?>
	<th class="center" data-col="lang-key">
		<?php echo JText::_('COM_LANGUAGES_HEADING_LANG_CONSTANT'); ?>
	</th>
	<?php endif; ?>

	<?php if (!$listSelect || in_array('lang-string', $listSelect)): ?>
		<?php $srLang = JLanguageHelper::getMetadata('en-GB'); ?>
		<th class="center" data-col="lang-string">
			<?php echo JText::sprintf('COM_LANGUAGES_HEADING_LANG_WITH_NAME', $srLang['name'], $srLang['tag']); ?>
		</th>
	<?php endif; ?>

	<?php if (!$listSelect || in_array('lang-override', $listSelect)): ?>
		<?php if ($trCode = $this->state->get('list.language')): ?>
		<?php $trLang = JLanguageHelper::getMetadata($trCode); ?>
		<th class="center" data-col="lang-override">
			<?php echo JText::sprintf('COM_LANGUAGES_HEADING_LANG_WITH_NAME', $trLang['name'] ?: $trLang['nativeName'], $trLang['tag']); ?>
		</th>
		<th class="center" data-col="lang-override" style="width: 10px !important;">
			<?php echo JText::_('COM_LANGUAGES_STRINGS_HEADING_LANG_HTML'); ?>
		</th>
		<?php endif; ?>
	<?php endif; ?>
</tr>
