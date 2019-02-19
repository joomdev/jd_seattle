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
$registry     = $this->registry;
$msgUndefined = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');
?>
<fieldset class="w100p users_profile_address form-horizontal">
	<legend>
		<?php echo JText::_('COM_SELLACIOUS_USER_FIELDSET_ADDRESSES'); ?>
	</legend>
	<?php if ($this->getShowOption('address')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_NAME_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('address.name') ?: $msgUndefined; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_COMPANY_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('address.company') ?: $msgUndefined; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_PO_BOX_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('address.po_box') ?: $msgUndefined; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_ADDRESS_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('address.address') ?: $msgUndefined; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_LANDMARK_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('address.landmark') ?: $msgUndefined; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_COUNTRY_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $this->escape($this->helper->location->getFieldValue($registry->get('address.country'), 'title')) ?: $msgUndefined; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_STATE_LOC_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo htmlspecialchars($this->helper->location->getFieldValue($registry->get('address.state_loc'), 'title')) ?: $msgUndefined; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_DISTRICT_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo htmlspecialchars($this->helper->location->getFieldValue($registry->get('address.district'), 'title')) ?: $msgUndefined; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_ZIP_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('address.zip') ?: $msgUndefined; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_MOBILE_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('address.mobile') ?: $msgUndefined; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_RESIDENTIAL_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php $res = $registry->get('address.residential');

			if ($res === null)
			{
				echo $msgUndefined;
			}
			else
			{
				echo $res ? JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_TYPE_OPTION_RESIDENTIAL')
					: JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_TYPE_OPTION_OFFICE');
			}
			?>
		</div>
	</div>
	<?php endif; ?>
</fieldset>
