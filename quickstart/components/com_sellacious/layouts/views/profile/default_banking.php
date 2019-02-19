<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/** @var  Registry  $bankinfo */
/** @var  Registry  $taxinfo */
$bankinfo = $this->registry->extract('profile.bankinfo') ?: new Registry;
$taxinfo  = $this->registry->extract('profile.taxinfo') ?: new Registry;

$msgUndefined = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');
?>
<fieldset class="w100p users_profile_bank_tax_info form-horizontal">
	<legend>
		<?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELDSET_BANK_TAX_INFO'); ?>
	</legend>
	<?php if ($this->getShowOption('profile.bankinfo.name')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BANKNAME_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $bankinfo->get('name') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.bankinfo.country')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BANKCOUNTRY_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $bankinfo->get('country') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.bankinfo.branch')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BANKBRANCH_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $bankinfo->get('branch') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.bankinfo.beneficiary')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BANKBENEFICIARY_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $bankinfo->get('beneficiary') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.bankinfo.accountnumber')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BANKACCNO_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $bankinfo->get('accountnumber') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.bankinfo.code')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BANKCODE_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $bankinfo->get('code') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.bankinfo.micr')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BANKMICR_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $bankinfo->get('micr') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.bankinfo.ifsc')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BANKIFSC_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $bankinfo->get('ifsc') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.bankinfo.swift')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BANKSWIFT_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $bankinfo->get('swift') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.taxinfo.salestax')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_SALESTAX_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $taxinfo->get('salestax') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.taxinfo.salestax')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_SERVICETAX_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $taxinfo->get('salestax') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.taxinfo.incometax')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_INCOMETAX_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $taxinfo->get('incometax') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.taxinfo.tax')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_TAX_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $taxinfo->get('tax') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
</fieldset>
