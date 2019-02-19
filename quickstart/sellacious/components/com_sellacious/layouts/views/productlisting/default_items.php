<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewProductListing  $this */
$items      = $this->form->getValue('products');
$seller_uid = $this->state->get('productlisting.seller_uid');
$s_currency = $this->helper->currency->forSeller($seller_uid, 'code_3');

$me = JFactory::getUser();
?>
<div class="row padding-10">
	<table id="itemList" class="w100p table table-striped table-bordered table-hover">
		<thead>
		<tr role="row">
			<th class="nowrap" style="width: 50px;">
				<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_IMAGE'); ?>
			</th>
			<th class="nowrap">
				<?php echo JText::_('JGLOBAL_TITLE'); ?>
			</th>
			<th class="nowrap">
				<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_CATEGORY'); ?>
			</th>
			<th class="nowrap center" style="width: 10%;">
				<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_TYPE'); ?>
			</th>
			<th class="nowrap center" style="width: 10%;">
				<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_SKU'); ?>
			</th>
			<th class="nowrap center" style="width: 10%;">
				<?php echo JText::_('COM_SELLACIOUS_PRODUCT_FIELD_MFR_SKU_LABEL'); ?>
			</th>
			<?php if ($seller_uid): ?>
				<?php if (count($items) > 1): ?>
					<th class="center" style="width: 75px;">
						<?php echo JText::_('COM_SELLACIOUS_PRODUCTLISTING_EXPIRY_LABEL'); ?>
					</th>
				<?php endif; ?>
				<th class="center" style="width: 75px;">
					<?php echo JText::_('COM_SELLACIOUS_PRODUCTLISTING_HEADING_PRICE'); ?>
				</th>
				<th class="center" style="width: 50px;">
					<?php echo JText::_('COM_SELLACIOUS_PRODUCTLISTING_HEADING_STOCK'); ?>
				</th>
			<?php endif; ?>
			<th class="nowrap center" colspan="2" style="width: 25px;">
				<?php echo JText::_('JGRID_HEADING_ID'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($items as $i => $item)
		{
			$item      = (object) $item;
			$image_url = $this->helper->product->getImage($item->id, null, true);

			$isOwn    = $item->owned_by == $me->id || $seller_uid == $me->id;
			$canEditS = $this->helper->access->check('product.edit.seller') || ($this->helper->access->check('product.edit.seller.own') && $isOwn);
			$canEditP = $this->helper->access->check('product.edit.pricing') || ($this->helper->access->check('product.edit.pricing.own') && $isOwn);
			?>
			<tr data-id="<?php echo (int) $item->id ?>" class="product-row">
				<td class="image-box">
					<img class="image-small" src="<?php echo $image_url; ?>"/>
					<img class="image-large" src="<?php echo $image_url; ?>"/>
				</td>
				<td>
					<strong><?php echo $this->escape($item->title); ?></strong>
					<?php if (is_array($item->variants)): ?>
					<a href="#" class="hasTooltip variant-info-toggle <?php
					echo count($item->variants) ? '' : 'disabled' ?>"> <?php
						echo JText::plural('COM_SELLACIOUS_PRODUCTLISTING_VARIANT_COUNT_N', count($item->variants)) ?>
						<i class="fa fa-plus-square-o fa-lg"></i></a>
					<?php endif; ?>
				</td>
				<td class="nowrap">
					<?php echo $this->escape($item->category_title); ?>
				</td>
				<td class="nowrap center">
					<?php $langKey = 'COM_SELLACIOUS_PRODUCT_FIELD_TYPE_OPTION_' . strtoupper($item->type);
					echo JFactory::getLanguage()->hasKey($langKey) ? JText::_($langKey) : JText::_('COM_SELLACIOUS_NOT_AVAILABLE'); ?>
				</td>
				<td class="nowrap center">
					<?php echo $this->escape($item->local_sku); ?>
				</td>
				<td class="nowrap center">
					<?php echo $this->escape($item->manufacturer_sku); ?>
				</td>
				<?php if ($seller_uid): ?>
					<?php if (count($items) > 1): ?>
						<td class="nowrap center">
							<?php
							$cat_id = $this->form->getValue('category_id', null, -1);
							$active = $this->helper->listing->getActive($item->id, $seller_uid, $cat_id);

							if ($active->state)
							{
								echo JText::sprintf('COM_SELLACIOUS_PRODUCT_LISTING_EXPIRY_DATE_LABEL', JHtml::_('date', $active->publish_down, 'M d, Y'));
							}
							else
							{
								echo JText::sprintf('COM_SELLACIOUS_PRODUCT_LISTING_INACTIVE_LABEL');
							}
							?>
						</td>
					<?php endif; ?>
					<td class="nowrap">
						<input type="text" name="jform[products][<?php echo $i ?>][price]" id="jform_products_<?php echo $i ?>_price"
							   class="form-control" value="<?php echo $item->price ?>" data-float="2"
							   title="" <?php echo $canEditP ? '' : ' disabled' ?>/>
					</td>
					<td class="nowrap">
						<input type="text" name="jform[products][<?php echo $i ?>][stock]" id="jform_products_<?php echo $i ?>_stock"
							   class="form-control" value="<?php echo $item->stock ?>" data-float="0"
							   title=""  <?php echo $canEditS ? '' : ' disabled' ?>/>
					</td>
				<?php endif; ?>
				<td class="center hidden-phone">
					<input type="hidden" name="jform[products][<?php echo $i ?>][product_id]" id="jform_products_<?php echo $i ?>_product_id"
						class="product-id" value="<?php echo $item->id ?>"/>
					<?php echo (int) $item->id; ?>
				</td>
				<td class="v-top text-right" style="width: 50px;">
					<button type="button" class="btn btn-xs btn-danger del-product">
						<i class="fa fa-times"></i> Remove
					</button>
				</td>
			</tr>
			<?php
			if ($item->variants)
			{
				foreach ($item->variants as $j => $variant)
				{
					$variant = (object) $variant;
					?>
					<tr class="variant-row variant-info-pid-<?php echo (int) $item->id ?> hidden">
						<td style="padding: 1px;"></td>
						<?php if ($seller_uid): ?>
							<td class="nowrap" colspan="<?php echo (count($items) > 1) ? 5 : 4 ?>">
								<input type="hidden" name="jform[products][<?php echo $i ?>][variants][<?php echo $j ?>][variant_id]"
									   id="jform_products_<?php echo $i ?>_variants_<?php echo $j ?>_variant_id"
									   value="<?php echo $variant->id ?>"/>
								<?php echo $this->escape($item->title); ?>&nbsp;
								<strong><?php echo $this->escape($variant->title); ?></strong>
							</td>
							<td class="nowrap">
								<div class="input-group variant_price_mod pull-right" style="width: 60px;">
									<span class="input-group-addon" style="font-size: 18px;">+</span>
									<span class="input-group-addon">
										<span class="onoffswitch">
											<input type="checkbox"
												   name="jform[products][<?php echo $i ?>][variants][<?php echo $j ?>][price_mod_perc]"
												   id="jform_products_<?php echo $i ?>_variants_<?php echo $j ?>_price_mod_perc"
												   class="onoffswitch-checkbox margin-type" value="1"
												<?php echo $variant->price_mod_perc ? 'checked' : ''; ?>
												<?php echo $canEditP ? '' : ' disabled' ?>/>
											<label class="onoffswitch-label"
												for="jform_products_<?php echo $i ?>_variants_<?php echo $j ?>_price_mod_perc">
												<span class="onoffswitch-inner" data-swchon-text="%" data-swchoff-text="<?php echo $s_currency ?>"></span>
												<span class="onoffswitch-switch"></span>
											</label>
										</span>
									</span>
								</div>
							</td>
							<td class="nowrap">
								<input name="jform[products][<?php echo $i ?>][variants][<?php echo $j ?>][price_mod]"
									   id="jform_products_<?php echo $i ?>_variants_<?php echo $j ?>_price_mod"
									   class="form-control" data-float="2" value="<?php echo $variant->price_mod ?>"
									   title="" <?php echo $canEditP ? '' : ' disabled' ?>/>
							</td>
							<td class="nowrap">
								<input type="text" name="jform[products][<?php echo $i ?>][variants][<?php echo $j ?>][stock]"
									   id="jform_products_<?php echo $i ?>_variants_<?php echo $j ?>_stock"
									   class="form-control" data-float="0" value="<?php echo $variant->stock ?>"
									   title="" <?php echo $canEditS ? '' : ' disabled' ?>/>
							</td>
						<?php else: ?>
							<td class="nowrap" colspan="5">
								<?php echo $this->escape($variant->title); ?>
							</td>
						<?php endif; ?>
						<td class="center hidden-phone" colspan="2" style="color:#9f9f9f;">
							(<?php echo (int) $variant->id; ?>)
						</td>
					</tr>
					<?php
				}
			}
		}
		?>
		</tbody>
	</table>
</div>
