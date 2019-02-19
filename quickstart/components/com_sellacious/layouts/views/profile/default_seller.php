<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

/** @var  \Joomla\Registry\Registry  $registry */
$registry = $this->registry;

$msgUndefined = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');
?>
<fieldset class="w100p users_profile_seller form-horizontal">
	<legend>
		<?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELDSET_SELLER'); ?>
	</legend>
	<?php if ($this->getShowOption('seller.title')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_SELLER_NAME_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('seller.title') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('seller.store_name')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_STORE_NAME_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('seller.store_name') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('seller.store_address')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_STORE_ADDRESS_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('seller.store_address') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('seller.currency')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_CURRENCY_LISTING_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('seller.currency') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ($this->getShowOption('seller.store_location')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_SELLER_STORE_LOCATION_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('seller.store_location') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
</fieldset>
