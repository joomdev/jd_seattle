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

JHtml::_('bootstrap.tooltip');
JHtml::_('script', 'com_sellacious/util.activation.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.activation.keyform.js', array('version' => S_VERSION_CORE, 'relative' => true));

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_OTP_RESENT');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_OTP_INVALID');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_CHECKING_IN');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_CHECKING_NOW');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_SERVER_ERROR');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_NOT_ACTIVATED');
?>
<div class="row" id="activation-wizard">

	<div class="col-xs-1 col-sm-1 col-md-3">
	</div>
	<div class="col-xs-10 col-sm-10 col-md-6">
		<div class="widget-body">
			<div class="form-horizontal activation-panel" id="panel1">
				<div class="msgbox hidden"></div>
				<fieldset class="fieldset">
					<div class="row">
						<div class="col-md-12 form-license">
							<div class="form-group">
								<label for="jform_sitekey" class="form-label center w100p">Enter Your License Key</label>
								<div class="input-group hasTooltip" title="Your License Key here" data-placement="bottom">
									<span class="input-group-addon">&nbsp; <i class="fa fa-key fa-lg fa-fw"></i> &nbsp;</span>
									<input type="text" id="jform_sitekey" autocomplete="sellacious-license-key"
										   class="form-control input-lg uppercase" placeholder="Enter Your License Key&hellip;"
										   maxlength="60" value="<?php echo $this->item->get('license.sitekey') ?>">
									<span class="input-group-btn">
										<button class="btn btn-primary btn-license-submit" type="button"> Activate </button>
									</span>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" id="jform_sitename" value="<?php echo $this->item->get('sitename'); ?>">
					<input type="hidden" id="jform_siteurl" value="<?php echo $this->item->get('siteurl'); ?>">
					<input type="hidden" id="jform_version" value="<?php echo $this->item->get('version'); ?>">
					<input type="hidden" id="jform_template" value="<?php echo $this->item->get('template'); ?>">
				</fieldset>
				<!-- Loading spinner to show only during processing / ajax -->
				<div class="no-nav load-spinner hidden center">
					<i class="fa fa-spinner fa-pulse fa-2x"></i>
					<h5><?php echo JText::_('COM_SELLACIOUS_LICENSE_WAIT_SPINNER_LABEL'); ?></h5>
				</div>
			</div>

			<div class="form-horizontal activation-panel" id="panel2">
				<div class="msgbox hidden"></div>
				<fieldset class="fieldset wait-activation auto-check">
					<div class="row">
						<div class="col-md-12 auto-check">
							<h5 class="text-center"><?php
								echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_LINK_SENT_AUTODETECT'); ?></h5>
							<div class="center"><i class="fa fa-refresh fa-spin fa-4x"></i></div>
							<h5 class="text-center"><?php echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_WAITING_MESSAGE'); ?> <span class="timer"></span></h5>
						</div>
						<div class="col-md-12 manual-check">
							<h5 class="text-center"><?php echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_LINK_SENT'); ?></h5>
							<div class="form-group">
								<div class="input-group w50p margin-center">
									<span class="input-group-addon">&nbsp; <i class="fa fa-calculator fa-lg fa-fw"></i> &nbsp;</span>
									<input type="text" name="jform[otp]" id="jform_otp" class="form-control input-lg" autocomplete="off"
									       maxlength="6" placeholder="Enter the OTP&hellip;">
								</div>
							</div>
						</div>
					</div>

					<a href="javascript:void(0);" class="btn btn-primary pull-left padding-20-0 margin-right-5 btn-resend"><?php
						echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_LINK_RESEND_BUTTON'); ?> &nbsp; <i class="fa fa-repeat"></i></a>

					<a href="javascript:void(0);" class="btn btn-primary pull-left padding-20-0 margin-right-5 btn-skip-activate"><?php
						echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_SKIP_LABEL'); ?> &nbsp; <i class="fa fa-pause"></i></a>

					<a href="javascript:void(0);" class="btn btn-primary pull-right padding-20-0 margin-right-5 btn-manual-check auto-check"><?php
						echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_BUTTON_MANUALLY') ?> &nbsp; <i class="fa fa-pencil"></i></a>

					<a href="javascript:void(0);" class="btn btn-primary pull-right padding-20-0 margin-right-5 btn-otp-submit manual-check"><?php
						echo JText::_('JNEXT') ?> &nbsp; <i class="fa fa-chevron-right"></i></a>

				</fieldset>

				<div class="no-nav retry-activation hidden center">
					<i class="fa fa-repeat fa-3x"></i>
					<h5><?php echo JText::_('COM_SELLACIOUS_LICENSE_RETRY_ACTIVATION_LABEL'); ?></h5>
					<button type="button" class="btn btn-default btn-activate"><?php
						echo JText::_('COM_SELLACIOUS_LICENSE_RETRY_ACTIVATION_BUTTON'); ?></button>
				</div>
				<!-- Loading spinner to show only during processing / ajax -->
				<div class="no-nav load-spinner hidden center">
					<i class="fa fa-spinner fa-pulse fa-2x"></i>
					<h5><?php echo JText::_('COM_SELLACIOUS_LICENSE_WAIT_VERIFY_SPINNER_LABEL'); ?></h5>
				</div>

				<div class="no-nav load-spinner-2 hidden center">
					<i class="fa fa-spinner fa-pulse fa-2x"></i>
					<h5><?php echo JText::_('COM_SELLACIOUS_LICENSE_WAIT_SPINNER_ACTIVATING_LABEL'); ?></h5>
				</div>
			</div>

			<div class="activation-panel" id="panel3">
				<div class="finished">
					<h1 class="text-center text-success"><strong><i class="fa fa-thumbs-up fa-lg"></i> Thank You!</strong></h1>
					<h4 class="text-center">We appreciate your taking
						the time to activate this copy of Sellacious. Don't forget that we offer an awesome support for any
						difficulties you face with sellacious.</h4>
				</div>
				<div class="skipped hidden">
					<h1 class="text-center text-warning"><strong><i class="fa fa-exclamation-circle fa-lg"></i> Wait!</strong></h1>
					<h5 class="text-center">Don't forget that we offer an awesome support for any difficulties you face with
						sellacious. To avail of the support features you must complete the verification process.</h5>
				</div>
				<br>
				<br>
			</div>

		</div>
	</div>
	<div class="col-xs-1 col-sm-1 col-md-3">
	</div>

</div>
