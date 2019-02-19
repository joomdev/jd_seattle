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
JHtml::_('behavior.formvalidator');

$this->document->addScript('templates/sellacious/js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.js');
$this->document->addScript('templates/sellacious/js/plugin/jquery-validate/jquery.validate.min.js');

JHtml::_('script', 'com_sellacious/util.activation.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.activation.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.activation.css', array('version' => S_VERSION_CORE, 'relative' => true));

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_OTP_RESENT');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_OTP_INVALID');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_CHECKING_IN');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_CHECKING_NOW');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_SERVER_ERROR');
JText::script('COM_SELLACIOUS_LICENSE_ACTIVATION_NOT_ACTIVATED');
?>
<!-- NEW WIDGET START -->
<article class="col-sm-1 col-sm-2 col-md-2 col-lg-3"></article>
<article class="col-xs-10 col-sm-8 col-md-8 col-lg-6">
	<!-- Widget ID (each widget will need unique ID)-->
	<div class="jarviswidget license-form">
		<header>
			<span class="widget-icon"> <i class="fa fa-check"></i> </span>
			<h2><?php echo JText::_('COM_SELLACIOUS_LICENSE_FIELDSET_BASIC_LABEL', true) ?></h2>
		</header>
		<div class="widget-body">
			<form id="wizard-license" novalidate="novalidate">
				<div id="activation-wizard" class="col-sm-12">
					<div class="form-bootstrapWizard center">
						<ul class="bootstrapWizard form-wizard">
							<li class="active" data-target="#step1">
								<a href="#tab1" data-toggle="tab" onclick="return false;"> <span class="step">1</span> <span class="title">Registration</span>
								</a>
							</li>
							<li data-target="#step2">
								<a href="#tab2" data-toggle="tab"> <span class="step">2</span> <span class="title">Verification</span>
								</a>
							</li>
							<li data-target="#step3">
								<a href="#tab3" data-toggle="tab"> <span class="step">3</span> <span class="title">Complete</span> </a>
							</li>
						</ul>
						<div class="clearfix"></div>
					</div>
					<div class="tab-content">
						<div class="tab-pane active" id="tab1">
							<br>
							<h3 class="pull-left"><strong>Step 1 </strong> - Registration</h3>
							<?php $haveLic = $this->item->get('license.sitekey') ?>

							<a class="alert-link pull-right padding-20-0 btn-have-license" href="javascript:void(0);"
							   		data-on="<?php echo $haveLic ? '1' : '0' ?>"><span
										class="<?php echo $haveLic ? 'hidden' : '' ?>"><?php
								echo JText::_('COM_SELLACIOUS_LICENSE_ENTER_KEY_BUTTON_LABEL'); ?></span><span
										class="<?php echo $haveLic ? '' : 'hidden' ?>"><?php
								echo JText::_('COM_SELLACIOUS_LICENSE_REGISTER_NEW_BUTTON_LABEL'); ?></span></a>

							<div class="clearfix"></div>

							<div class="fieldset">

								<label class="msgbox hidden"></label>

								<input type="hidden" id="jform_sitename" value="<?php echo $this->item->get('sitename'); ?>">
								<input type="hidden" id="jform_siteurl" value="<?php echo $this->item->get('siteurl'); ?>">
								<input type="hidden" id="jform_version" value="<?php echo $this->item->get('version'); ?>">
								<input type="hidden" id="jform_template" value="<?php echo $this->item->get('template'); ?>">

								<div class="row form-registration <?php echo $haveLic ? 'have-license' : '' ?>">

									<div class="col-sm-12 form-register">
										<div class="form-group">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-user fa-lg fa-fw"></i></span>
												<input class="form-control input-lg" type="text" name="jform[name]" id="jform_name"
													   placeholder="Name" value="<?php echo $this->item->get('license.name') ?>">
											</div>
										</div>
									</div>

									<div class="col-sm-12 form-register">
										<div class="form-group">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-envelope fa-lg fa-fw"></i></span>
												<input class="form-control input-lg" type="text" name="jform[email]" id="jform_email"
													   placeholder="email@address.com" value="<?php echo $this->item->get('license.email') ?>">
											</div>
										</div>
									</div>

									<div class="col-sm-12 form-register">
										<div class="smart-form">
											<label for="jform_choices" class="h4 margin-bottom-10"><?php
												echo JText::_('COM_SELLACIOUS_ACTIVATION_SURVEY_WHY_SELLACIOUS_LABEL') ?></label>
											<div class="inline-group" id="jform_choices">
												<label class="checkbox">
													<input type="checkbox" name="jform[choices][]" id="jform_choices_0" value="features">
													<i></i><?php echo JText::_('COM_SELLACIOUS_ACTIVATION_SURVEY_WHY_SELLACIOUS_FEATURES') ?></label>
												<label class="checkbox">
													<input type="checkbox" name="jform[choices][]" id="jform_choices_1" value="admin_interface">
													<i></i><?php echo JText::_('COM_SELLACIOUS_ACTIVATION_SURVEY_WHY_SELLACIOUS_ADMIN_INTERFACE') ?></label>
												<label class="checkbox">
													<input type="checkbox" name="jform[choices][]" id="jform_choices_2" value="multi_seller">
													<i></i><?php echo JText::_('COM_SELLACIOUS_ACTIVATION_SURVEY_WHY_SELLACIOUS_MULTI_SELLER') ?></label>
												<label class="checkbox">
													<input type="checkbox" name="jform[choices][]" id="jform_choices_3" value="shoprules">
													<i></i><?php echo JText::_('COM_SELLACIOUS_ACTIVATION_SURVEY_WHY_SELLACIOUS_SHOPRULES') ?></label>
												<label class="checkbox">
													<input type="checkbox" name="jform[choices][]" id="jform_choices_4" value="support">
													<i></i><?php echo JText::_('COM_SELLACIOUS_ACTIVATION_SURVEY_WHY_SELLACIOUS_SUPPORT') ?></label>
												<label class="checkbox">
													<input type="checkbox" name="jform[choices][]" id="jform_choices_5" value="other">
													<i></i><?php echo JText::_('COM_SELLACIOUS_ACTIVATION_SURVEY_WHY_SELLACIOUS_OTHER') ?></label>
											</div>
										</div>
									</div>

									<div class="col-sm-12 form-license">
										<div class="form-group">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-key fa-lg fa-fw"></i></span>
												<input class="form-control input-lg uppercase" type="text" name="jform[sitekey]" id="jform_sitekey"
													   placeholder="License Key" maxlength="60" value="<?php echo $this->item->get('license.sitekey') ?>">
											</div>
										</div>
									</div>

								</div>
							</div>

							<!-- Loading spinner to show only during processing / ajax -->
							<div class="no-nav load-spinner hidden center">
								<i class="fa fa-spinner fa-pulse fa-4x"></i>
								<h5><?php echo JText::_('COM_SELLACIOUS_LICENSE_WAIT_SPINNER_LABEL') ?></h5>
							</div>

						</div>

						<div class="tab-pane" id="tab2">
							<br>
							<h3 class="pull-left"><strong>Step 2</strong> - Verification</h3>

							<a class="alert-link pull-right padding-20-0 btn-skip-activate" href="javascript:void(0);"><?php
								echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_SKIP_LABEL'); ?></a>

							<div class="clearfix"></div>

							<div class="fieldset">

								<label class="msgbox hidden"></label>

								<div class="row wait-activation auto-check">

									<div class="col-sm-12 auto-check">
										<h5 class="text-center"><?php
											echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_LINK_SENT_AUTODETECT'); ?></h5>
										<div class="center"><i class="fa fa-refresh fa-spin fa-4x"></i></div>
										<h5 class="text-center"><?php echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_WAITING_MESSAGE'); ?></h5>
										<div class="center">
											<button type="button" class="btn btn-primary btn-xs btn-manual-check"><?php
												echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_BUTTON_MANUALLY') ?></button>
										</div>
									</div>

									<div class="col-sm-12 manual-check">
										<h5 class="text-center"><?php echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_LINK_SENT'); ?></h5>
										<div class="form-group">
											<div class="input-group w30p margin-center">
												<span class="input-group-addon"><i class="fa fa-calculator fa-lg fa-fw"></i></span>
												<input class="form-control input-lg" type="text" name="jform[otp]" id="jform_otp"
													   placeholder="Verification Code" maxlength="6">
											</div>
										</div>
									</div>

								</div>

								<a class="alert-link pull-right btn-resend" href="javascript:void(0);"><?php
									echo JText::_('COM_SELLACIOUS_LICENSE_ACTIVATION_LINK_RESEND_BUTTON'); ?></a>

							</div>

							<!-- Loading spinner to show only during processing / ajax -->
							<div class="no-nav load-spinner hidden center">
								<i class="fa fa-spinner fa-pulse fa-4x"></i>
								<h5><?php echo JText::_('COM_SELLACIOUS_LICENSE_WAIT_VERIFY_SPINNER_LABEL'); ?></h5>
							</div>

							<div class="no-nav load-spinner-2 hidden center">
								<i class="fa fa-spinner fa-pulse fa-4x"></i>
								<h5><?php echo JText::_('COM_SELLACIOUS_LICENSE_WAIT_SPINNER_ACTIVATING_LABEL'); ?></h5>
							</div>

							<div class="no-nav retry-activation hidden center">
								<i class="fa fa-repeat fa-3x"></i>
								<h5><?php echo JText::_('COM_SELLACIOUS_LICENSE_RETRY_ACTIVATION_LABEL'); ?></h5>
								<button type="button" class="btn btn-default btn-activate"><?php
									echo JText::_('COM_SELLACIOUS_LICENSE_RETRY_ACTIVATION_BUTTON'); ?></button>
							</div>
						</div>

						<div class="tab-pane" id="tab3">
							<br>
							<h3><strong>Step 3</strong> - Complete</h3>
							<br>
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

						<div class="form-actions">
							<div class="row">
								<div class="col-sm-12">
									<ul class="pager wizard no-margin">
										<li class="previous disabled">
											<a href="javascript:void(0);" class="btn btn-lg btn-default"> Previous </a>
										</li>
										<li><span class="timer" style="display: none;"></span></li>
										<li class="next">
											<a href="javascript:void(0);" class="btn btn-lg txt-color-darken"> Next </a>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	<!-- end widget -->
</article>
<article class="col-sm-1 col-sm-2 col-md-2 col-lg-3"></article>
<!-- WIDGET END -->
