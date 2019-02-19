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
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);
JHtml::_('script', 'sellacious/util.anchor.js', false, true);

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

$item               = $this->item;
$allow_checkout     = $this->helper->config->get('allow_checkout');
$cart_pages         = (array) $this->helper->config->get('product_add_to_cart_display');
$buynow_pages       = (array) $this->helper->config->get('product_buy_now_display');
$display_stock      = $this->helper->config->get('frontend_display_stock');
$c_currency         = $this->helper->currency->current('code_3');
$marketPlace        = $this->helper->config->get('multi_seller');
$login_to_see_price = $this->helper->config->get('login_to_see_price', 0);

$me           = JFactory::getUser();
$samplemedia  = $this->getSampleMedia();
$preview_url  = $this->item->get('preview_url');
$preview_mode = $this->item->get('preview_mode');
$mfr          = array(
	'list.select' => "a.id, IF(a.title = '', u.name, a.title) AS title",
	'list.join'   => array(array('inner', '#__users u ON u.id = a.user_id')),
	'user_id'     => $item->get('manufacturer_id')
);
$manufacturer = $this->helper->manufacturer->loadObject($mfr);

$current_url = JUri::getInstance()->toString();
$login_url   = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($current_url), false);

if ($this->helper->config->get('mfg_link') == 'cats')
{
	$urlM = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=1&manufacturer_id=' . $item->get('manufacturer_id'));
}
elseif ($this->helper->config->get('mfg_link') == 'products')
{
	$urlM = JRoute::_('index.php?option=com_sellacious&view=products&manufacturer_id=' . $item->get('manufacturer_id'));
}
else
{
	$urlM = 'javascript:void(0)';
}

$reviewsUrl = JRoute::_('index.php?option=com_sellacious&view=reviews&product_id=' . $item->get('id'));
?>
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>

