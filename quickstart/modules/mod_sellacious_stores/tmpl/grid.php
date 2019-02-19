<?php
/**
 * @version     1.6.1
 * @package     Sellacious Seller Stores Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;

JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_stores/style.css', null, true);

?>
<div class="mod-sellacious-stores stores-grid-layout <?php echo $class_sfx; ?>">
	<?php foreach ($stores AS $store):

		$store->profile = $helper->profile->getItem(array('user_id' => $store->user_id));
		$store->rating = $helper->rating->getSellerRating($store->user_id);
		$store->product_count = ModSellaciousStores::getSellerProductCount($store->user_id);
		$store = new Registry($store);

		$logo     = $helper->media->getImage('sellers.logo', $store->get('id'));
		$rateable = (array) $helper->config->get('allow_ratings_for');

		$url = 'index.php?option=com_sellacious&view=store&layout=store&id=' . $store->get('user_id');

		?>

		<div class="store-wrap-grid">
			<div class="store-box">
				<div class="image-box">
					<a href="<?php echo $url; ?>">
						<img src="<?php echo $logo; ?>" title="<?php echo htmlspecialchars($store->get('title'), ENT_COMPAT, 'UTF-8'); ?>">
					</a>
				</div>
				<div class="store-info-box">
					<div class="store-title">
						<a href="<?php echo $url; ?>" title="<?php echo $store->get('title'); ?>">
							<?php echo $store->get('store_name') ?: $store->get('title') ?>
						</a>
					</div>
					<?php if ($display_product_count == '1'): ?>
						<div class="store-product-count">
							<?php echo JText::plural('MOD_SELLACIOUS_STORES_PRODUCT_COUNT_N', $store->get('product_count')); ?>
						</div>
					<?php endif; ?>
					<?php if (in_array('seller', $rateable) && $display_ratings == '1'): ?>
						<?php $stars = round($store->get('rating.rating', 0) * 2); ?>
						<div class="product-rating rating-stars star-<?php echo $stars ?>">
							<?php echo number_format($store->get('rating.rating', 0), 1) ?>
							<?php echo '<span> – </span>';
							echo JText::plural('COM_SELLACIOUS_RATINGS_COUNT_N', $store->get('rating.count')); ?>
						</div>
					<?php endif; ?>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="clearfix"></div>
</div>
