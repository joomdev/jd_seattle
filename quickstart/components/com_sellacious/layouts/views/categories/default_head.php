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

// @var  SellaciousViewCategories $this */
if (empty($this->current->id) && !$this->state->get('manufacturers.id') && !$this->state->get('stores.id'))
{
	return;
}
if (!empty($this->current->id))
{
	$item        = $this->current;
	$paths       = $this->helper->category->getImages($item->id, true);
	$suffix      = $item->parent_id > 1 ? '&parent_id=' . $item->parent_id : '';
	$title       = $item->title;
	$description = $item->description;
}
else
{
	$item        = new stdClass();
	$paths       = array();
	$suffix      = '';
	$title       = '';
	$description = '';
}

$suffixStore        = $this->state->get('stores.id') ? '&store_id=' . $this->state->get('stores.id') : '';
$suffixManufacturer = $this->state->get('manufacturers.id') ? '&manufacturer_id=' . $this->state->get('manufacturers.id') : '';
$urlC               = JRoute::_('index.php?option=com_sellacious&view=categories' . $suffix . $suffixStore . $suffixManufacturer);
$urlP               = JRoute::_('index.php?option=com_sellacious&view=products&category_id=' . $this->state->get('categories.id', 1) . $suffixStore . $suffixManufacturer);
$imgPriority        = $this->state->get('image_priority');
$allowFallback      = $this->state->get('allow_fallback');

if ($this->state->get('manufacturers.id'))
{
	$manufacturer = $this->helper->manufacturer->getItem(array('user_id' => $this->state->get('manufacturers.id')));
	$pathsM       = $this->helper->media->getImages('manufacturers.logo', $manufacturer->id, true);
}

if ($this->state->get('stores.id'))
{
	$seller = $this->helper->seller->getItem(array('user_id' => $this->state->get('stores.id')));
	$pathsS = $this->helper->media->getImages('sellers.logo', $seller->id, true);
}


if ((int) $allowFallback == 1)
{
	if ($imgPriority == 'mfg')
	{
		if (!empty($pathsM))
		{
			$paths = $pathsM;
		}
		elseif (!empty($pathsS))
		{
			$paths = $pathsS;
		}
	}
	elseif ($imgPriority == 'store')
	{
		if (!empty($pathsS))
		{
			$paths = $pathsS;
		}
	}
}
else
{
	if ($imgPriority == 'mfg' && !empty($pathsM))
	{
		$paths = $pathsM;
	}
	elseif ($imgPriority == 'store' && !empty($pathsS))
	{
		$paths = $pathsS;
	}
	elseif (empty($this->current->id))
	{
		return;
	}
}
?>

<div class="category-box-parent" data-rollover="container">
	<?php if ($description): ?>
		<div class="sell-row">
			<div class="sell-col-sm-6">
				<div class="cat-img">
					<span class="product-img bgrollover" style="background-image:url(<?php echo reset($paths) ?>);"
						  data-rollover="<?php echo htmlspecialchars(json_encode($paths)); ?>"></span>
				</div>
			</div>
			<div class="sell-col-sm-6">
				<div class="cat-infoarea">
					<h1><?php echo $title; ?></h1>

					<?php if ($this->state->get('stores.id')) : ?>
						<h3><?php echo 'Store: ' . $seller->store_name ?: $seller->title; ?></h3>
					<?php endif; ?>

					<?php if ($this->state->get('manufacturers.id')) : ?>
						<h3><?php echo 'Manufacturer: ' . $manufacturer->title; ?></h3>
					<?php endif; ?>

					<?php if ($this->state->get('show_description')): ?>
						<div class="cat-description">
							<?php echo $description ?>
						</div>
					<?php endif; ?>
					<div class="cat-btn-group">
						<a href="<?php echo $urlC ?>" class="btn btn-small btn-primary">
							<i class="fa fa-chevron-left"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_BACK'); ?></a>
						<?php if ($this->helper->config->get('category_page_view_all_products')): ?>
						<a href="<?php echo $urlP ?>" class="btn btn-small btn-default" id="view-all-products"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_VIEW_ALL_PRODUCTS'); ?>
							<i class="fa fa-chevron-right"></i></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php else: ?>
		<div class="cat-text">
			<div class="image-wrap">
					<span class="product-img bgrollover" style="background-image:url(<?php echo reset($paths) ?>);"
						  data-rollover="<?php echo htmlspecialchars(json_encode($paths)); ?>"></span>
			</div>
			<div class="title-wrap">
				<h1><?php echo $item->title; ?></h1>
				<?php if ($this->state->get('stores.id')) : ?>
					<h3><?php echo 'Store: ' . $seller->store_name ?: $seller->title; ?></h3>
				<?php endif; ?>

				<?php if ($this->state->get('manufacturers.id')) : ?>
					<h3><?php echo 'Manufacturer: ' . $manufacturer->title; ?></h3>
				<?php endif; ?>

				<div class="cat-btn-group">
					<a href="<?php echo $urlC ?>" class="btn btn-default">
						<i class="fa fa-chevron-left"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_BACK'); ?></a>
					<?php if ($this->helper->config->get('category_page_view_all_products')): ?>
					<a href="<?php echo $urlP ?>" class="btn btn-primary" id="view-all-products"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_VIEW_ALL_PRODUCTS'); ?> <i 
						class="fa fa-chevron-right"></i></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
