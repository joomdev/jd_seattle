<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Call this template with:
 * [
 * 	'returnURL' => 'index.php?......'
 * ]
 * to set up a custom return URL
 */
?>
<div class="akeeba-panel">
	<form action="index.php" method="post" name="switchActiveProfileForm" id="switchActiveProfileForm">
		<input type="hidden" name="option" value="com_akeeba" />
		<input type="hidden" name="view" value="ControlPanel" />
		<input type="hidden" name="task" value="SwitchProfile" />
		<?php if(isset($returnURL)): ?>
		<input type="hidden" name="returnurl" value="<?php echo $returnURL; ?>" />
		<?php endif; ?>
		<input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1" />

	    <label>
			<?php echo \JText::_('COM_AKEEBA_CPANEL_PROFILE_TITLE'); ?>: #<?php echo $this->profileid; ?>

		</label>
		<?php echo \JHtml::_('select.genericlist', $this->profileList, 'profileid', 'onchange="document.forms.switchActiveProfileForm.submit()" class="advancedSelect"', 'value', 'text', $this->profileid); ?>
		<button class="akeeba-btn akeeba-hidden-phone" onclick="this.form.submit(); return false;">
			<span class="akion-forward"></span>
			<?php echo \JText::_('COM_AKEEBA_CPANEL_PROFILE_BUTTON'); ?>
		</button>
	</form>
</div>