<div class="product-single">
	<div class="sell-row">
		<div class="sell-col-md-5">
			<?php echo $this->loadTemplate('images'); ?>
			<div class="clearfix"></div>
			<?php if ($item->get('price_display') == 0 && !($login_to_see_price && $me->guest)): ?>
				<div id="buy-now-box">
					<?php $btnClass = $item->get('stock_capacity') > 0 ? 'btn-add-cart' : ' disabled'; ?>
					<?php if ($allow_checkout && in_array('product', $cart_pages)): ?>
						<button type="button" class="btn btn-warning btn-cart <?php echo $btnClass ?>"
								data-item="<?php echo $item->get('code') ?>"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART')); ?>
							<?php if ($display_stock):
								echo '(' . (int) $item->get('stock_capacity') . ')';
							endif; ?>
						</button>
					<?php endif; ?>
					<?php if ($allow_checkout && in_array('product', $buynow_pages)): ?>
						<button type="button" class="btn btn-success btn-cart <?php echo $btnClass ?>"
							data-item="<?php echo $item->get('code') ?>" data-checkout="true">
							<?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW')); ?></button>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ($preview_url && $preview_mode): ?>
				<div class="preview_btn">
					<a href="<?php echo $preview_url; ?>" target="<?php echo $preview_mode; ?>" class="btn btn-primary">
						<?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_PREVIEW_BTN'); ?>
					</a>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
		</div>
		<div id="product-info" class="sell-col-md-7">
			<div class="maintitlearea">
				<?php if (in_array('product', (array) $this->helper->config->get('splcategory_badge_display')) && is_array($item->get('special_listings'))): ?>
					<div class="badge-area"><?php
						foreach ($item->get('special_listings') as $spl_cat):
							$badges = $this->helper->media->getImages('splcategories.badge', (int) $spl_cat->catid, false);
							if (count($badges)): ?>
								<img src="<?php echo reset($badges) ?>" alt="Badge" class="spl-badge"/><?php
							endif;
						endforeach; ?>
					</div>
				<?php endif; ?>
				<h1><?php echo $item->get('title');
					echo $item->get('variant_title') ? ' - <small>' . $item->get('variant_title') . '</small>' : ''; ?></h1>

				<?php $rating_display = (array) $this->helper->config->get('product_rating_display'); ?>
				<?php if ($this->helper->config->get('product_rating') && (in_array('product', $rating_display))): ?>
					<?php $stars = round($item->get('rating.rating', 0) * 2); ?>
					<div class="product-rating rating-stars star-<?php echo $stars ?>">
						<a href="<?php echo $reviewsUrl ?>"><?php echo number_format($item->get('rating.rating', 0), 1) ?></a>
					</div>
				<?php endif; ?>
			</div>

			<!-- BEGIN: seller/admin can directly jump to backend for edit -->
			<?php $actions = array('basic.own', 'seller.own', 'pricing.own', 'shipping.own', 'related.own', 'seo.own');

			if ($this->helper->access->check('product.edit') ||
				($this->helper->access->checkAny($actions, 'product.edit.', $item->get('id')) && $item->get('seller_uid') == $me->id)): ?>
				<?php $editUrl = JUri::root() . JPATH_SELLACIOUS_DIR . '/index.php?option=com_sellacious&view=product&layout=edit&id=' . $item->get('id'); ?>
				<a target="_blank" class="btn btn-mini btn-default edit-product pull-right" href="<?php echo $editUrl; ?>"><i
							class="fa fa-edit"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_LINK_BACKEND_EDIT'); ?></a>&nbsp;
			<?php endif; ?>
			<!-- END: seller/admin can directly jump to backend for edit -->
			<div class="clearfix"></div>

			<?php if (isset($manufacturer->id)): ?>
				<div class="manufacturer-name">
					<a href="<?php echo $urlM ?>" class="hasTooltip" title="Manufacturer"><?php echo $manufacturer->title; ?></a>
				</div>
			<?php endif; ?>

			<hr class="isolate"/>
			<div class="sell-row">

				<?php
				$showlisting          = $this->helper->config->get('show_allowed_listing_type');
				$allowed_listing_type = (array) $this->helper->config->get('allowed_listing_type');
				$conditionbox         = ($showlisting && (count($allowed_listing_type) != 1));
				$exchangeReturn       = ($item->get('exchange_days')) || ($item->get('return_days'));
				?>

				<div class="<?php echo ($marketPlace || $conditionbox || $exchangeReturn) ? 'sell-col-xs-7' : 'sell-col-xs-12' ?>">
					<?php
					if ($login_to_see_price && $me->guest):
					?>
						<div class="pricearea">
							<a href="<?php echo $login_url ?>"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_LOGIN_TO_VIEW'); ?></a>
						</div>
					<?php
					else:
						echo $this->loadTemplate('price');
					endif;
					?>
					<div class="qtyarea">
						<?php
						if ($allow_checkout && $item->get('price_display') == 0):
							if ($item->get('stock_capacity') > 0):
								$options = array(
									'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
									'backdrop' => 'static',
								);
								echo JHtml::_('bootstrap.renderModal', 'modal-cart', $options); ?>
								<div class="quantitybox">
									<label><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_QUANTITY_INPUT_LABEL'); ?>
										<input type="number" name="quantity" id="product-quantity" min="1"
											   data-uid="<?php echo $item->get('code') ?>" value="1"/>
									</label>
								</div><?php
							else: ?>
								<div class="label btn-primary outofstock">
								<?php echo JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK') ?></div><?php
							endif;
						endif; ?>
					</div>

					<?php if (isset($samplemedia->id) && $samplemedia->id > 0): ?>
						<div class="esamplefile">
							<a download href="<?php echo $samplemedia->path; ?>" class="btn btn-primary">
								<i class="fa fa-download"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_DOWNLOAD_SAMPLE'); ?></a>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($marketPlace || $conditionbox || $exchangeReturn): ?>
					<div class="sell-col-xs-5">
						<div class="product-actions">
							<?php if ($marketPlace): ?>
								<div class="seller-details">
									<div class="seller-info">
										<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SOLD_BY'); ?></h4>
										<p><a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->get('seller_uid')); ?>">
											<?php echo $item->get('seller_store', $item->get('seller_name', $item->get('seller_company', $item->get('seller_username')))); ?></a>
											<?php if ($this->helper->config->get('show_seller_rating')): ?>
												<?php $rating = $item->get('seller_rating.rating'); ?>
												<span class="label <?php echo ($rating < 3) ? 'label-warning' : 'label-success' ?>"><?php
													echo number_format($rating, 1) ?> / 5.0</span>
											<?php endif; ?></p>
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

							<?php if ($exchangeReturn): ?>
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

			<?php if ((in_array('product', (array) $this->helper->config->get('product_features_list'))) || (count($offers = $item->get('offers')))): ?>
				<hr class="isolate"/>
				<div class="sell-row">
					<?php if (in_array('product', (array) $this->helper->config->get('product_features_list'))): ?>
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
										$lang_key = 'COM_SELLACIOUS_PRODUCT_OFFER_ITEM_TEXT' . ($offer->inclusive && $offer->apply_rule_on_price_display ? '_INCLUSIVE' : '');
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

			<?php if ($attachments = $this->item->get('attachments')): ?>
				<hr class="isolate"/>
				<div class="attachment-area">
					<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_ATTACHMENTS'); ?></h4>
					<div class="media-attachments">
						<ul class="media-attachment-row">
							<?php foreach ($attachments as $attachment): ?>
								<?php $downloadLink = JRoute::_(JUri::base(true) . '/index.php?option=com_sellacious&task=media.download&id=' . $attachment->id); ?>
								<li><a href="<?php echo $downloadLink ?>" class="attach-link-view"><?php echo $attachment->original_name ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>

			<?php echo $this->loadTemplate('variations'); ?>

			<?php if ($item->get('introtext')): ?>
				<blockquote class="introtext"><?php echo $item->get('introtext') ?></blockquote>
			<?php endif; ?>

			<hr class="isolate"/>
			<?php echo $this->loadTemplate('toolbar'); ?>

			<div class="clearfix"></div>
			<?php if ($this->helper->config->get('multi_seller', 0) && count($item->get('sellers')) > 1): ?>
				<div class="product-sellers-count">
					<a href="#also-selling">
						<i class="fa fa-location-arrow"></i>
						<?php echo JText::plural('COM_SELLACIOUS_PRODUCT_SELLER_COUNT_N_DESC', count($item->get('sellers'))); ?>
					</a>
				</div>
				<div class="clearfix"></div>
			<?php endif; ?>

		</div>
		<div class="clearfix"></div>
	</div>

	<?php if ($item->get('variants')): ?>
		<?php echo $this->loadTemplate('variants'); ?>
	<?php endif; ?>

	<div class="clearfix"></div>

	<?php if ($item->get('sellers')): ?>
		<?php echo $this->loadTemplate('sellers'); ?>
	<?php endif; ?>

	<?php if ($item->get('description')): ?>
		<div class="description-box sell-infobox">
			<h3><?php echo JText::_('COM_SELLACIOUS_PRODUCT_DESCRIPTION'); ?></h3>
			<div class="desc-text sell-info-inner">
				<?php echo $item->get('description') ?>
			</div>
		</div>
	<?php endif; ?>

	<?php echo $this->loadTemplate('physical'); ?>

	<?php
	$in_box    = $item->get('whats_in_box');
	$pkg_items = $item->get('package_items');

	if ($in_box || $pkg_items): ?>
		<div class="package-box">
		<h3><?php echo JText::_('COM_SELLACIOUS_PRODUCT_WHAT_IN_BOX'); ?></h3><?php
		if ($in_box): ?>
			<div class="package-inner">
			<?php echo $in_box ?>
			</div><?php
		endif;

		if ($pkg_items):
			echo $this->loadTemplate('packages', $pkg_items);
		endif; ?>
		</div><?php
	endif;

	if ($item->get('specifications')):
		echo $this->loadTemplate('specifications');
	endif;

	if ($this->helper->config->get('product_rating')): ?>
		<div class="rating-box sell-infobox">
			<h3><?php echo JText::_('COM_SELLACIOUS_TITLE_RATINGS') ?></h3><?php
			echo $this->loadTemplate('ratings');
			echo $this->loadTemplate('rating');
			echo $this->loadTemplate('reviews'); ?>
		</div>
	<?php endif;

	if ($this->helper->config->get('product_questions')): ?>
		<div class="questionarea-box sell-infobox">
			<h3><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_ASK') ?></h3><?php
			echo $this->loadTemplate('question');
			echo $this->loadTemplate('questions'); ?>
		</div>
	<?php endif; ?>
</div>
