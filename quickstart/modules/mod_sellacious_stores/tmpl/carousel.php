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

JHtml::_('jquery.framework');
JHtml::_('stylesheet', JUri::root() . 'modules/mod_sellacious_stores/assets/css/owl.carousel.min.css');

JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_stores/style.css', null, true);

?>
<div class="mod-sellacious-stores stores-carousel-layout <?php echo $class_sfx; ?>">
	<div class="stores-carousel owl-carousel" id="stores-carousel<?php echo $module->id; ?>">
		<?php foreach ($stores AS $store):

			$store->profile = $helper->profile->getItem(array('user_id' => $store->user_id));
			$store->rating = $helper->rating->getSellerRating($store->user_id);
			$store->product_count = ModSellaciousStores::getSellerProductCount($store->user_id);
			$store = new Registry($store);

			$logo     = $helper->media->getImage('sellers.logo', $store->get('id'));
			$rateable = (array) $helper->config->get('allow_ratings_for');

			$url = 'index.php?option=com_sellacious&view=store&layout=store&id=' . $store->get('user_id');

			?>

			<div class="item">
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
</div>

<?php
if ($autoplayopt == "1"):
	$autoplayvalue = 'true';
else:
	$autoplayvalue = 'false';
endif;
?>

<script type="text/javascript">
	jQuery(document).ready(function ($) {

		var owl = $('#stores-carousel<?php echo $module->id ?>');
		owl.owlCarousel({
			nav: true,
			navText: [
				"<i class='fa fa-angle-left'></i>",
				"<i class='fa fa-angle-right'></i>"
			],
			rewind: true,
			autoplay: <?php echo $autoplayvalue; ?>,
			autoplayTimeout: <?php echo $autoplayspeed; ?>,
			autoplayHoverPause: true,
			margin: <?php echo $gutter; ?>,
			responsive: {
				0: {
					items: <?php echo $responsive0to500; ?>
				},
				500: {
					items: <?php echo $responsive500; ?>
				},
				992: {
					items: <?php echo $responsive992; ?>
				},
				1200: {
					items: <?php echo $responsive1200; ?>
				},
				1400: {
					items: <?php echo $responsive1400; ?>
				}
			}
		});
	});
</script>
<?php
JHtml::_('script', JUri::root() . 'modules/mod_sellacious_stores/assets/js/owl.carousel.js');
?>
