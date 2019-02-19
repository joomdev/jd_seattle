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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

$helper = SellaciousHelper::getInstance();

/** @var stdClass $displayData */
$field     = $displayData;
$precision = $field->precision;
$rowIndex  = $field->row_index;
$value     = isset($field->value[$rowIndex]) ? (array) $field->value[$rowIndex] : array();

$min     = ArrayHelper::getValue($value, 'min', 0, 'float');
$max     = ArrayHelper::getValue($value, 'max', 0, 'float');
$unit    = ArrayHelper::getValue($value, 'u', 0, 'int');
$country = ArrayHelper::getValue($value, 'country', 0, 'int');
$state   = ArrayHelper::getValue($value, 'state', 0, 'int');
$zip     = ArrayHelper::getValue($value, 'zip', '', 'string');
$price   = ArrayHelper::getValue($value, 'price', 0, 'float');

try
{
	$country = $helper->location->getTitle($country);
}
catch (Exception $e)
{
	$country = '';
}

try
{
	$state = $helper->location->getTitle($state);
}
catch (Exception $e)
{
	$state = '';
}
?>
<tr role="row" id="<?php echo $field->id ?>_sfssrow_<?php echo $rowIndex ?>" class="sfssrow">
	<td class="nowrap text-center" data-float="<?php echo $precision ?>">
		<?php echo $min ?>
	</td>
	<td class="nowrap text-center" data-float="<?php echo $precision ?>">
		<?php echo $max ?>
	</td>
	<td class="nowrap text-center">
		<?php echo $country; ?>
	</td>
	<td class="nowrap text-center">
		<?php echo $state; ?>
	</td>
	<td class="nowrap text-center">
		<?php echo $zip; ?>
	</td>
	<td class="nowrap text-center" data-float="2">
		<?php echo $price ?>
		<?php if ($field->unitToggle && $unit): ?>
			<?php echo JText::_('COM_SELLACIOUS_FIELD_SHIPPING_RATE_PER_UNIT_SUFFIX') ?>
		<?php endif; ?>
	</td>
</tr>
