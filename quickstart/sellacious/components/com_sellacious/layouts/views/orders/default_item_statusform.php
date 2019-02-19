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

/** @var  SellaciousViewOrders $this */
/** @var  stdClass $tplData */
$oi = $tplData;
?>
<table class="status-form-table w100p">
	<thead>
		<tr>
			<td style="width:30%;"><?php echo JText::_('COM_SELLACIOUS_ORDER_NEW_STATUS'); ?> </td>
			<td style="width:70%;">
				<input type="hidden" name="jform[order_id]" value="<?php echo $oi->order_id ?>"/>
				<input type="hidden" name="jform[item_uid]" value="<?php echo $oi->item_uid ?>"/>
				<select name="jform[status]" title="" class="w100p oi-status-list">
					<option value=""><?php echo JText::_('COM_SELLACIOUS_ORDER_STATUS_KEEP_CURRENT') ?></option>
					<?php
					$params = array(
						'option.key'  => 'id',
						'option.text' => 'title',
					);
					echo JHtml::_('select.options', $oi->statuses, $params); ?>
				</select>
			</td>
		</tr>
	</thead>
	<tbody>
	<!-- Ajax loaded content here -->
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2" class="text-right">
				<button type="button" class="btn btn-default btn-oi-status-close">
					<i class="fa fa-times"></i> <?php echo JText::_('COM_SELLACIOUS_ORDER_CLOSE'); ?>
				</button>
				<button type="button" class="btn btn-primary btn-oi-status-save">
					<i class="fa fa-save"></i> <?php echo JText::_('COM_SELLACIOUS_ORDER_SAVE'); ?>
				</button>
				<?php echo JHtml::_('form.token'); ?>
			</td>
		</tr>
	</tfoot>
</table>
