<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/** @var  PlgSystemSellaciousImporter  $this */
/** @var  stdClass  $displayData */
$template = $displayData;

JHtml::_('script', 'com_importer/select2.js', false, true);
?>
<table class="table table-bordered w100p bg-color-white">
	<tbody>
	<?php
	$createProduct     = $this->helper->access->check('product.create');
	$createVariant     = $this->helper->access->check('variant.create');
	$createCategory    = $this->helper->access->check('category.create');
	$createSplCategory = $this->helper->access->check('splcategory.create');
	$createMfr         = $this->helper->access->check('manufacturer.create');
	$createSeller      = $this->helper->access->check('seller.create');

	if ($createProduct|| $createVariant || $createCategory || $createSplCategory || $createMfr || $createSeller): ?>
		<tr>
			<td class="v-top"><label for="options_create"><?php echo JText::_('COM_IMPORTER_IMPORT_CREATE_OPTIONS_LABEL'); ?></label></td>
			<td>
				<div id="options_create" class="btn-group">

					<?php if ($createCategory): ?>
						<label for="options_create_0" class="checkbox">
							<input type="checkbox" id="options_create_0" name="options[create][]" class="checkbox style-0" value="categories"/>
							<span><?php echo JText::_('COM_IMPORTER_IMPORT_FIELD_CREATE_MISSING_CATEGORIES'); ?></span>
						</label>
					<?php endif; ?>

					<?php if ($createSplCategory): ?>
						<label for="options_create_1" class="checkbox">
							<input type="checkbox" id="options_create_1" name="options[create][]" class="checkbox style-0" value="splcategories"/>
							<span><?php echo JText::_('COM_IMPORTER_IMPORT_FIELD_CREATE_MISSING_SPECIAL_CATEGORIES'); ?></span>
						</label>
					<?php endif; ?>

					<?php if ($createMfr): ?>
						<label for="options_create_2" class="checkbox">
							<input type="checkbox" id="options_create_2" name="options[create][]" class="checkbox style-0" value="manufacturers"/>
							<span><?php echo JText::_('COM_IMPORTER_IMPORT_FIELD_CREATE_MISSING_MANUFACTURERS'); ?></span>
						</label>
					<?php endif; ?>

					<?php if ($createSeller): ?>
						<label for="options_create_3" class="checkbox">
							<input type="checkbox" id="options_create_3" name="options[create][]" class="checkbox style-0" value="sellers"/>
							<span><?php echo JText::_('COM_IMPORTER_IMPORT_FIELD_CREATE_MISSING_SELLERS'); ?></span>
						</label>
					<?php endif; ?>

					<?php if ($createProduct): ?>
						<label for="options_create_4" class="checkbox">
							<input type="checkbox" id="options_create_4" name="options[create][]" class="checkbox style-0" value="products"/>
							<span><?php echo JText::_('COM_IMPORTER_IMPORT_FIELD_CREATE_MISSING_PRODUCTS'); ?></span>
						</label>
					<?php endif; ?>

					<?php if ($createVariant): ?>
						<label for="options_create_5" class="checkbox">
							<input type="checkbox" id="options_create_5" name="options[create][]" class="checkbox style-0" value="variants"/>
							<span><?php echo JText::_('COM_IMPORTER_IMPORT_FIELD_CREATE_MISSING_VARIANTS'); ?></span>
						</label>
					<?php endif; ?>

				</div>
			</td>
		</tr>
	<?php endif; ?>

	<tr>
		<td><label for="options_unique_product"><?php echo JText::_('COM_IMPORTER_IMPORT_UNIQUE_KEY_PRODUCT_LABEL'); ?></label></td>
		<td>
			<select name="options[unique][product]" id="options_unique_product" class="w100p hasSelect2">
				<option value=""><?php echo JText::_('COM_IMPORTER_OPTION_SELECT'); ?></option>
				<option value="PRODUCT_UNIQUE_ALIAS"><?php echo JText::_('COM_IMPORTER_IMPORT_PRODUCT_UNIQUE_ALIAS_OPTION_LABEL'); ?></option>
				<option value="PRODUCT_TITLE"><?php echo JText::_('COM_IMPORTER_IMPORT_PRODUCT_TITLE_OPTION_LABEL'); ?></option>
				<option value="PRODUCT_SKU"><?php echo JText::_('COM_IMPORTER_IMPORT_PRODUCT_SKU_OPTION_LABEL'); ?></option>
				<option value="MFG_ASSIGNED_SKU"><?php echo JText::_('COM_IMPORTER_IMPORT_PRODUCT_MFG_SKU_OPTION_LABEL'); ?></option>
				<?php
				foreach ($template->mapping as $column => $alias):
					if (preg_match('/SPEC_[\d]+/i', $column)):
						?><option value="<?php echo $column ?>"><?php echo $alias ?></option><?php
					endif;
				endforeach;
				?>
			</select>
		</td>
	</tr>

	<?php if ($this->helper->config->get('multi_variant')): ?>
		<tr>
			<td><label for="options_unique_variant"><?php echo JText::_('COM_IMPORTER_IMPORT_UNIQUE_KEY_VARIANT_LABEL'); ?></label></td>
			<td>
				<select name="options[unique][variant]" id="options_unique_variant" class="w100p hasSelect2">
					<option value=""><?php echo JText::_('COM_IMPORTER_OPTION_SELECT'); ?></option>
					<option value="VARIANT_UNIQUE_ALIAS"><?php echo JText::_('COM_IMPORTER_IMPORT_VARIANT_UNIQUE_ALIAS_OPTION_LABEL'); ?></option>
					<option value="VARIANT_TITLE"><?php echo JText::_('COM_IMPORTER_IMPORT_VARIANT_TITLE_OPTION_LABEL'); ?></option>
					<option value="VARIANT_SKU"><?php echo JText::_('COM_IMPORTER_IMPORT_VARIANT_SKU_OPTION_LABEL'); ?></option>
					<?php
					foreach ($template->mapping as $column => $alias):
						if (preg_match('/SPEC_[\d]+_/i', $column)): ?>
							<option value="<?php echo $column ?>"><?php echo $alias ?></option><?php
						endif;
					endforeach;
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="options_variant_independent"><?php echo JText::_('COM_IMPORTER_IMPORT_VARIANT_INDEPENDENT_LABEL'); ?></label></td>
			<td>
				<div id="options_variant_independent" class="btn-group">
					<label for="options_variant_independent_0" class="radio style-0">
						<input type="radio" id="options_variant_independent_0" name="options[variant_independent]" class="radio style-0" value="0" checked/>
						<span><?php echo JText::_('COM_IMPORTER_IMPORT_VARIANT_INDEPENDENT_NO'); ?></span>
					</label>
					<label for="options_variant_independent_1" class="radio style-0">
						<input type="radio" id="options_variant_independent_1" name="options[variant_independent]" class="radio style-0" value="1"/>
						<span><?php echo JText::_('COM_IMPORTER_IMPORT_VARIANT_INDEPENDENT_YES'); ?></span>
					</label>
				</div>
			</td>
		</tr>
	<?php endif; ?>

	<tr><td colspan="2" class="red"><span>* </span><?php echo JText::_('COM_IMPORTER_IMPORT_DEFAULT_UNIQUE_KEY_MESSAGE'); ?></td></tr>
	</tbody>
</table>
