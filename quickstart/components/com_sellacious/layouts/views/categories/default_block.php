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

/** @var  stdClass  $tplData */
$item   = $tplData;

/** @var  SellaciousViewCategories $this */

$paths              = $this->helper->category->getImages($item->id, true);
$suffixStore        = $this->state->get('stores.id') ? '&store_id=' . $this->state->get('stores.id') : '';
$suffixManufacturer = $this->state->get('manufacturers.id') ? '&manufacturer_id=' . $this->state->get('manufacturers.id') : '';
$url                = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $item->id . $suffixStore . $suffixManufacturer);

$catCols = $this->helper->config->get('category_cols', 4);
$sellCats = '';
if ($catCols == '4'):
	$sellCats = 'sell-col-md-3 sell-col-sm-4 sell-col-xs-6';
elseif ($catCols == '3'):
	$sellCats = 'sell-col-sm-4 sell-col-xs-6';
elseif ($catCols == '2'):
	$sellCats = 'sell-col-xs-6';
elseif ($catCols == 'auto' ):
	$sellCats = 'auto-adjust';
endif;

?>
<div class="category-cols <?php echo $sellCats; ?>">
	<div class="category-box" data-rollover="container">
		<a href="<?php echo $url ?>">
			<h6><?php echo $item->title; ?></h6>
			<div class="image-box">
				<span class="product-img bgrollover" style="background-image:url(<?php echo reset($paths) ?>);"
			 		data-rollover="<?php echo htmlspecialchars(json_encode($paths)); ?>"></span>
			</div>
		</a>
		<?php if (isset($item->product_count) || isset($item->subcat_count)): ?>
		<div class="item-counts-strip">
			<div class="tip-left"><?php
				if (isset($item->product_count))
				{
					echo JText::plural('COM_SELLACIOUS_CATEGORY_PRODUCT_COUNT_N', $item->product_count);
				}
			?></div>
			<div class="tip-right"><?php
				if (isset($item->subcat_count))
				{
					echo JText::plural('COM_SELLACIOUS_CATEGORY_SUBCATEGORIES_COUNT_N', $item->subcat_count);
				}
			?></div>
		</div>
		<?php endif; ?>
	</div>
</div>
