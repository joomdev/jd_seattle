<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

$data = $displayData;

JHtml::_('bootstrap.tooltip');
$metatitle = JHtml::tooltipText(JText::_($data->tip ? $data->tip : $data->title), JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN'), 0);
?>
<div class="js-stools-column-order hasTooltip cursor-pointer" data-order="<?php echo $data->order; ?>" data-direction="<?php echo strtoupper($data->direction); ?>" data-name="<?php echo JText::_($data->title); ?>" title="<?php echo $metatitle; ?>">
	<?php if (!empty($data->title)) : ?>
		<?php echo JText::_($data->title); ?>
	<?php endif; ?>
	<?php if ($data->order == $data->selected) : ?>
		<i class="sorting_<?php echo ($data->direction == 'asc') ? 'desc' : 'asc'; ?>"></i>
	<?php else: ?>
		<i class="sorting"></i>
	<?php endif; ?>
</div>
