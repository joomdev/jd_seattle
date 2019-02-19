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

/** @var  stdClass $tplData */
$item = $tplData;

/** @var  SellaciousViewStores $this */
$logo            = $this->helper->media->getImage('sellers.logo', $item->id, true);
$url             = JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->user_id);
$store           = new Registry($item);
$rateable        = (array) $this->helper->config->get('allow_ratings_for');
$display_ratings = $this->helper->config->get('show_store_rating', 1);

$reviewsUrl = JRoute::_('index.php?option=com_sellacious&view=reviews&seller_uid=' . $store->get('user_id'));
?>
<div class="store-wrap">
	<div class="store-box">
		<div class="image-box">
			<a href="<?php echo $url; ?>">
				<img src="<?php echo $logo; ?>" alt="<?php echo htmlspecialchars($store->get('title'), ENT_COMPAT, 'UTF-8'); ?>">
			</a>
		</div>
		<div class="store-info-box">
			<div class="store-title">
				<a href="<?php echo $url; ?>" title="<?php echo $store->get('title'); ?>">
					<?php echo $store->get('store_name') ?: $store->get('title') ?>
				</a>
			</div>
			<?php if ($this->helper->config->get('show_store_product_count') == '1'): ?>
				<div class="store-product-count">
					<?php echo JText::plural('COM_SELLACIOUS_STORES_PRODUCT_COUNT_N', $store->get('product_count')); ?>
				</div>
			<?php endif; ?>
			<?php if (in_array('seller', $rateable) && ($this->helper->config->get('show_store_rating') == '1')): ?>
				<?php $stars = round($store->get('rating.rating', 0) * 2); ?>
				<div class="product-rating rating-stars star-<?php echo $stars ?>">
					<a href="<?php echo $reviewsUrl ?>">
						<?php echo number_format($store->get('rating.rating', 0), 1) ?>
						<?php echo '<span> – </span>';
						echo JText::plural('COM_SELLACIOUS_RATINGS_COUNT_N', $store->get('rating.count')); ?>
					</a>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
		</div>
	</div>
</div>
