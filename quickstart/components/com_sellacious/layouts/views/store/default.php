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

use Joomla\Registry\Registry;

/** @var  SellaciousViewStore  $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

// We may later decide not to use cart aio assets and separate the logic
JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);

if ($this->helper->config->get('product_compare')):
	JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
endif;

JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);
JHtml::_('script', 'com_sellacious/isotope.pkgd.min.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.products.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.products.list.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.store.css', null, true);

$styles   = array();
$seller   = new Joomla\Registry\Registry($this->seller);
$logo     = $this->helper->media->getImage('sellers.logo', $seller->get('id'));
$rateable = (array) $this->helper->config->get('allow_ratings_for');

$additionalInfo = array();
$dispatcher = $this->helper->core->loadPlugins();
$dispatcher->trigger('onRenderSellerInfo', array('com_sellacious.store', $seller, &$additionalInfo));
$reviewsUrl = JRoute::_('index.php?option=com_sellacious&view=reviews&seller_uid=' . $seller->get('user_id'));
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
			<div class="product-rating rating-stars star-<?php echo $stars; ?>">
				<a href="<?php echo $reviewsUrl ?>">
					<?php echo number_format($seller->get('rating.rating', 0), 1) ?>
					<?php echo '<span> – </span>'; echo JText::plural('COM_SELLACIOUS_RATINGS_COUNT_N', $seller->get('rating.count')); ?>
				</a>
			</div>
		<?php endif; ?>
		<?php
			foreach ($additionalInfo as $info)
			{
				echo $info;
			}
		?>
	</div>
	<div class="seller-logoarea">
		<img class="seller-logo" src="<?php echo $logo ?>"
		alt="<?php echo htmlspecialchars($seller->get('title'), ENT_COMPAT, 'UTF-8'); ?>">
	</div>
	<div class="clearfix"></div>
</div>

<?php if (count($this->filters)): ?>
<div class="w100p filter-choosen">
	<?php
	foreach ($this->filters as $filter)
	{
		$selected = array();

		foreach ($filter->choices as $ck => $choice)
		{
			if ($choice->selected)
			{
				$selected[$ck] = $choice;
			}
		}

		if (count($selected))
		{
			?><div class="btn-group">
				<label class="btn btn-small active btn-info cursor-normal"><?php echo $filter->title ?>: </label>
			<?php
			foreach ($selected as $ck => $choice)
			{
				?>
				<label for="filter_fields_f<?php echo $filter->id ?>_<?php echo (int) $ck ?>"
					class="btn btn-small btn-default"><?php
						echo $this->helper->field->renderValue($choice->value, $filter->type); ?>
							<i class="fa fa-times cursor-pointer"></i></label><?php
			}
			?></div><?php
		}
	}
	?>
</div>
<div class="clearfix"></div>
<?php
endif;
$options = array(
	'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
	'backdrop' => 'static',
);
echo JHtml::_('bootstrap.renderModal', 'modal-cart', $options);

$switcher_display    = (array) $this->helper->config->get('list_switcher_display');
$active_layout       = $this->helper->config->get('list_style', 'grid');
$list_style_switcher = $this->helper->config->get('list_style_switcher', 1);

if ($list_style_switcher): ?>
	<div class="layout-switcher btn-group" data-toggle="radio">
		<?php if (count($switcher_display)):

			if (!in_array($active_layout, $switcher_display)):
				$active_layout = $switcher_display[0];
			endif; ?>

			<?php if (count($switcher_display) > 1):
				if (in_array('masonry', $switcher_display)): ?>
					<button data-style="masonry-layout" class="btn btn-primary switch-style <?php
						echo $active_layout == 'masonry' ? 'active' : '' ?>"><i class="fa fa-indent"></i></button><?php
				endif;
				if (in_array('grid', $switcher_display)): ?>
					<button data-style="grid-layout" class="btn btn-primary switch-style <?php
						echo $active_layout == 'grid' ? 'active' : '' ?>"><i class="fa fa-th"></i></button><?php
				endif;
				if (in_array('list', $switcher_display)): ?>
					<button data-style="list-layout" class="btn btn-primary switch-style <?php
						echo $active_layout == 'list' ? 'active' : '' ?>"><i class="fa fa-list"></i></button><?php
				endif; ?>
			<?php else: ?>
				<button data-style="<?php echo $active_layout ?>-layout" class="hidden switch-style active"></button>
			<?php endif; ?>

		<?php else: ?>
			<button data-style="masonry-layout" class="btn btn-primary switch-style <?php
				echo $active_layout == 'masonry' ? 'active' : '' ?>"><i class="fa fa-indent"></i></button>
			<button data-style="grid-layout" class="btn btn-primary switch-style <?php
				echo $active_layout == 'grid' ? 'active' : '' ?>"><i class="fa fa-th"></i></button>
			<button data-style="list-layout" class="btn btn-primary switch-style <?php
				echo $active_layout == 'list' ? 'active' : '' ?>"><i class="fa fa-list"></i></button>
		<?php endif; ?>
	</div>
<?php else: ?>
	<button data-style="<?php echo $active_layout ?>-layout" class="hidden switch-style active"></button>
<?php endif; ?>

<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="adminForm" id="adminForm">
<?php
$order         = $this->state->get('list.custom_ordering');
$sortOptions   = array(
	'order_max'  => JText::_('COM_SELLACIOUS_PRODUCTS_ORDERING_ORDER_COUNT'),
	'rating_max' => JText::_('COM_SELLACIOUS_PRODUCTS_ORDERING_RATING'),
	'price_min'  => JText::_('COM_SELLACIOUS_PRODUCTS_ORDERING_PRICE_ASC'),
	'price_max'  => JText::_('COM_SELLACIOUS_PRODUCTS_ORDERING_PRICE_DESC'),
); ?>

<div class="sortingbar">
	<label for="custom_ordering"><?php echo JText::_('COM_SELLACIOUS_SORT_BY') ?></label>
	<?php echo JHtml::_('select.genericlist', $sortOptions, 'custom_ordering', 'onchange="Joomla.submitform();"', 'value', 'text', $order) ?>
</div>
<div class="clearfix"></div>

<?php
$imgH = (int) $this->helper->config->get('products_image_height', 220);
$imgS = $this->helper->config->get('products_image_size_adjust') ?: 'contain';
?>
<style>
	.product-box .image-box .product-img {
		height: <?php echo $imgH ?>px;
		background-size: <?php echo $imgS ?>;
	}
	
	@media (max-width: 767px) {
		.list-layout .product-box .image-box .product-img {
			height: <?php echo $imgH ?>px;
		}
	}
</style>

<div id="products-page" class="w100p">
	<div id="products-box" class="sell-cols-row">
		<?php
		if (count($this->items) == 0)
		{
			?><h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_NO_MATCH_FILTER') ?></h4><?php
		}

		foreach ($this->items as $item)
		{
			$cat_id = (int) $item->spl_listing_catid;

			if (!isset($styles[$cat_id]))
			{
				$style  = '';
				$params = new Registry($item->spl_listing_params);

				// New or old format?
				$css = isset($params['styles']) ? (array) $params->get('styles') : $params;

				foreach ($css as $css_k => $css_v)
				{
					$style .= "$css_k: $css_v;";
				}

				$styles[$cat_id] = ".product-box.spl-cat-$cat_id { $style }";
			}

			echo $this->loadTemplate('block', $item);
		}

		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration(implode("\n", $styles));
		?>
		<div class="clearfix"></div>
	</div>
</div>
<div class="clearfix"></div>
<div class="left pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>
</form>
