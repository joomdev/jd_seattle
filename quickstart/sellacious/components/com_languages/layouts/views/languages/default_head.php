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

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>
<tr>
	<th style="width: 10px;" class="text-center">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this, 'cb');" />
			<span></span>
		</label>
	</th>
	<th width="1%" class="nowrap center">
		<?php echo JText::_('COM_LANGUAGES_INSTALL_HEADING_ACTION') ?>
	</th>
	<th width="1%" class="nowrap center hidden">
	</th>
	<th class="title nowrap">
		<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'title', $listDirn, $listOrder); ?>
	</th>
	<th class="title nowrap hidden-phone hidden-tablet">
		<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_TITLE_NATIVE', 'title_native', $listDirn, $listOrder); ?>
	</th>
	<th width="1%" class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_TAG', 'lang_code', $listDirn, $listOrder); ?>
	</th>
	<th width="1%" class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_CODE', 'sef', $listDirn, $listOrder); ?>
	</th>
	<th width="8%" class="nowrap hidden-phone text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_IMAGE', 'image', $listDirn, $listOrder); ?>
	</th>
</tr>
