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

/** @var SellaciousViewProduct $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip', '.hasTooltip');

if ($this->helper->config->get('product_compare')):
	JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
endif;

JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.product.js', true, true);

// We may later decide not to use cart aio assets and separate the logic
JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.product.css', null, true);

$item           = $this->item;
$allow_checkout = $this->helper->config->get('allow_checkout');
$cart_pages     = (array) $this->helper->config->get('product_add_to_cart_display');
$buynow_pages   = (array) $this->helper->config->get('product_buy_now_display');
$c_currency     = $this->helper->currency->current('code_3');
$marketPlace    = $this->helper->config->get('multi_seller');

JText::script('COM_SELLACIOUS');
?>
<style>
	/* Suppress compare bar for now, it should not be loaded in the modal at all. Work for later */
	#compare-bar { display: none; }
	.product_quickview{ padding: 20px;}
</style>
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>

<div class="product_quickview sell-row">
	<div class="sell-col-md-5">
		<?php echo $this->loadTemplate('images'); ?>
		<div class="clearfix"></div>
	</div>
	<div id="product-info" class="sell-col-md-7">
		<div class="maintitlearea">
			<?php if (in_array('product', (array) $this->helper->config->get('splcategory_badge_display')) && is_array($item->get('special_listings'))): ?>
				<div class="badge-area"><?php
					foreach ($item->get('special_listings') as $spl_cat):
						$badges = $this->helper->media->getImages('splcategories.badge', (int) $spl_cat->catid, false);
						if (count($badges)): ?>
							<img src="<?php echo reset($badges) ?>" class="spl-badge"/><?php
						endif;
					endforeach; ?>
				</div>
			<?php endif; ?>
			<h1><?php echo $item->get('title');
				echo $item->get('variant_title') ? ' - <small>' . $item->get('variant_title') . '</small>' : ''; ?></h1>

			<?php $rating_display = (array) $this->helper->config->get('product_rating_display'); ?>
			<?php if ($this->helper->config->get('product_rating') && (in_array('product_modal', $rating_display))): ?>
				<?php $stars = round($item->get('rating.rating', 0) * 2); ?>
				<div class="product-rating rating-stars star-<?php echo $stars ?>"><?php echo number_format($item->get('rating.rating', 0), 1) ?></div>
			<?php endif; ?>
		</div>

		<div class="clearfix"></div>
		<hr class="isolate"/>
		<div class="sell-row">
			<?php
			$showlisting = $this->helper->config->get('show_allowed_listing_type');
			$allowed_listing_type = (array) $this->helper->config->get('allowed_listing_type');
			$conditionbox = ($showlisting && (count($allowed_listing_type) != 1));
			$exchgeRtrn = ($item->get('exchange_days')) || ($item->get('return_days'));
			?>

			<div class="<?php echo ($marketPlace || $conditionbox || $exchgeRtrn) ? 'sell-col-xs-7' : 'sell-col-xs-12' ?>">
				<?php echo $this->loadTemplate('price'); ?>
				<div class="qtyarea">
					<?php
					if ($allow_checkout && $item->get('price_display') == 0):
						if ($item->get('stock_capacity') > 0): ?>
							<div class="quantitybox">
								<label><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_QUANTITY_INPUT_LABEL'); ?>
									<input type="number" name="quantity" id="product-quantity" min="1"
										   data-uid="<?php echo $item->get('code') ?>" value="1"/>
								</label>
								</div><?php
						else:?>
							<div class="label btn-primary outofstock">
								<?php echo JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK') ?></div><?php
						endif;
					endif ;?>
				</div>
			</div>

			<?php if($marketPlace || $conditionbox || $exchgeRtrn): ?>
				<div class="sell-col-xs-5">
					<div class="product-actions">
						<?php if($marketPlace): ?>
							<div class="seller-details">
								<div class="seller-info">
									<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SOLD_BY'); ?></h4>
									<p><a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->get('seller_uid')); ?>">
											<?php echo $item->get('seller_store', $item->get('seller_name', $item->get('seller_company', $item->get('seller_username')))); ?></a>
										<?php if ($this->helper->config->get('show_seller_rating')) : ?>
											<?php $rating = $item->get('seller_rating.rating'); ?>
											<span class="label <?php echo ($rating < 3) ? 'label-warning' : 'label-success' ?>"><?php echo number_format($rating, 1) ?> / 5.0</span>
										<?php endif;?></p>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($showlisting): ?>
								<?php if (array_intersect(array(2, 3), $allowed_listing_type)): ?>
								<div class="conditionbox">
									<span class="label label-info"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CONDITION'); ?>
										<?php
										$list_type = $item->get('listing_type');

										// What if this is a not allowed listing type value
										if ($list_type == 1):
											echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_LISTING_TYPE_VALUE', $list_type);
										else:
											$list_cond = $item->get('item_condition');
											echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_ITEM_CONDITION_VALUE', $list_type * 10 + (int) $list_cond);
										endif;
										?>
									</span>
								</div>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ($exchgeRtrn): ?>
							<div class="exchange_box">
								<?php if ($item->get('exchange_days')): ?>
									<?php if ($item->get('exchange_tnc')):
										$options = array(
											'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')),
											'backdrop' => 'static',
										);
										echo JHtml::_('bootstrap.renderModal', 'exchange_tnc', $options, $item->get('exchange_tnc'));
									endif; ?>
									<div class="replacement-info">
										<i class="fa fa-refresh"></i>
										<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')); ?>
										<?php if ($item->get('exchange_tnc')): ?>
											<a href="#exchange_tnc" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<?php if ($item->get('return_days')): ?>
									<?php if ($item->get('return_tnc')):
										$options = array(
											'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')),
											'backdrop' => 'static',
										);
										echo JHtml::_('bootstrap.renderModal', 'return_tnc', $options, $item->get('return_tnc'));
									endif; ?>
									<div class="replacement-info">
										<i class="fa fa-refresh"></i>
										<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')); ?>
										<?php if ($item->get('return_tnc')): ?>
											<a href="#return_tnc" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
		</div>

		<?php if ((in_array('product_modal', (array) $this->helper->config->get('product_features_list'))) || (count($offers = $item->get('offers')))): ?>
			<hr class="isolate"/>
			<div class="sell-row">
				<?php if (in_array('product_modal', (array) $this->helper->config->get('product_features_list'))): ?>
					<?php
					$features = array_filter((array) json_decode($item->get('variant_features'), true), 'trim');

					if (!$features):
						$features = array_filter((array) json_decode($item->get('features'), true), 'trim');
					endif;

					if ($features): ?>
						<div class="<?php echo (count($offers = $item->get('offers'))) ? 'sell-col-xs-7' : 'sell-col-xs-12' ?>">
							<ul class="product-features"><?php
								foreach ($features as $feature):
									echo '<li>' . htmlspecialchars($feature) . '</li>';
								endforeach; ?>
							</ul>
						</div><?php
					endif; ?>
				<?php endif; ?>

				<?php if (count($offers = $item->get('offers'))): ?>
					<div class="<?php echo $features ? 'sell-col-xs-5' : 'sell-col-xs-12' ?>">
						<div class="offer-info">
							<h4 class="offer-info-header"><?php echo JText::plural('COM_SELLACIOUS_PRODUCT_OFFER_COUNT_N', count($offers)) ?> | <?php
								echo JText::_('COM_SELLACIOUS_PRODUCT_APPLICATION_ON_CHECKOUT'); ?></h4>
							<div class="offerslist">
							<?php
							foreach ($offers as $offer)
							{
								$lang_key = 'COM_SELLACIOUS_PRODUCT_OFFER_ITEM_TEXT' . ($offer->inclusive ? '_INCLUSIVE' : '');
								echo '<div class="offerblock">' . JText::sprintf($lang_key, $offer->title) . '</div>';
							}
							?>
							</div>
						</div>
						<div class="clearfix"></div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>


		<?php if ($item->get('introtext')): ?>
			<blockquote class="introtext"><?php echo $item->get('introtext') ?></blockquote>
		<?php endif; ?>

		<hr class="isolate"/>
		<?php echo $this->loadTemplate('toolbar'); ?>

		<div class="clearfix"></div>

		<?php if ($item->get('price_display') == 0): ?>
			<hr class="isolate"/>
			<div id="buy-now-box" class="sell-row">
				<?php $btnClass = $item->get('stock_capacity') > 0 ? 'btn-add-cart' : ' disabled'; ?>
				<?php if ($allow_checkout && in_array('product_modal', $cart_pages)): ?>
					<div class="sell-col-xs-6">
						<button type="button" class="btn btn-primary btn-cart <?php echo $btnClass ?>"
							data-item="<?php echo $item->get('code') ?>"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART')); ?></button>
					</div>
				<?php endif; ?>
				<?php if ($allow_checkout && in_array('product_modal', $buynow_pages)): ?>
					<div class="sell-col-xs-6">
						<button type="button" class="btn btn-success btn-cart <?php echo $btnClass ?>" data-item="<?php echo $item->get('code') ?>"
							data-checkout="true"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW')); ?></button>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<div class="clearfix"></div>
