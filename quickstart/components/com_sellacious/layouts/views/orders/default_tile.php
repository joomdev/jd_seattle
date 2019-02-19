<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewOrders  $this */
/** @var  stdClass  $tplData */
$order = $tplData;

$c_currency = $this->helper->currency->current('code_3');
$paid       = $this->helper->order->isPaid($order->id);
?>
<div class="w100p tile-box toggle-box">
	<div class="tile-head toggle-element">
		<table class="w100p">
			<tr>
				<?php $o_url = JRoute::_(sprintf('index.php?option=com_sellacious&view=order&id=%d', $order->id)); ?>
				<td class="order-number"><?php echo $this->escape($order->order_number); ?></td>
				<td class="hidden-phone">
					<div class="order-item-csv">
					<?php
					$titles = array();
					$oi     = reset($order->items);

					if (is_object($oi))
					{
						echo trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ');
					}
					?>
					</div>
				</td>
				<td class="order-item-count hidden-phone" style="width:60px;"><?php
					echo JText::plural('COM_SELLACIOUS_ORDERS_PREFIX_TOTAL_ITEMS_N', count($order->items)); ?></td>
				<td style="width:160px;">
					<a class="pull-right btn-toggle"><i class="fa fa-caret-down fa-lg"></i> </a>
					<span class="order-total"><span><?php
						echo $this->helper->currency->display($order->grand_total, $order->currency, $c_currency, true);
					?></span>&nbsp; <i class="fa order-<?php echo $paid ? 'paid fa-check' : 'not-paid fa-times' ?>"> </i></span>
				</td>
			</tr>
		</table>
	</div>
	<div class="tile-head toggle-element hidden">
		<a href="<?php echo $o_url ?>"><button type="button" class="btn btn-primary btn-lg order-number active">
			<?php echo $this->escape($order->order_number); ?></button></a>
		<a class="pull-right btn-toggle"><i class="fa fa-caret-up fa-lg"></i> </a>
	</div>
	<div class="tile-body toggle-element hidden">
	<?php
	if (!empty($order->items))
	{
		?>
		<table class="order-items w100p">
			<tbody>
			<?php
			foreach ($order->items as $oi)
			{
				$code   = $this->helper->product->getCode($oi->product_id, $oi->variant_id, $oi->seller_uid);
				$p_url  = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);
				$title  = trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ');
				$status = $this->helper->order->getStatus($oi->order_id, $oi->item_uid);
				$images = $this->helper->product->getImages($oi->product_id, $oi->variant_id);
				?>
				<tr>
					<td style="width:100px; max-width: 100px;">
						<a href="<?php echo $p_url ?>">
						<img src="<?php echo reset($images) ?>" alt="<?php echo $title ?>"></a>
					</td>
					<td class="v-top">
						<a href="<?php echo $p_url ?>" class="dark-link"><?php echo $this->escape($title) ?></a><br>
						<?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N', $oi->quantity); ?><br>
						<?php echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SELLER', $oi->seller_company); ?>
					</td>
					<td class="text-center">
						<?php
						if ($oi->return_available)
						{
							?><a href="#return-form-<?php echo $oi->id ?>" role="button" data-toggle="modal"
								class="btn btn-default btn-small btn-return-order"><i class="fa fa-undo"></i>
							<span class="hidden-phone"><?php echo JText::_('COM_SELLACIOUS_ORDER_PLACE_RETURN'); ?></span></a><br><?php
						}

						if ($oi->exchange_available)
						{
							?><a href="#exchange-form-<?php echo $oi->id ?>" role="button" data-toggle="modal"
								class="btn btn-default btn-small btn-exchange-order"><i class="fa fa-exchange"></i>
							<span class="hidden-phone"><?php echo JText::_('COM_SELLACIOUS_ORDER_PLACE_EXCHANGE'); ?></a><?php
						}
						?>
					</td>
					<td class="text-right nowrap v-top" style="max-width: 170px;">
						<span class="item-total"><?php
							echo $this->helper->currency->display($oi->sub_total + $oi->shipping_amount, $order->currency, $c_currency, true); ?></span>
						<br/>
						<span class="item-status">
						<?php
						if ($status->s_title)
						{
							$status_dt = JHtml::_('date', $status->created, 'F d, Y (l)');
							echo JText::sprintf('COM_SELLACIOUS_ORDER_STATUS_AT_DATE_MESSAGE', $status->s_title, $status_dt);
						}
						?>
						</span>
					</td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
		<?php
	}
	else
	{
		?><h5><em><?php echo JText::_('COM_SELLACIOUS_ORDER_NO_ITEM_MESSAGE'); ?></em></h5><?php
	}
	?>
	</div>
	<div class="tile-foot toggle-element hidden">
		<?php echo JText::_('COM_SELLACIOUS_ORDER_CREATED_DATE_LABEL'); ?>:
		<strong><?php echo JHtml::_('date', $order->created, 'D, F d, Y h:i A') ?></strong>
		<i class="fa order-<?php echo $paid ? 'paid fa-check' : 'not-paid fa-times' ?>"> </i>
		<span class="order-total">
			<?php echo JText::_('COM_SELLACIOUS_ORDER_GRAND_TOTAL_LABEL'); ?>:
			<span><?php echo $this->helper->currency->display($order->grand_total, $order->currency, $c_currency, true); ?></span>
		</span>
	</div>
</div>


