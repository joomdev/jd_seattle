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

/** @var array $tplData */
list($i, $item, $isOwn, $canEditS, $canEditP) = $tplData;

/** @var  SellaciousViewProducts $this */
$s_currency   = $this->helper->currency->forSeller($item->seller_uid, 'code_3');
$multi_seller = $this->helper->config->get('multi_seller', 0);

$j  = 0;

foreach ($item->variants as $vid => $variant)
{
	$j++;
	$variant = (object) $variant;
	?>
	<tr class="variant-row" data-row="<?php echo $i ?>" data-variant="<?php echo $j ?>">
		<td style="padding: 1px;"></td>
		<td class="nowrap">
			<?php echo $this->escape($item->local_sku); ?> <strong><?php echo $this->escape($variant->local_sku); ?></strong>
		</td>
		<td class="nowrap" colspan="<?php echo 2 + ($multi_seller ? 1 : 0) ?>">
			<input type="hidden" name="jform[<?php echo $i ?>][variants][<?php echo $j ?>][seller_uid]"
				   id="jform_<?php echo $i ?>_variants_<?php echo $j ?>_seller_uid" value="<?php echo $item->seller_uid ?>">
			<input type="hidden" name="jform[<?php echo $i ?>][variants][<?php echo $j ?>][variant_id]"
				   id="jform_<?php echo $i ?>_variants_<?php echo $j ?>_variant_id" value="<?php echo $variant->id ?>">
			<?php echo $this->escape($item->title); ?> <strong><?php echo $this->escape($variant->title); ?></strong>
		</td>
		<td>
			<input class="inputbox product-price" value="<?php echo number_format($item->price->product_price, 2, '.', '') ?>"
				   data-float="2" title="" disabled/>
		</td>
		<td class="nowrap" style="width:190px;">
			<div class="controls">
				<div class="input-group">
				<span class="input-group-addon" style="font-size: 18px;">+</span>
				<input name="jform[<?php echo $i ?>][variants][<?php echo $j ?>][price_mod]" data-field="margin"
					   id="jform_<?php echo $i ?>_variants_<?php echo $j ?>_price_mod" class="form-control tiny-input margin" data-float="2"
					   value="<?php echo number_format($variant->price_mod, 2, '.', '') ?>" title="" <?php echo $canEditP ? '' : ' disabled="disabled"'; ?>/>
				<span class="input-group-addon">
					<span class="onoffswitch">
						<input type="checkbox" name="jform[<?php echo $i ?>][variants][<?php echo $j ?>][price_mod_perc]"
							   id="jform_<?php echo $i ?>_variants_<?php echo $j ?>_price_mod_perc" data-field="margin-type"
							   class="onoffswitch-checkbox margin-type" value="1" <?php echo $canEditP ? '' : ' disabled="disabled"'; ?>
							<?php echo $variant->price_mod_perc ? 'checked' : ''; ?>/>
						<label class="onoffswitch-label" for="jform_<?php echo $i ?>_variants_<?php echo $j ?>_price_mod_perc">
							<span class="onoffswitch-inner" data-swchon-text="%" data-swchoff-text="<?php echo $s_currency ?>"></span>
							<span class="onoffswitch-switch"></span>
						</label>
					</span>
				</span>
			</div>
			</div>
		</td>
		<td class="text-right">
			<?php $variant->price = $variant->price_mod_perc ? $item->price->product_price * $variant->price_mod / 100.0 : $variant->price_mod; ?>
			<input class="inputbox basic-price" data-amount="<?php echo (float) $item->price->product_price ?>"
				   value="<?php echo (float) $item->price->product_price + (float) $variant->price ?>" data-float="2" title="" disabled/>
		</td>
		<?php if ($this->helper->config->get('stock_management', 'product') != 'global'): ?>
		<td class="nowrap" style="width:70px;">
			<div class="controls">
			<input type="text" name="jform[<?php echo $i ?>][variants][<?php echo $j ?>][stock]"
				   id="jform_<?php echo $i ?>_variants_<?php echo $j ?>_stock" data-field="stock" class="form-control tiny-input stock"
				   data-float="0" value="<?php echo $variant->stock ?>" title="" <?php echo $canEditS ? '' : ' disabled="disabled"'; ?>/>
			</div>
		</td>
		<td class="nowrap" style="width:70px;">
			<div class="controls">
			<input type="text" name="jform[<?php echo $i ?>][variants][<?php echo $j ?>][over_stock]"
				   id="jform_<?php echo $i ?>_variants_<?php echo $j ?>_over_stock"
				   data-field="over-stock" class="form-control tiny-input over-stock"
				   data-float="0" value="<?php echo $variant->over_stock ?>" title="" <?php echo $canEditS ? '' : ' disabled="disabled"'; ?>/>
			</div>
		</td>
		<?php endif; ?>
		<td class="center hidden-phone" style="color: #9f9f9f;">(<?php echo (int) $variant->id; ?>)</td>
	</tr>
	<?php
}
