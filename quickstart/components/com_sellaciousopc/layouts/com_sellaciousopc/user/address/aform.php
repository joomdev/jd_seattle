<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/** @var stdClass $displayData */
$address = new Joomla\Registry\Registry($displayData);
$address->set('address', trim($address->get('address'), '\t\n\r\0\x0B\x0C, '));
$address->def('id', 0);

$aform 	 = $address->get("aform", "aform");

$i       = (int) $address->get('id');
$options = array('control' => $aform . $i, 'name' => 'com_sellacious.address.' . $aform . $i);
$helper  = SellaciousHelper::getInstance();
$form    = $helper->user->getAddressForm($options, $address->toArray());
?>
<form class="opc-address-form">
	<div class="opc-address-form-content">

		<?php foreach ($form->getFieldset() as $field): ?>
			<?php if ($field->fieldname == 'id'): ?>
				<?php echo $field->input ?>
			<?php else: ?>
				<div class="control-group">
					<div class="control-label text-left"><?php echo $field->label ?></div>
					<div class="controls"><?php echo $field->input ?></div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

	</div>
	<?php if($aform == "sform")
		{
			?>
			<input type="hidden" name="set_shipping" value="1">
		<?php
		}
	else if($aform == "bform")
		{
			?>
			<input type="hidden" name="set_billing" value="1">
			<?php
		}
	?>
</form>
