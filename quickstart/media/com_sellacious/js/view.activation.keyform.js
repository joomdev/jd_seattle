/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var SellaciousKeyActivation = function () {

	this.step = null;
	this.ajaxDetect = null;
	this.intervalDetect = 0;

	this.api = {};

	return this;
};

(function ($) {

	$(document).ready(function () {

		SellaciousKeyActivation.prototype = {

			init: function (wizard) {
				var $this = this;
				$this.wizard = $(wizard);
				$this.wizard
					.on('click', '.btn-license-submit', function () {
						// If we are processing OR already activated, do not continue!
						if ($this.api.activated || $this.wizard.find('.no-nav').not('.hidden').length) return false;
						$this.submitKey();
					})
					.on('click', '.btn-otp-submit', function () {
						$this.submitOtp();
					})
					.on('click', '.btn-manual-check', function () {
						$this.wizard.find('#panel2').find('.wait-activation').toggleClass('manual');
					})
					.on('click', '.btn-activate', function () {
						// This is used when the license server has responded with success but self update is stuck
						if ($this.api && $this.api.activated) $this.setLicense();
					})
					.on('click', '.btn-skip-activate', function () {
						$this.stopDetect();
						$this.thanks(true);
					})
					.on('click', '.btn-resend', function () {
						$this.stepMessage(2, '');
						if ($this.api) {
							$this.api.validate(true)
								.done(function (response) {
									if (response.status === 1) {
										if ($this.api.activated) {
											// Already activated. Thanks!
											$this.setLicense();
										} else {
											// OTP Sent!
											$this.stepMessage(2, Joomla.JText._('COM_SELLACIOUS_LICENSE_ACTIVATION_OTP_RESENT', null));
										}
									} else {
										$this.stepMessage(2, response.message, 'invalid');
									}
								})
								.always(function (data, status, xhr) {
									$this.stepLoader(2, false);
								});
						}
					})
				;
				$this.navStep(1);
			},

			navStep: function (index) {
				var $this = this;
				$this.wizard.find('.activation-panel').addClass('hidden');
				$this.wizard.find('#panel' + index).removeClass('hidden');
			},

			stepLoader: function (index, isWait) {
				var $this = this;
				var panel = $this.wizard.find('#panel' + index);
				panel.find('.fieldset').toggleClass('hidden', isWait);
				panel.find('.load-spinner').toggleClass('hidden', !isWait);
			},

			stepMessage: function (index, text, type) {
				var mBox;
				var $this = this;
				mBox = index === null ? $this.wizard.find('.msgbox') : $this.wizard.find('#panel' + index).find('.msgbox');
				mBox.removeClass('invalid').removeClass('success');
				text ? mBox.text(text).removeClass('hidden').addClass(type || 'success') : mBox.text('').addClass('hidden');
			},

			submitKey: function () {
				var $this = this;
				var $sitekey = $this.wizard.find('#jform_sitekey').removeClass('invalid');
				if ($sitekey.val().length < 42) {
					$sitekey.addClass('invalid').focus();
					return;
				}

				var data = {
					siteurl: $this.wizard.find('#jform_siteurl').val(),
					sitename: $this.wizard.find('#jform_sitename').val(),
					version: $this.wizard.find('#jform_version').val(),
					site_template: $this.wizard.find('#jform_template').val(),
					sitekey: $sitekey.val()
				};

				$this.stepLoader(1, true);

				// When starting new registration clear old api instance
				$this.api = new SellaciousActivationApi();
				$this.api.setSitekey(data.sitekey)
					.setSiteurl(data.siteurl)
					.setSitename(data.sitename)
					.setVersion(data.version)
					.setTemplate(data.template);

				return $this.api.validate(true)
					.done(function (response) {
						if (response.status === 1) {
							if ($this.api.activated) {
								// Already activated. Thanks! But we are in tab 1, move forward!
								$this.stepLoader(1, false);
								$this.navStep(2);
								$this.setLicense();
							} else if (!response.data.registered || response.data.modified) {
								$this.stepLoader(1, false);
								$this.stepMessage(1, 'The License Key is invalid.', 'invalid');
							} else {
								// OTP sent! Switch panel
								$this.api.setLicense()
									.done(function (response) {
										if (response.status === 1) {
											$this.navStep(2);
											$this.startDetect();
										} else {
											$this.stepMessage(1, response.message, 'invalid');
										}
									})
									.always(function () {
										$this.stepLoader(1, false);
									});
							}
						} else {
							$this.stepLoader(1, false);
							$this.stepMessage(1, response.message, 'invalid');
						}
					})
					.always(function (data, status, xhr) {
						if (status !== 'success') $this.stepLoader(1, false);
					});
			},

			setLicense: function () {
				var $this = this;

				var tab = $this.wizard.find('#panel2');
				tab.find('.fieldset').toggleClass('hidden', true);
				tab.find('.load-spinner').toggleClass('hidden', true);

				tab.find('.load-spinner-2').toggleClass('hidden', false);
				tab.find('.retry-activation').toggleClass('hidden', true);

				$this.api.setLicense()
					.done(function (response) {
						if (response.status === 1) {
							$this.thanks();
						} else {
							$this.stepMessage(2, response.message, 'invalid');
						}
					})
					.always(function () {
						tab.find('.load-spinner-2').toggleClass('hidden', true);
						tab.find('.retry-activation').toggleClass('hidden', false);
					});
			},

			submitOtp: function () {
				var $this = this;
				var $otp = $this.wizard.find('#jform_otp');
				var otp = $otp.val();
				var auto = !$this.wizard.find('#panel2').find('.wait-activation').is('.manual') || otp.length === 0;
				if (auto) {
					// Triggered by click without OTP
					$this.checkActivation(true).done(function () {
						if (!$this.api.activated) {
							$this.stepMessage(2, Joomla.JText._('COM_SELLACIOUS_LICENSE_ACTIVATION_NOT_ACTIVATED', null), 'invalid');
						}
					});
				} else if (/^\d{6}$/.test(otp)) {
					$otp.removeClass('invalid');
					$this.activate(otp);
				} else {
					$otp.addClass('invalid');
					$this.stepMessage(2, Joomla.JText._('COM_SELLACIOUS_LICENSE_ACTIVATION_OTP_INVALID', 'Invalid OTP'), 'invalid');
				}
			},

			checkActivation: function (ui) {
				var $this = this;
				ui && $this.stepLoader(2, true);
				return $this.api.validate(false)
					.done(function (response) {
						if (response.status === 1) {
							if ($this.api.activated) {
								$this.setLicense();
							} if (!response.data.registered || response.data.modified) {
								$this.stepMessage(2, 'The license key is invalid.', 'invalid');
							} else {
								ui || $this.startDetect();
							}
						} else {
							$this.stepMessage(2, response.message, 'invalid');
						}
					})
					.always(function (data, status, xhr) {
						ui && $this.stepLoader(2, false);
					});
			},

			activate: function (otp) {
				var $this = this;
				$this.stepMessage(2, '');
				$this.stepLoader(2, true);
				$this.api.activate(otp)
					.done(function (response) {
						if (response.status === 1) {
							if ($this.api.activated) {
								// Activated. Thanks!
								$this.stepLoader(2, false);
								$this.setLicense();
							} else {
								var txt = Joomla.JText._('COM_SELLACIOUS_LICENSE_ACTIVATION_OTP_INVALID', 'Invalid OTP');
								$this.stepMessage(2, response.message || txt, 'invalid');
							}
						} else {
							$this.stepMessage(2, response.message, 'invalid');
						}
					})
					.fail(function () {
						$this.wizard.find('.timer').html('').hide();
						$this.wizard.find('#panel2').find('.wait-activation').addClass('manual');
						$this.stepMessage(2, Joomla.JText._('COM_SELLACIOUS_LICENSE_ACTIVATION_SERVER_ERROR', 'Error connecting to activation server&hellip;'), 'invalid');
					});
			},

			startDetect: function () {
				var $this = this;
				var i = 10;
				if ($this.intervalDetect) clearInterval($this.intervalDetect);
				$this.intervalDetect = setInterval(function () {
					var txt = Joomla.JText._('COM_SELLACIOUS_LICENSE_ACTIVATION_CHECKING_' + (i < 1 ? 'NOW' : 'IN'), (i < 1 ? 'Checking Now&hellip;' : 'Checking in %d seconds&hellip;'));
					$this.wizard.find('.timer').html(txt.replace('%d', i)).show();
					if (i >= 1) i--;
					else {
						i = 10;
						clearInterval($this.intervalDetect);
						$this.ajaxDetect = $this.checkActivation();
					}
				}, 1000);
			},

			stopDetect: function () {
				var $this = this;
				if ($this.intervalDetect) clearInterval($this.intervalDetect);
				if ($this.ajaxDetect) $this.ajaxDetect.abort();
				$this.wizard.find('.timer').html('').hide();
			},

			thanks: function (skip) {
				var $this = this;
				$this.navStep(3);
				$this.wizard.find('#panel3').find('.skipped').toggleClass('hidden', !skip);
				$this.wizard.find('#panel3').find('.finished').toggleClass('hidden', !!skip);
				setTimeout(function () {
					var win = window.parent || window;
					win.location.href = 'index.php';
				}, 5000);
			}
		};

		// Now assign the wizard
		var o = new SellaciousKeyActivation;
		o.init('#activation-wizard');
	});

})(jQuery);
