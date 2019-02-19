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

/** @var  object  $displayData */
$address = $displayData;
$helper  = SellaciousHelper::getInstance();
?>
<tr id="address-row-<?php echo $address->id ?>" class="address-row">
	<td class="v-top">
		<div class="pull-right">
			<button type="button" class="btn btn-xs btn-success edit-address"
					data-id="<?php echo $address->id ?>"><i class="fa fa-edit"></i> Edit</button>
			<button type="button" class="btn btn-xs btn-info copy-address"
					data-id="<?php echo $address->id ?>"><i class="fa fa-copy"></i> Copy</button>
			<button type="button" class="btn btn-xs btn-danger delete-address"
					data-id="<?php echo $address->id ?>"><i class="fa fa-times"></i> Delete</button>
		</div>
		<?php
		$addr     = preg_replace('/(\s*,\s*)+|\s*,?\n/', ', ', $address->address);
		$landmark = $address->landmark;
		$zip      = $address->zip;
		$district = $helper->location->getFieldValue($address->district, 'title');
		$state    = $helper->location->getFieldValue($address->state_loc, 'title');
		$country  = $helper->location->getFieldValue($address->country, 'title');
		?>
		<div id="address-viewer" class="pull-left nowrap">
			<div id="address-text">
				<span class="address_name"><?php echo htmlspecialchars($address->name) ?></span>
				<span class="address_mobile">(<i class="fa fa-mobile-phone fa-lg"></i> <?php echo $address->mobile ?>)</span><br/>
				<span class="address_address"><?php echo htmlspecialchars($addr) ?></span>,
				<span class="address_landmark"><?php echo htmlspecialchars($landmark) ?></span>,
				<span class="address_district"><?php echo htmlspecialchars($district) ?></span>,
				<span class="address_state_loc"><?php echo htmlspecialchars($state) ?></span>,
				<span class="address_zip"><?php echo htmlspecialchars($zip) ?> </span> -
				<span class="address_country"><?php echo htmlspecialchars($country) ?></span><br/>
			</div>
		</div>
	</td>
</tr>
