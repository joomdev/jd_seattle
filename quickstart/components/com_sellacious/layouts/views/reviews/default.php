<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Registry\Registry;
use Sellacious\Product;

defined('_JEXEC') or die;

JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

/** @var SellaciousViewOrders $this */
$app  = JFactory::getApplication();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Load the behaviors.
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

JHtml::_('script', 'com_sellacious/fe.view.orders.tile.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.reviews.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

$reviews     = $this->items;
$link_detail = $this->helper->config->get('product_detail_page');
$rateable    = (array) $this->helper->config->get('allow_ratings_for');
?>
<?php if (!empty($this->seller->id)):
	$seller = new Joomla\Registry\Registry($this->seller);
	$logo   = $this->helper->media->getImage('sellers.logo', $seller->get('id'));
	?>
	<div id="seller-info">
		<div class="sellerdata">
			<h2><?php echo $seller->get('store_name') ?: $seller->get('title') ?></h2>
			<?php if ($this->helper->config->get('show_store_product_count') == '1' && $seller->get('product_count')): ?>
				<div class="product-count">
					<?php echo JText::plural('COM_SELLACIOUS_SELLER_PRODUCT_COUNT_N', $seller->get('product_count')); ?>
				</div>
			<?php endif; ?>
			<?php if ($seller->get('store_address')): ?>
				<div class="store-address"><?php echo nl2br($seller->get('store_address')) ?></div>
			<?php endif; ?>
			<?php if (in_array('seller', $rateable)): ?>
				<?php $stars = round($seller->get('rating.rating', 0) * 2); ?>
				<div class="product-rating rating-stars star-<?php echo $stars ?>">
					<?php echo number_format($seller->get('rating.rating', 0), 1) ?>
					<?php echo '<span> – </span>'; echo JText::plural('COM_SELLACIOUS_RATINGS_COUNT_N', $seller->get('rating.count')); ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="seller-logoarea">
			<img class="seller-logo" src="<?php echo $logo ?>"
				 alt="<?php echo htmlspecialchars($seller->get('title'), ENT_COMPAT, 'UTF-8'); ?>">
		</div>
		<div class="clearfix"></div>
	</div>
	<?php if (!empty($this->seller_reviews)): ?>
	<div class="rating-box sell-infobox">
		<div class="reviewslist">
			<?php
			foreach ($this->seller_reviews as $sreview):?>
				<div class="sell-row nomargin">
					<div class="sell-col-xs-3 nopadd">
						<div class="reviewauthor">
							<div class="rating-stars rating-stars-md star-<?php echo $sreview->rating * 2 ?>">
								<span class="starcounts"><?php echo number_format($sreview->rating, 1); ?></span>
							</div>
							<h4 class="pr-author"><?php echo $sreview->author_name ?></h4>
							<h5 class="pr-date"><?php echo JHtml::_('date', $sreview->created, 'M d, Y'); ?></h5>
							<?php if ($sreview->buyer == 1): ?>
								<div class="buyer-badge"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CERTIFIED_BUYER'); ?></div>
							<?php endif; ?>
						</div>
					</div>
					<div class="sell-col-xs-9 nopadd">
						<div class="reviewtyped">
							<h3 class="pr-title"><?php echo $sreview->title ?></h3>
							<p class="pr-body"><?php echo $sreview->comment ?></p>
						</div>
					</div>
				</div>
			<?php
			endforeach;
			?>
		</div>
	</div>
	<h3><?php echo JText::_('COM_SELLACIOUS_REVIEWS_PRODUCT');?></h3>
	<?php endif; ?>
<?php endif; ?>

<?php if ($this->state->get('filter.product_id', 0)):
	$productId = $this->state->get('filter.product_id', 0);
	$product   = new Product($productId);

	$productRating = new Registry($this->helper->rating->getProductRating($productId));
	$productImage  = $this->helper->product->getImage($productId);
	?>
	<div id="product-info">
		<div class="productdata">
			<h2><?php echo $product->get('title') ?></h2>
			<?php if (in_array('product', $rateable)): ?>
				<?php $stars = round($productRating->get('rating', 0) * 2); ?>
				<div class="product-rating rating-stars star-<?php echo $stars ?>">
					<?php echo number_format($productRating->get('rating', 0), 1) ?>
					<?php echo '<span> – </span>'; echo JText::plural('COM_SELLACIOUS_RATINGS_COUNT_N', $productRating->get('count')); ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="product-logoarea">
			<img class="product-logo" src="<?php echo $productImage ?>"
				 alt="<?php echo htmlspecialchars($product->get('title'), ENT_COMPAT, 'UTF-8'); ?>">
		</div>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>"
	  method="post" name="adminForm" id="adminForm">
		<?php if (!empty($reviews)): ?>
		<div class="rating-box sell-infobox">
			<div class="reviewslist">
				<?php
				foreach ($reviews as $review):

					/** @var Sellacious\Product $product */
					$product = $review->product;

					$url_raw = 'index.php?option=com_sellacious&view=product&p=' . $product->getCode();
					$url     = JRoute::_($url_raw);
					?>
					<div class="sell-row nomargin">
						<div class="sell-col-xs-3 nopadd">
							<div class="reviewauthor">
								<div class="rating-stars rating-stars-md star-<?php echo $review->rating * 2 ?>">
									<span class="starcounts"><?php echo number_format($review->rating, 1); ?></span>
								</div>
								<h4 class="pr-author"><?php echo $review->author_name ?></h4>
								<h5 class="pr-date"><?php echo JHtml::_('date', $review->created, 'M d, Y'); ?></h5>
								<?php if ($review->buyer == 1): ?>
									<div class="buyer-badge"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CERTIFIED_BUYER'); ?></div>
								<?php endif; ?>
							</div>
						</div>
						<div class="sell-col-xs-9 nopadd">
							<?php if (!$this->state->get('filter.product_id', 0)):?>
							<div class="reviewproduct">
								<div class="product-icon">
									<img src="<?php echo $review->product_image; ?>" />
								</div>
								<div class="product-title">
									<a href="<?php echo $link_detail ? $url : 'javascript:void(0);' ?>" title="<?php echo $product->get('title'); ?>">
										<?php echo $product->get('title'); ?></a>
								</div>
							</div>
							<?php endif; ?>
							<div class="reviewtyped">
								<h3 class="pr-title"><?php echo $review->title ?></h3>
								<p class="pr-body"><?php echo $review->comment ?></p>
							</div>
						</div>
					</div>
				<?php
				endforeach;
				?>
			</div>
		</div>
		<?php endif; ?>
		<table class="w100p">
			<tr>
				<td class="text-center">
					<div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
				</td>
			</tr>
			<tr>
				<td class="text-center">
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</table>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>

		<?php
		if ($tmpl = $app->input->get('tmpl'))
		{
			?><input type="hidden" name="tmpl" value="<?php echo $tmpl ?>"/><?php
		}

		if ($layout = $app->input->get('layout'))
		{
			?><input type="hidden" name="layout" value="<?php echo $layout ?>"/><?php
		}

		echo JHtml::_('form.token');
		?>
</form>
