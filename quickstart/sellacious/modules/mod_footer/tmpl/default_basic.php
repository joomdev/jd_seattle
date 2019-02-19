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

$helper = SellaciousHelper::getInstance();
?>
<div class="page-footer">
	<div class="row">
		<div class="col-xs-12 col-sm-8">
			<?php if ($helper->config->get('show_license_to', 1) || !$helper->access->isSubscribed()) : ?>
				<?php if (!$license->get('name') || !$license->get('sitekey')): ?>

					<i class="fa fa-warning txt-color-white"></i>
					<span class="txt-color-yellow"> &nbsp;<?php echo JText::_('MOD_FOOTER_LICENSED_NOT_REGISTERED') ?></span>

				<?php else: ?>

					<span class="txt-color-white"><?php echo JText::_('MOD_FOOTER_LICENSED_TO_LABEL') ?>: <?php echo $license->get('name') ?></span>

					<?php if (!$license->get('active')): ?>

						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<i class="fa fa-warning txt-color-white"></i>
						<span class="txt-color-yellow"> &nbsp;<?php echo JText::_('MOD_FOOTER_LICENSED_NOT_ACTIVATED') ?></span>

					<?php endif; ?>

				<?php endif; ?>
			<?php endif; ?>
		</div>
		<div class="col-xs-12 col-sm-4">
			<div class="page-end">
				<?php if (isset($helper) && ($helper->config->get('show_rate_us', 1) || !$helper->access->isSubscribed())) : ?>
					<a href="https://extensions.joomla.org/write-review/review/add?extension_id=11448"
					   target="_blank" title="More info"><i class="fa fa-star"></i><?php echo JText::_('TPL_SELLACIOUS_RATE_US_ON_JED'); ?>  </a>
				<?php endif; ?>
				<?php if (isset($helper) && ($helper->config->get('show_sellacious_version', 1) || !$helper->access->isSubscribed())) : ?>
					<span class="app-version-footer">v<?php echo S_VERSION_CORE ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
