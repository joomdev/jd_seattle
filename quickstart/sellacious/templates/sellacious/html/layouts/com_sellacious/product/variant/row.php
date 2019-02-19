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
$arg = $displayData;

/** @var  stdClass $variant */
$variant    = $arg->variant;
$seller_uid = $arg->seller_uid;

$me         = JFactory::getUser();
$helper     = SellaciousHelper::getInstance();
$full_title = $variant->product_title . '-' . $variant->title;
$full_sku   = $variant->product_sku . '-' . $variant->local_sku;
?>
<tr id="variant-row-<?php echo $variant->id ?>" class="variant-row">
	<td style="width:50px; padding:1px;" class="image-box">
		<img class="image-small" src="<?php echo $variant->image ?>" />
		<img class="image-large" src="<?php echo $variant->image ?>" />
	</td>
	<td class="v-top">
		<div class="pull-right">
			<?php
			if (isset($arg->allow_edit) && $arg->allow_edit)
			{
				?><button type="button" class="btn btn-xs btn-success edit-variant"
				data-id="<?php echo $variant->id ?>"><i class="fa fa-edit"></i> Edit</button><?php
			}

			if (isset($arg->allow_create) && $arg->allow_create)
			{
				?><button type="button" class="btn btn-xs btn-info copy-variant"
				data-id="<?php echo $variant->id ?>"><i class="fa fa-copy"></i> Copy</button><?php
			}

			if (isset($arg->allow_delete) && $arg->allow_delete)
			{
				?><button type="button" class="btn btn-xs btn-danger delete-variant"
				data-id="<?php echo $variant->id ?>"><i class="fa fa-times"></i> Delete</button><?php
			}
			?>
		</div>
		<div class="h2 pull-left">
			<strong><?php echo htmlspecialchars($full_title) ?></strong> (<?php echo $full_sku ?>)
			&nbsp;<small>(<?php echo $helper->product->getCode($variant->product_id, $variant->id, $seller_uid); ?>)</small>
		</div>
		<div class="clearfix"></div>
		<div class="variant-specs">
			<?php
			$span = '<span style="margin-right: 15px;"><b>%s</b>: %s</span>';

			foreach ($variant->fields as $field)
			{
				$value = $helper->field->renderValue($field->field_value, $field->field_type, $field);

				if ($value)
				{
					echo sprintf($span, $field->field_title, $value);
				}
			}
			?>
		</div>
	</td>
	<?php
	if ($seller_uid && ($helper->access->check('product.edit.pricing') || ($helper->access->check('product.edit.pricing.own') && $seller_uid == $me->id)))
	{
		?>
		<td class="v-middle nowrap text-right" style="width:70px;">
			<?php
			$c_code = $helper->currency->forSeller($seller_uid, 'code_3');
			$amount = number_format($variant->price, 2, '.', '');

			echo $variant->price_pc ? "$amount %" : "$amount $c_code";
			?>
		</td>
		<?php
	}
	?>
</tr>
