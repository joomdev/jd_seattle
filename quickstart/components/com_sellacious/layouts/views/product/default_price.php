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

/** @var  SellaciousViewProduct  $this */

$item       = $this->item;
$prices     = $item->get('prices');
$display    = $item->get('price_display');
$s_currency = $this->helper->currency->forSeller($item->get('seller_uid'), 'code_3');
$c_currency = $this->helper->currency->current('code_3');

$security              = $this->helper->config->get('contact_spam_protection');
$allowed_price_display = (array) $this->helper->config->get('allowed_price_display');

$page_id = $this->getLayout() == 'modal' ? 'product_modal' : 'product';

if ($display == 0):
	$price_display = $this->helper->config->get('product_price_display');
	$price_d_pages = (array) $this->helper->config->get('product_price_display_pages');

	if ($item->get('price_id') > 0 && $price_display > 0 && in_array($page_id, $price_d_pages)): ?>
		<div class="pricearea">
			<div class="mainprice">
				<span class="product-price"><?php
					echo round($item->get('sales_price'), 2) >= 0.01 ? $this->helper->currency->display($item->get('sales_price'), $s_currency, $c_currency, true) : JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE');
				?></span>
				<?php if ($price_display == 2 && $item->get('list_price') > 0):
					echo JText::_('COM_SELLACIOUS_PRODUCT_SELLING_PRICE_LABEL'); ?> <strong><del><?php echo $this->helper->currency->display($item->get('list_price'), $s_currency, $c_currency, true) ?></del></strong>
				<?php endif; ?>
			</div>
			<div class="clearfix"></div>

			<?php echo $this->loadTemplate('prices'); ?>

			<?php if ($this->helper->config->get('show_shipping_info_on_detail')): ?>
				<div class="text-left product-ship-cost">
					<?php
					echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_ICON');

					$flat_ship = $item->get('flat_shipping');
					$ship_fee  = $item->get('shipping_flat_fee');

					if ($flat_ship == 0):
						echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_IN_CART');
					elseif (round($ship_fee, 2) > 0):
						$fee = $this->helper->currency->display($ship_fee, $s_currency, $c_currency, true);
						echo JText::sprintf('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FLAT', $fee);
					else:
						echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FREE');
					endif; ?>
				</div>
			<?php endif; ?>
		</div><?php
	endif;
elseif ($display == 1 && in_array(1, $allowed_price_display)): ?>
	<div class="querysend btn-toggle">
		<button type="button" class="btn btn-default" data-toggle="true"><?php
				echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_CALL_US') ?></button>
		<button type="button" class="btn btn-default hidden" data-toggle="true"><?php
			$mobile = $item->get('seller_mobile') ?: JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_NO_NUMBER');

			if ($security):
				$text = $this->helper->media->writeText($mobile, 4, true);
				?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
			else:
				echo $mobile;
			endif; ?>
		</button>
	</div>
	<?php
elseif ($display == 2 && in_array(2, $allowed_price_display)): ?>
	<div class="querysend btn-toggle">
		<button type="button" class="btn btn-default" data-toggle="true"><?php
			echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_EMAIL_US') ?></button>
		<button type="button" class="btn btn-default hidden" data-toggle="true"><?php
			$seller_email = $item->get('seller_email') ?: JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_NO_EMAIL');

			if ($security):
				$text = $this->helper->media->writeText($seller_email, 4, true);
				?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
			else:
				echo $seller_email;
			endif; ?>
		</button>
	</div>
	<?php
elseif ($display == 3 && in_array(3, $allowed_price_display)):
	// $body = JLayoutHelper::render('com_sellacious.product.seller.queryform');
	$code    = $this->state->get('product.code');
	$options = array(
		'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_OPEN_QUERY_FORM_FOR', $item->get('title'), $item->get('variant_title')),
		'backdrop' => 'static',
		'height'   => '500',
		'keyboard' => true,
		'url'      => "index.php?option=com_sellacious&view=product&p=" . $code . "&layout=query&tmpl=component",
	);

	echo JHtml::_('bootstrap.renderModal', "query-form-{$code}", $options);
	?>
	<div class="querysend"><a href="#query-form-<?php echo $code ?>"
			role="button" data-toggle="modal" class="btn btn-primary"><i class="fa fa-file-text"></i>
			<?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_OPEN_QUERY_FORM') ?></a>
	</div>
	<?php
endif;
