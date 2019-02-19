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

/** @var  SellaciousViewProduct  $this */
/** @var  stdClass[]  $tplData */
$items = $tplData;
?>
<div class="packages-items">
	<?php
	foreach ($items as $item):

		$paths = $this->helper->product->getImages($item->product_id, $item->variant_id);
		$code  = $this->helper->product->getCode($item->product_id, $item->variant_id, $this->item->get('seller_uid'));
		$url   = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);

		$link_detail = $this->helper->config->get('product_detail_page');
		?>
		<div class="product-box">
			<div class="image-box">
				<a href="<?php echo $link_detail ? $url : 'javascript:void(0);' ?>">
					<img src="<?php echo reset($paths) ?>" title="<?php echo htmlspecialchars($item->product_title) ?>"/>
				</a>
			</div>
			<div class="product-info-box">
				<h3 class="product-title">
					<a href="<?php echo $link_detail ? $url : 'javascript:void(0);' ?>">
						<?php echo $item->product_title;
						echo $item->variant_title ? ' - <small>' . $item->variant_title . '</small>' : ''; ?></a>
				</h3>
				<?php if (($item->product_sku) || ($item->variant_sku)): ?>
					<div class="product-sku-info">
						<strong><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_SKU'); ?>:</strong> <?php echo $item->product_sku; ?>
						<?php if ($item->variant_sku): ?>
							- <small><?php echo $item->variant_sku; ?></small>
						<?php endif; ?>
					</div>
				<?php endif;?>
			</div>
			<div class="clearfix"></div>
		</div><?php
	endforeach; ?>
</div>
