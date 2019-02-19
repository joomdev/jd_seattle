<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/** @var  object $displayData */
$arg        = $displayData;

/** @var  stdClass  $product */
/** @var  stdClass  $variant */
$product    = $arg->product;
$variant    = $arg->variant;
$seller_uid = $arg->seller_uid;

$me         = JFactory::getUser();
$helper     = SellaciousHelper::getInstance();
$full_title = $product->title . '-' . $variant->title;
$full_sku   = $product->local_sku . '-' . $variant->local_sku;
?>
<tr id="variant-row-<?php echo $variant->id ?>" class="variant-row">
	<td style="width:50px; padding:1px;" class="image-box">
		<img class="image-small" src="<?php echo $variant->image ?>"/>
		<img class="image-large" src="<?php echo $variant->image ?>"/>
	</td>
	<td class="v-top">
		<div class="pull-right">
			<?php
			if (isset($arg->allow_edit) && $arg->allow_edit)
			{
				?>
				<button type="button" class="btn btn-xs btn-success edit-variant"
					data-id="<?php echo $variant->id ?>"><i class="fa fa-edit"></i> Edit</button><?php
			}

			if (isset($arg->allow_create) && $arg->allow_create)
			{
				?>
				<button type="button" class="btn btn-xs btn-info copy-variant"
					data-id="<?php echo $variant->id ?>"><i class="fa fa-copy"></i> Copy</button><?php
			}

			if (isset($arg->allow_delete) && $arg->allow_delete)
			{
				?>
				<button type="button" class="btn btn-xs btn-danger delete-variant"
					data-id="<?php echo $variant->id ?>"><i class="fa fa-times"></i> Delete</button><?php
			}
			?>
		</div>
		<div class="h2 pull-left">
			<strong><?php echo htmlspecialchars($full_title) ?></strong> (<?php echo $full_sku ?>)
		</div>
		<div class="clearfix"></div>
		<div class="variant-specs">
			<?php
			$span = '<span style="margin-right: 15px;"><b>%s</b>: %s</span>';

			foreach ($variant->fields as $field)
			{
				if ($field->field_value)
				{
					echo sprintf($span, htmlspecialchars($field->field_title), htmlspecialchars($field->field_value));
				}
			}
			?>
		</div>
	</td>
	<?php
	if ($seller_uid && ($helper->access->check('product.edit.pricing') || ($helper->access->check('product.edit.pricing.own') && $seller_uid == $me->id)))
	{
		?>
		<td class="v-middle" style="width:160px;">
		<input type="hidden" name="jform[prices][variants][<?php echo $variant->id ?>][variant_id]"
			id="jform_prices_variants_<?php echo $variant->id ?>_variant_id" value="<?php echo $variant->id ?>" />
		<div class="input-group variant_price_mod">
			<?php $c_code = $helper->currency->forSeller($seller_uid, 'code_3') ?>
			<input name="jform[prices][variants][<?php echo $variant->id ?>][price_mod]" class="form-control required"
				value="<?php echo number_format($variant->price, 2, '.', '') ?>" title="" />
			<span class="input-group-addon">
				<span class="onoffswitch">
					<input type="checkbox" name="jform[prices][variants][<?php echo $variant->id ?>][price_mod_perc]"
						id="jform_prices_variants_<?php echo $variant->id ?>_price_mod_perc"
						class="onoffswitch-checkbox sfpprow-margin-type" value="1"
						<?php echo $variant->price_pc ? 'checked' : ''; ?>/>
					<label class="onoffswitch-label"
						for="jform_prices_variants_<?php echo $variant->id ?>_price_mod_perc">
						<span class="onoffswitch-inner" data-swchon-text="%" data-swchoff-text="<?php echo $c_code ?>"></span>
						<span class="onoffswitch-switch"></span>
					</label>
				</span>
			</span>
		</div>
		</td><?php
	}
	?>
</tr>
