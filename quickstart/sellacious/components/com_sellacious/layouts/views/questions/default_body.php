<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewQuestions  $this */
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
$me        = JFactory::getUser();

foreach ($this->items as $i => $item)
{
	$canEdit    = $this->helper->access->check('question.edit') ||
		($this->helper->access->check('question.edit.own') && $item->seller_uid == $me->id);

	$site_url  = 'index.php?option=com_sellacious&view=product&p=' . $item->product_code;

	// Site route will be available if we could use JRoute::link, use 'isset' to test if we have it.
	if (is_callable(array('JRoute', 'link'))):
		// @fixme: B/C against J3.9
		// $siteRoute = call_user_func_array(array('JRoute', 'link'), array('site', $site_url));
		$site_url  = trim(JUri::root(), '/') . '/' . $site_url;
	else:
		$site_url  = trim(JUri::root(), '/') . '/' . $site_url;
	endif;

	$productTitle = $item->product_title;

	if($item->variant_title)
	{
		$productTitle .= ' -' .$item->variant_title;
	}
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>
		<td class="nowrap center">

			<?php
				if ($item->state):
					echo '<span class="text-success">' . JText::_('COM_SELLACIOUS_QUESTIONS_STATE_REPLIED') . '</span>';
				else:
					echo '<span class="text-danger">' . JText::_('COM_SELLACIOUS_QUESTIONS_STATE_UNREPLIED') . '</span>';
				endif; ?>
			</span>
		</td>
		<td class="left">
			<?php if ($canEdit) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=question.edit&id=' . $item->id); ?>">
					<?php echo $this->escape($item->question); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->question); ?>
			<?php endif; ?>
		</td>
		<td class="left">
			<?php echo $this->escape($productTitle); ?> (<?php echo $item->product_code; ?>)
			<span class="txt-color-red">&nbsp;
				<a target="_blank" class="hasTooltip" data-placement="right" title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_LINK_FRONTEND_TIP'); ?>"
				   href="<?php echo isset($siteRoute) ? $siteRoute : $site_url; ?>"><i class="fa fa-external-link-square"></i></a>&nbsp;
			</span>
		</td>

		<td class="center nowrap">
			<?php echo $item->created == '0000-00-00 00:00:00' ? JText::_('JNONE') : JHtml::_('date', $item->created, 'M d, Y') ?>
		</td>
		<td class="center nowrap">
			<?php echo $item->replied == '0000-00-00 00:00:00' ? JText::_('JNONE') : JHtml::_('date', $item->replied, 'M d, Y') ?>
		</td>
		<td class="center hidden-phone">
			<span><?php echo (int) $item->id; ?></span>
		</td>
	</tr>
	<?php
}
