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
defined('_JEXEC') or die;

/** @var  SellaciousViewActivation  $this */
JHtml::_('script', 'com_sellacious/view.activation.readonly.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.activation.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.activation.info.css', array('version' => S_VERSION_CORE, 'relative' => true));

$link = JRoute::_('index.php?option=com_sellacious&view=activation&layout=register');

JText::script('COM_SELLACIOUS_ACTIVATION_CONFIRM_TRIAL_MESSAGE');
?>
<div class="row">

	<?php if($this->item->get('license.active')): ?>
		<a href="<?php echo JRoute::_('index.php'); ?>" style="position: fixed; top: 15px; right: 15px; left: auto; border-radius: 15px;"
		   class="btn btn-primary pull-left"><i class="fa fa-home"></i> &nbsp;<?php echo JText::_('COM_SELLACIOUS_LICENSE_BACK_TO_DASHBOARD_LABEL', true) ?> </a>
	<?php endif; ?>

	<!-- NEW WIDGET START -->
	<article class="hidden-xs col-sm-2 col-md-3 col-lg-4"></article>
	<article class="col-xs-12 col-sm-8 col-md-6 col-lg-4">
		<!-- Widget ID -->
		<div class="jarviswidget license-info license-validate" data-auto-validate="true">
			<header>
				<span class="widget-icon"> <i class="fa fa-check"></i> </span>
				<h2><?php echo JText::_('COM_SELLACIOUS_LICENSE_VIEWER_LABEL', true) ?></h2>

				<?php if($this->item->get('license.active')): ?>
					<a href="<?php echo JRoute::_('index.php'); ?>"
					   style="color: #ffffff; position: relative; width: auto; border-radius: 15px;"
					   class="btn pull-right"><i class="fa fa-2x fa-times"></i> </a>
				<?php endif; ?>
			</header>
			<div class="widget-body">
				<div class="col-sm-12">
					<div class="tab-content">
						<div class="tab-pane active">

							<a href="<?php echo $link; ?>" style="width: auto; border-radius: 15px;"
							   class="btn btn-primary pull-left btn-register">Change or Upgrade &nbsp;<i class="fa fa-pencil-square-o"></i> </a>
							<br>
							<br>
							<div class="clearfix"></div>

							<div class="fieldset">

								<!-- Loading spinner to show only during processing / ajax -->
								<div class="center license-verify">
									<span class="checking">
										<i class="fa fa-spinner fa-pulse fa-2x"></i><br>
										<label><?php echo JText::_('COM_SELLACIOUS_LICENSE_WAIT_VERIFY_SPINNER_LABEL') ?></label>
									</span>
									<span class="active">
										<i class="fa fa-thumbs-up fa-2x text-success"></i><br>
										<?php if (!$this->helper->access->isSubscribed()): ?>
											<label class="text-success"><?php echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVE_LABEL') ?></label>
										<?php else: ?>
											<label class="text-success"><?php echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVE_VALID_LABEL') ?></label>
										<?php endif; ?>
									</span>
									<span class="inactive">
										<i class="fa fa-warning fa-2x text-danger"></i><br>
										<label class="text-danger"><?php echo JText::sprintf('COM_SELLACIOUS_LICENSE_INACTIVE_LABEL_LINK', $link) ?></label>
									</span>
									<span class="void">
										<i class="fa fa-warning fa-2x text-danger"></i><br>
										<label class="text-danger"><?php echo JText::_('COM_SELLACIOUS_LICENSE_VOID_LABEL') ?></label>
									</span>
									<span class="unregistered">
										<i class="fa fa-warning fa-2x text-danger"></i><br>
										<label class="text-danger"><?php echo JText::_('COM_SELLACIOUS_LICENSE_UNREGISTERED_LABEL') ?></label>
									</span>
									<span class="error">
										<i class="fa fa-times fa-2x text-danger"></i><br>
										<label class="text-danger"><?php echo JText::_('COM_SELLACIOUS_LICENSE_ERROR_LABEL') ?></label>
									</span>
								</div>

								<div class="h1 license-name"><span><?php echo $this->item->get('license.name'); ?></span></div>
								<div class="h4 license-email"><i class="fa fa-envelope-o"></i> <span><?php echo $this->item->get('license.email'); ?></span></div>
								<div class="h4 license-sitename"><i class="fa fa-globe"></i> <span><?php
									$sitename = $this->item->get('license.sitename');

									if (strlen($sitename) > 93)
									{
										$abbrev = $this->escape(substr($sitename, 0, 90) . '…');

										echo sprintf('<span title="%s">%s</span>', $this->escape($sitename), $abbrev);
									}
									else
									{
										echo $this->escape($sitename);
									}
									?></span></div>
								<div class="license-siteurl"><i class="fa fa-link"></i> <span><?php echo $this->item->get('license.siteurl'); ?></span></div>
								<div class="license-sitekey"><?php echo JText::_('COM_SELLACIOUS_ACTIVATION_LICENSE_KEY_LABEL'); ?>: <span><?php
									echo $this->escape($this->item->get('license.sitekey')) ?></span></div>
								<div class="license-subscription hidden"><?php echo JText::_('COM_SELLACIOUS_ACTIVATION_LICENSE_SUBSCRIPTION_LABEL'); ?>: <span> </span></div>
								<div class="license-expiry_date hidden"><?php echo JText::_('COM_SELLACIOUS_ACTIVATION_LICENSE_EXPIRY_DATE_LABEL'); ?>: <span> </span></div>

								<div class="license-site_id"><span></span></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- end widget -->
	</article>
	<article class="hidden-xs col-sm-2 col-md-3 col-lg-4"></article>
	<!-- WIDGET END -->
</div>
