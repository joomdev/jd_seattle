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

/** @var  SellaciousViewShippingRules  $this */

$me        = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
$shippedBy = $this->helper->config->get('shipped_by');

foreach ($this->items as $i => $item)
{
	$canChange = $this->helper->access->check('shippingrule.edit.state');
	$canDelete = $this->helper->access->check('shippingrule.delete');
	$canEdit   = $this->helper->access->check('shippingrule.edit') ||
		($shippedBy == 'seller' && $item->created_by == $me->id && $this->helper->access->check('shippingrule.edit.own'));
	?>
	<tr role="row">
		<td class="nowrap text-center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange || $canDelete) ? '' : ' disabled="disabled"' ?>/>
				<span></span>
			</label>
		</td>
		<td class="nowrap text-center">
			<span class="btn-round"><?php
				echo JHtml::_('jgrid.published', $item->state, $i, 'shippingrules.', $canChange);?></span>
		</td>
		<td>
			<?php if ($canEdit): ?>
				<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=shippingrule.edit&id=' . $item->id); ?>">
					<?php echo $this->escape($item->title); ?></a>
			<?php else: ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
		</td>
		<td class="text-center hidden-phone">
			<span><?php echo (int)$item->id; ?></span>
		</td>
	</tr>
	<?php
}
