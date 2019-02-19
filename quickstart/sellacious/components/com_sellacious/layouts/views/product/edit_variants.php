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

use Joomla\Utilities\ArrayHelper;

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', array('version' => S_VERSION_CORE));

$fieldsets = $this->form->getFieldsets();
$fields    = $this->form->getFieldset('variants');
$fieldset  = ArrayHelper::getValue($fieldsets, 'variants');

$me = JFactory::getUser();
?>
<script>
	jQuery(function ($) {
		$(document).ready(function () {
			var o = new SellaciousViewProduct.Variant;
			o.init('#tab-variants', '<?php echo JSession::getFormToken() ?>', '<?php echo JUri::root(true) ?>');
		});
	});
</script>
<div class="tab-pane fade" id="tab-variants">
	<div class="pull-right margin-bottom-10 margin-top-10">
		<?php
		if ($this->helper->access->check('variant.create'))
		{
			?><button type="button" class="btn btn-xs btn-success edit-variant" id="add-variant"
					  data-id="0"><i class="fa fa-plus-circle"></i> <?php echo JText::_('COM_SELLACIOUS_ADD_VARIANT'); ?></button><?php
		}
		?>
	</div>
	<fieldset style="margin-bottom: 10px">
		<div class="pull-right padding-bottom-10">
			<button type="button" class="btn btn-xs btn-success" id="btn-apply-variant"><i
					class="fa fa-save"></i> <?php echo JText::_('COM_SELLACIOUS_VARIANT_SAVE'); ?></button>
			<button type="button" class="btn btn-xs btn-primary" id="btn-save-variant"><i
					class="fa fa-check"></i> <?php echo JText::_('COM_SELLACIOUS_VARIANT_SAVE_CLOSE'); ?></button>
			<button type="button" class="btn btn-xs btn-danger" id="btn-close-variant"><i
					class="fa fa-times"></i> <?php echo JText::_('COM_SELLACIOUS_VARIANT_CLOSE_DISCARD'); ?></button>
		</div>
		<div class="clearfix"></div>
		<div id="variant-fields">
			<?php
			foreach ($fields as $field)
			{
				if ($field->hidden):
					echo $field->input;
				else:
					?>
					<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
						<?php
						if ($field->label && (!isset($fieldset->width) || $fieldset->width < 12))
						{
							echo '<div class="form-label col-sm-3 col-md-3 col-lg-2">' . $field->label . '</div>';
							echo '<div class="controls col-sm-9 col-md-9 col-lg-10">' . $field->input . '</div>';
						}
						else
						{
							echo '<div class="controls col-md-12">' . $field->input . '</div>';
						}
						?>
					</div>
					<?php
				endif;
			}
			?>
		</div>
		<div class="clearfix"></div>
	</fieldset>
	<table class="table table-bordered table-striped table-hover" id="variants-list" style="clear: none;">
		<tbody>
		<?php
		if (!empty($this->variants))
		{
			$prices       = $this->item->get('prices.variants');
			$price_mods   = is_array($prices) ? $this->helper->core->arrayAssoc($prices, 'variant_id', 'price_mod') : array();
			$mod_percents = is_array($prices) ? $this->helper->core->arrayAssoc($prices, 'variant_id', 'price_mod_perc') : array();
			$product      = $this->helper->product->getItem($this->item->get('id'));

			foreach ($this->variants as $i => $variant)
			{
				$filter = array(
					'table_name' => 'variants',
					'record_id'  => $variant->id,
					'context'    => 'images',
				);
				$image  = $this->helper->media->getFieldValue($filter, 'path');

				$variant->image    = $this->helper->media->getURL($image, true);
				$variant->price    = ArrayHelper::getValue($price_mods, $variant->id, 0);
				$variant->price_pc = ArrayHelper::getValue($mod_percents, $variant->id, 0);

				// todo: Decouple this access check from layout, probably move to a helper function
				$isOwner     = $variant->owned_by > 0 && ($variant->owned_by == $me->get('id'));
				$allowCreate = $this->helper->access->check('variant.create');
				$allowEdit   = $this->helper->access->check('variant.edit') || ($isOwner && $this->helper->access->check('variant.edit.own'));
				$allowDelete = $this->helper->access->check('variant.delete') || ($isOwner && $this->helper->access->check('variant.delete.own'));

				$data = new stdClass;

				$data->variant          = $variant;
				$variant->product_title = $product->title;
				$variant->product_sku   = $product->local_sku;
				$data->seller_uid       = $this->item->get('seller.seller_uid');
				$data->allow_edit       = $allowEdit;
				$data->allow_create     = $allowCreate;
				$data->allow_delete     = $allowDelete;

				echo JLayoutHelper::render('com_sellacious.product.variant.row', $data);
			}
		}
		?>
		</tbody>
	</table>
</div>
