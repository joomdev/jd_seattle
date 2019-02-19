/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var SellaciousActivationApi = function () {

	this.activated = false;
	this._cname = null;
	this._email = null;
	this._sitekey = null;

	this._sitehash = null;
	this._sitehash2 = null;

	this._siteurl = null;
	this._sitename = null;
	this._version = null;
	this._template = null;
	this._choices = {};

	this.ajax = null;
	this.ajax = null;

	return this;
};

jQuery(document).ready(function ($) {

	SellaciousActivationApi.prototype = {

		setHash: function (value) {
			this._sitehash = value;
			return this;
		},

		setSitekey: function (value) {
			this._sitekey = value;
			return this;
		},

		setSiteurl: function (value) {
			this._siteurl = value;
			return this;
		},

		setSitename: function (value) {
			this._sitename = value;
			return this;
		},

		setVersion: function (value) {
			this._version = value;
			return this;
		},

		setTemplate: function (value) {
			this._template = value;
			return this;
		},

		setChoices: function (items) {
			this._choices = items;
			return this;
		},

		setCredential: function (name, email) {
			this._cname = name;
			this._email = email;
			return this;
		},

		register: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			return $this.ajax = $.ajax({
				url: Joomla.getOptions('sellacious.jarvis_site') + '/index.php?option=com_jarvis&task=site.register&format=json',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {
					name: $this._cname,
					email: $this._email,
					siteurl: $this._siteurl,
					sitename: $this._sitename,
					version: $this._version,
					site_template: $this._template,
					choices: $this._choices,
				}
			}).done(function (response) {
				if (response.status === 1) {
					$this._sitekey = response.data.sitekey;
					// Check modified/registered not needed as this will be new one!
					$this.activated = response.data.active;
				}
			}).fail(function (xhr) {
				console.log(xhr.responseText);
			});
		},

		validate: function (sendOtp) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			return $this.ajax = $.ajax({
				url: Joomla.getOptions('sellacious.jarvis_site') + '/index.php?option=com_jarvis&task=site.validate&format=json',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {
					sitekey: $this._sitekey,
					siteurl: $this._siteurl,
					sitename: $this._sitename,
					version: $this._version,
					site_template: $this._template,
					send_otp: sendOtp ? 1 : 0,
				}
			})
				.done(function (response) {
					/** @namespace response.data.modified */
					if (response.status === 1 &&
						response.data.registered === true &&
						response.data.modified === false &&
						response.data.active === true) {
						$this.activated = true;
						$this._cname = response.data.name;
						$this._email = response.data.email;
					}
				})
				.fail(function (xhr) {
					console.log(xhr.responseText);
				});
		},

		setLicense: function () {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			return $this.ajax = $.ajax({
				url: 'index.php?option=com_sellacious&task=activation.retrieveAjax',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {sitekey: $this._sitekey}
			})
				.fail(function (xhr) {
					console.log(xhr.responseText);
				});
		},

		activate: function (otp) {
			var $this = this;
			if ($this.ajax) $this.ajax.abort();
			return $this.ajax = $.ajax({
				url: Joomla.getOptions('sellacious.jarvis_site') + '/index.php?option=com_jarvis&task=site.activate&format=json',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {
					sitekey: $this._sitekey,
					otp: otp ? otp : null
				}
			})
				.done(function (response) {
					/** @namespace response.data.modified */
					if (response.status === 1 &&
						response.data.registered === true &&
						response.data.modified === false &&
						response.data.active === true) {
						$this.activated = true;
					}
				})
				.fail(function (xhr) {
					console.log(xhr.responseText);
				});
		},
	};
});

