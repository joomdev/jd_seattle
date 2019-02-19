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

/** @var  SellaciousViewPaymentMethods  $this */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$orderFull = $this->escape($this->state->get('list.fullordering'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc') || strtolower($orderFull) == 'a.ordering asc';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_sellacious&task=' . $this->view_list . '.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', $this->view_item . 'List', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$handlers  = $this->helper->paymentMethod->getHandlers();

foreach ($this->items as $i => $item)
{
	$active    = array_key_exists($item->handler, $handlers);
	$canEdit   = $this->helper->access->check('paymentmethod.edit', $item->id);
	$canChange = $this->helper->access->check('paymentmethod.edit.state', $item->id);
	?>
	<tr role="row">
		<td class="order nowrap center hidden-phone">
			<?php
			$iconClass = '';

			if (!$canChange)
			{
				$iconClass = ' inactive';
			}
			elseif (!$saveOrder)
			{
				$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
			}
			?>
			<span class="sortable-handler <?php echo $iconClass ?>">
				<span class="icon-menu"></span>
			</span>
			<?php if ($canChange && $saveOrder) : ?>
				<input type="text" style="display:none" name="order[]" size="5"
				       value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " title=""/>
			<?php endif; ?>
		</td>
		<td class="nowrap center hidden-phone">
			<label><input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange) && $active ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>
		<td class="nowrap center" style="width:120px;">
			<?php if ($active): ?>
				<span class="btn-round"><?php echo JHtml::_('jgrid.published', $item->state, $i, 'paymentmethods.', $canChange); ?></span>
			<?php else: ?>
			<span class="btn-round"><a class="btn btn-default btn-circle btn-xs disabled">&nbsp;<i class="fa fa-lock"></i></a></span>
			<?php endif; ?>
		</td>
		<td class="left">
			<?php if ($canEdit &&  $active): ?>
				<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=paymentmethod.edit&id=' . $item->id); ?>">
					<?php echo $this->escape($item->title); ?></a>
			<?php else: ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
		</td>
		<td class="text-center">
			<?php echo $this->escape($item->handler); ?>
		</td>
		<td class="text-center nowrap" style="width: 100px;">
			<?php $contexts = (array) json_decode($item->contexts); ?>
			<div class="btn-group">
				<a class="btn btn-xs btn-<?php echo in_array('cart', $contexts) ? 'primary' : 'default disabled' ?>"><?php echo JText::_('COM_SELLACIOUS_PAYMENTMETHOD_BTN_CART'); ?></a>
				<a class="btn btn-xs btn-<?php echo in_array('addfund', $contexts) ? 'primary' : 'default disabled' ?>"><?php echo JText::_('COM_SELLACIOUS_PAYMENTMETHOD_BTN_ADDFUND'); ?></a>
			</div>
		</td>
		<td class="text-center">
			<?php
			if ($item->success_status)
			{
				echo '<label class="label label-success">' . JText::_('COM_SELLACIOUS_PAYMENTMETHOD_STATUS_APPROVED_LABEL') . '</label>';
			}
			else
			{
				echo '<label class="label label-warning">' . JText::_('COM_SELLACIOUS_PAYMENTMETHOD_STATUS_APPROVAL_LABEL') . '</label>';
			}
			?>
		</td>
		<td class="text-center">
			<?php echo sprintf('%.3f%%', $item->percent_fee); ?>
		</td>
		<td class="text-center">
			<?php $g_currency = $this->helper->currency->getGlobal('code_3'); ?>
			<?php echo $this->helper->currency->display($item->flat_fee, $g_currency, null); ?>
		</td>

		<td class="center hidden-phone">
			<?php echo (int)$item->id; ?>
		</td>
	</tr>
	<?php
}
