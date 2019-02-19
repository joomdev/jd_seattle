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
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die;

/** @var  array  $displayData */
$field    = $displayData['field'];
$listed   = $displayData['listed'];
$unlisted = $displayData['unlisted'];

JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/field.productsellersgrid.js', array('version' => S_VERSION_CORE, 'relative' => true));

JText::script('COM_SELLACIOUS_PRODUCT_SELLER_SWITCH_WARNING_EDIT_LOST');

$helper = SellaciousHelper::getInstance();
$config = ConfigHelper::getInstance('com_sellacious');

$freeListing = $config->get('free_listing');
?>
<div id="<?php echo $field->id ?>_wrapper" class="w100p margin-bottom-10 padding-5 jff-productsellersgrid">

	<input type="hidden" name="<?php echo $field->name ?>" class="jff-psg-input"
	       id="<?php echo $field->id ?>" value="<?php echo (int) $field->value ?>" readonly="readonly"/>

	<?php if ($listed): ?>
	<h5 class="strong"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_LISTED') ?></h5>
	<table class="table table-bordered table-striped">
		<thead>
		<tr>
			<th><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_NAME') ?></th>
			<th><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_STORE') ?></th>
			<th class="center" style="width: 120px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_SELLING') ?></th>
			<th class="center" style="width: 120px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_STOCK') ?></th>
			<?php if(!$freeListing): ?>
				<th class="center" style="width: 120px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_EXPIRATION') ?></th>
			<?php endif; ?>
			<th class="center" style="width: 120px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_LINK') ?></th>
			<th class="center" style="width: 120px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_EDIT') ?></th>
		</tr>
		</thead>
		<?php foreach ($listed as $seller): ?>
			<tr>
				<td><?php echo $seller->name ?: $seller->company ?></td>
				<td><?php echo $seller->store_name ?></td>
				<td class="center"><?php echo $seller->is_selling && !$seller->block ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>' ?></td>
				<td class="center"><?php echo (int) $seller->stock ?><?php echo $seller->over_stock ? ' + ' . (int) $seller->over_stock : '' ?></td>
				<?php if(!$freeListing): ?>
					<td class="center">
						<?php echo $seller->expiration ? JHtml::_('date', $seller->expiration, 'M d, Y') : '<i class="fa fa-times-circle"></i>'; ?>
					</td>
				<?php endif; ?>
				<td class="center">
					<?php if ($seller->is_selling): ?>
						<?php $code = $helper->product->getCode($field->productId, 0, $seller->id); ?>
						<a target="_blank" href="<?php echo JRoute::_('../index.php?option=com_sellacious&view=product&p=' . $code) ?>"><i
								class="fa fa-external-link-square"></i></a>
					<?php else: ?>
						<i class="fa fa-times-circle"></i>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php if ($field->value == $seller->id): ?>
					<button type="button" class="btn btn-primary disabled w100p"
					        data-seller-uid="<?php echo (int) $seller->id ?>"><?php
						echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_BTN_SWITCH_CURRENT') ?></button>
					<?php else: ?>
						<button type="button" class="btn btn-primary jff-psg-switch-btn w100p"
					            data-seller-uid="<?php echo (int) $seller->id ?>"><?php
							echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_BTN_SWITCH_LISTED') ?></button>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>

	<?php if ($unlisted): ?>
	<br>
	<h5 class="strong"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_UNLISTED') ?></h5>
	<table class="table table-bordered table-striped">
		<thead>
		<tr>
			<th><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_NAME') ?></th>
			<th><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_STORE') ?></th>
			<th class="center" style="width: 120px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_SELLING') ?></th>
			<th class="center" style="width: 120px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_STOCK') ?></th>
			<?php if(!$freeListing): ?>
				<th class="center" style="width: 120px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_EXPIRATION') ?></th>
			<?php endif; ?>
			<th class="center" style="width: 120px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_LINK') ?></th>
			<th class="center" style="width: 120px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_HEADING_EDIT') ?></th>
		</tr>
		</thead>
		<?php foreach ($unlisted as $seller): ?>
			<tr>
				<td><?php echo $seller->name ?: $seller->company ?></td>
				<td><?php echo $seller->store_name ?></td>
				<td class="center"><i class="fa fa-times"></i></td>
				<td class="center"><i class="fa fa-times-circle"></i></td>
				<?php if(!$freeListing): ?>
					<td class="center"><i class="fa fa-times-circle"></i></td>
				<?php endif; ?>
				<td class="center"><i class="fa fa-times-circle"></i></td>
				<td class="center">
					<?php if ($field->value == $seller->id): ?>
						<button type="button" class="btn btn-primary disabled w100p"
								data-seller-uid="<?php echo (int) $seller->id ?>"><?php
							echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_BTN_SWITCH_CURRENT') ?></button>
					<?php else: ?>
						<button type="button" class="btn btn-primary jff-psg-switch-btn w100p"
								data-seller-uid="<?php echo (int) $seller->id ?>"><?php
							echo JText::_('COM_SELLACIOUS_PRODUCT_SELLER_BTN_SWITCH_UNLISTED') ?></button>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>

</div>
