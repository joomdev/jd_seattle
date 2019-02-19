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

/** @var  stdClass  $displayData */
$formfield = $displayData;
$helper = SellaciousHelper::getInstance();
?>
<div class="bg-color-white w100p" style="padding: 1px; border: 1px solid #eee;">
	<table id="<?php echo $formfield->id; ?>" class="table table-bordered table-striped table-hover w100p">
		<thead>
		<tr class="v-bottom">
			<th><?php echo JText::_('COM_SELLACIOUS_PRODUCT_VARIANT_HEADING_LABEL') ?></th>
			<?php if ($helper->config->get('stock_management', 'product') == 'product'): ?>
			<th class="text-center"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_VARIANT_STOCK_LABEL') ?></th>
			<th class="text-center"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_VARIANT_OVER_STOCK_LABEL') ?></th>
			<?php endif; ?>
			<th class="text-center"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_VARIANT_PRICE_LABEL') ?></th>
		</tr>
		</thead>
		<tbody><?php
		$layout  = 'com_sellacious.formfield.variantprices.rowtemplate';
		$options = array('client' => 2, 'debug' => 0);

		foreach ($formfield->variants as $variant)
		{
			$formfield->variant = $variant;

			echo JLayoutHelper::render($layout, $formfield, '', $options);
		}
		?></tbody>
		<tfoot class="hidden">
		<tr>
			<td><span class="strong"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_VARIANT_NO_VARIANT_FOR_PRICING') ?></span></td>
		</tr>
		</tfoot>
	</table>
</div>
