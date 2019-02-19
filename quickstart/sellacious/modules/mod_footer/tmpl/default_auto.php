<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

$link = JRoute::_('index.php?option=com_sellacious&view=activation');

JHtml::_('script', 'com_sellacious/view.activation.readonly.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.activation.footer.css', array('version' => S_VERSION_CORE, 'relative' => true));

JText::script('COM_SELLACIOUS_ACTIVATION_CONFIRM_TRIAL_MESSAGE');

$helper = SellaciousHelper::getInstance();
?>
<div class="page-footer">
	<div class="row">
		<div class="col-xs-12 col-sm-8">
			<?php if ($helper->config->get('show_license_to', 1) || !$helper->access->isSubscribed()) : ?>
				<span class="license-validate pull-left txt-color-white"><i class="fa fa-repeat"></i> &nbsp;</span>

				<span class="license-validate txt-color-white pull-left license-name hidden"><?php
					echo JText::_('MOD_FOOTER_LICENSED_TO_LABEL') ?>: <span> </span></span>

				<span class="license-verify pull-left" style="margin-left: 10px">
					<span class="checking">
						<i class="fa txt-color-white fa-spinner fa-pulse hidden"></i>&nbsp;
						<label class="text-info hidden"><?php echo JText::_('COM_SELLACIOUS_LICENSE_WAIT_VERIFY_SPINNER_LABEL') ?></label>
					</span>

					<?php if (!$helper->access->isSubscribed()): ?>
						<span class="active">
							<i class="fa fa-thumbs-up text-success"></i>
							<a href="<?php echo $link ?>">
							<label class="text-success"><?php echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVE_LABEL') ?></label></a>
						</span>
					<?php endif; ?>

					<span class="inactive">
						<i class="fa fa-warning text-danger"></i>
						<label class="text-danger"><?php echo JText::sprintf('COM_SELLACIOUS_LICENSE_INACTIVE_LABEL_LINK', $link . '&layout=register') ?></label>
					</span>
					<span class="void">
						<i class="fa fa-warning text-danger"></i>
						<a href="<?php echo $link ?>">
						<label class="text-danger"><?php echo JText::_('COM_SELLACIOUS_LICENSE_VOID_LABEL') ?></label></a>
					</span>
					<span class="unregistered">
						<i class="fa fa-warning text-danger"></i>
						<a href="<?php echo $link ?>">
						<label class="text-danger"><?php echo JText::_('COM_SELLACIOUS_LICENSE_UNREGISTERED_LABEL') ?></label></a>
					</span>
					<span class="error">
						<i class="fa fa-times text-danger"></i>
						<a href="<?php echo $link ?>">
						<label class="text-danger"><?php echo JText::_('COM_SELLACIOUS_LICENSE_ERROR_LABEL') ?></label></a>
					</span>
				</span>

				<span style="white-space: nowrap;">
					<a href="<?php echo $link ?>">
						<span class="license-subscription hidden txt-color-lighten text-normal">&nbsp;&nbsp;&nbsp;<i class="fa fa-star txt-color-gold"></i>
						<?php echo JText::_('COM_SELLACIOUS_LICENSE_PREMIUM_EXPIRY_LABEL') ?></span>
						<span class="license-expiry_date hidden"><span class="txt-color-lighten text-normal"></span></span>
					</a>
				</span>

				<span style="white-space: nowrap; padding-left: 10px;" class="premium-prompt hidden text-success">
					<a href="index.php?option=com_sellacious&view=activation&layout=register"><span class="txt-color-lighten"><?php
							echo JText::_('COM_SELLACIOUS_LICENSE_UPGRADE_MESSAGE') ?></span></a>
					<?php if (!$helper->core->getLicense('free_forever')): ?>
					| <a href="#" class="request-trial cursor-pointer"><span class="txt-color-lighten"><?php
							echo JText::_('COM_SELLACIOUS_LICENSE_UPGRADE_TRIAL_MESSAGE') ?></span></a>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</div>
		<div class="col-xs-12 col-sm-4">
			<div class="page-end">
				<?php if (isset($helper) && $helper->config->get('show_rate_us', 1) || !$helper->access->isSubscribed()) : ?>
					<a href="https://extensions.joomla.org/write-review/review/add?extension_id=11448" target="_blank" title="More info"><i class="fa fa-star"></i><?php echo JText::_('TPL_SELLACIOUS_RATE_US_ON_JED'); ?>  </a>
				<?php endif; ?>
				<?php if ($helper->config->get('show_sellacious_version', 1) || !$helper->access->isSubscribed()) : ?>
					<span class="app-version-footer">v<?php echo S_VERSION_CORE ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
