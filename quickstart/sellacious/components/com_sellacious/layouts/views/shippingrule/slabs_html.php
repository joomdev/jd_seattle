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

/** @var  \stdClass[]  $tplData */
$slabs = $tplData;

$currency  = $this->helper->currency->getGlobal('code_3');
$ruleTitle = $this->state->get('shippingrule.title', 'shipping-slabs');
?>
<?php if (count($slabs) > 0): ?>
	<a style="position:fixed; margin: 10px;"
	   href="<?php echo JRoute::_('index.php?option=com_sellacious&view=shippingrule&layout=slabs&tmpl=component&format=csv') ?>"
	   class="btn btn-primary btn-sm pull-right"><i class="fa fa-download"></i> <?php
		echo JText::_('COM_SELLACIOUS_BTN_EXPORT') ?></a>
<?php endif; ?>

<h1 class="center"><?php echo $ruleTitle ?></h1>

<table class="table table-bordered table-striped table-hover table-nopadding shipping-slabs-table" style="width: auto; margin: auto">
	<thead>
	<tr role="row" class="cursor-pointer v-top">
		<th class="nowrap text-center" style="width: 150px;">
			<?php echo JText::_('COM_SELLACIOUS_SHIPPINGSLABS_FIELD_GRID_HEADING_RANGE_FROM') ?>
		</th>
		<th class="nowrap text-center" style="width: 150px;">
			<?php echo JText::_('COM_SELLACIOUS_SHIPPINGSLABS_FIELD_GRID_HEADING_RANGE_TO') ?>
		</th>

		<th class="nowrap text-center" style="min-width: 150px;">
			<?php echo JText::_('COM_SELLACIOUS_SHIPPINGSLABS_FIELD_GRID_HEADING_COUNTRY') ?>
		</th>
		<th class="nowrap text-center" style="min-width: 150px;">
			<?php echo JText::_('COM_SELLACIOUS_SHIPPINGSLABS_FIELD_GRID_HEADING_STATE') ?>
		</th>
		<th class="nowrap text-center" style="min-width: 150px;">
			<?php echo JText::_('COM_SELLACIOUS_SHIPPINGSLABS_FIELD_GRID_HEADING_ZIP') ?>
		</th>

		<th class="nowrap text-center" style="width: 150px;">
			<?php echo JText::_('COM_SELLACIOUS_SHIPPINGSLABS_FIELD_GRID_HEADING_PRICE') ?>
			<small>(<?php echo $currency ?>)</small>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($slabs as $i => $record)
	{
		$record = (array) $record;

		$min     = ArrayHelper::getValue($record, 'min', 0, 'float');
		$max     = ArrayHelper::getValue($record, 'max', 0, 'float');
		$unit    = ArrayHelper::getValue($record, 'u', 0, 'int');
		$country = ArrayHelper::getValue($record, 'country', 0, 'int');
		$state   = ArrayHelper::getValue($record, 'state', 0, 'int');
		$zip     = ArrayHelper::getValue($record, 'zip', '', 'string');
		$price   = ArrayHelper::getValue($record, 'price', 0, 'float');

		try
		{
			$country = $this->helper->location->getTitle($country);
		}
		catch (Exception $e)
		{
			$country = '';
		}

		try
		{
			$state = $this->helper->location->getTitle($state);
		}
		catch (Exception $e)
		{
			$state = '';
		}
		?>
		<tr role="row" class="sfssrow">
			<td class="nowrap text-center">
				<?php echo $min ?>
			</td>
			<td class="nowrap text-center">
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
				<?php if ($unit): ?>
					<?php echo JText::_('COM_SELLACIOUS_FIELD_SHIPPING_RATE_PER_UNIT_SUFFIX') ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>
