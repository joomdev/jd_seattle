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

/** @var  SellaciousViewCategories $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

if ($this->helper->config->get('product_compare')):
	JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
endif;

JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);

JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);
JHtml::_('script', 'com_sellacious/isotope.pkgd.min.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.categories.products.css', null, true);

$styles = array();

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
	<div class="clearfix"></div>
<?php else: ?>
	<button data-style="<?php echo $active_layout ?>-layout" class="hidden switch-style active"></button>
<?php endif; ?>

<?php
$options = array(
	'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
	'backdrop' => 'static',
);
echo JHtml::_('bootstrap.renderModal', 'modal-cart', $options);

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
		foreach ($this->get('products') as $item)
		{
			if ($cat_id = (int) $item->spl_listing_catid)
			{
				$css2 = array();
				$params = new Registry($item->spl_listing_params);

				// New or old format?
				$css = isset($params['styles']) ? (array) $params->get('styles') : $params;

				foreach ($css as $css_k => $css_v)
				{
					$css2[$css_k] = "$css_k: $css_v;";
				}

				$styles[$cat_id] = ".product-box.spl-cat-$cat_id { \n\t" . implode("\n\t", $css2) . "\n }";
			}

			echo $this->loadTemplate('product', $item);
		}

		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration(implode("\n", $styles));
		?>
		<div class="clearfix"></div>
	</div>
</div>
<div class="clearfix"></div>
<?php
$viewAllForProducts = $this->helper->config->get('category_page_view_all_products', 1);
$viewAllUrl         = JRoute::_('index.php?option=com_sellacious&view=products&category_id=' . $this->state->get('categories.id', 1));
?>
<?php if ($viewAllForProducts): ?>
	<div class="view-button-area">
		<a href="<?php echo $viewAllUrl ?>" class="btn btn-primary" class="view-all-products-link">
			<?php echo JText::_('COM_SELLACIOUS_PRODUCT_VIEW_ALL_PRODUCTS'); ?> &nbsp;<i class="fa fa-chevron-right"></i>
		</a>
	</div>
<?php endif; ?>
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" value="1" id="formToken">
