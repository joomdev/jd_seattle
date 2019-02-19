/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {

	$('select').select2();

	var $wizard = $('.wizard').wizard();

	$wizard.on('change', function (e, d) {
		var currentIndex = $wizard.wizard('selectedItem');
		var selected = $wizard.find('li.active');

		if (d.direction == 'next') {
			var target = selected.data('target');
			var container = $wizard.next('.step-content').find(target);

			if (!document.formvalidator.isValid(container)) {
				e.preventDefault();
				$('#system-message-container').empty();
				$.smallBox({
					title: "Error!",
					content: "Please enter valid input for the highlighted fields&hellip;",
					color: "#df481d",
					iconSmall: "fa fa-times bounce animated",
					timeout: 4000
				});
			// IMPORTANT: This will work until we don't have two or more continuous tabs disabled - and not the last tab.
			} else if (selected.next('li').is('.hidden')) {
				$wizard.wizard('selectedItem', {step: currentIndex.step + 1});
			}
		} else if (d.direction == 'previous') {
			// IMPORTANT: This will work until we don't have two or more continuous tabs disabled - and not the last tab.
			if (selected.prev('li').is('.hidden')) {
				$wizard.wizard('selectedItem', {step: currentIndex.step - 1});
			}
		}
	});

	$wizard.on('finished', function (e) {
		// We might need to revalidate entire form and switch to the tab containing invalid fields.
		// For now we'll skip this assuming that validation per tab has already been completed.

		var selected = $wizard.find('li.active');
		var target = selected.data('target');
		var container = $wizard.next('.step-content').find(target);

		if (!document.formvalidator.isValid(container)) {
			e.preventDefault();
			$('#system-message-container').empty();
			$.smallBox({
				title: "Error!",
				content: "Please enter valid input for the highlighted fields&hellip;",
				color: "#df481d",
				iconSmall: "fa fa-times bounce animated",
				timeout: 4000
			});
		} else {
			$('.setup-spinner').removeClass('hidden');
			Joomla.submitform('setup.save', document.getElementById('fuelux-wizard'));
		}
	});

	var $currency = $('#jform_com_sellacious_global_currency');

	$currency.on('change', function () {
		var currency = $currency.val();
		console.log(currency);
		if (currency.length === 3) {
			$('.g-currency').text(currency);
		}
	}).triggerHandler('click');

	var $multiSeller = $('#jform_com_sellacious_multi_seller');

	$multiSeller.find('input').on('click change', function () {
		var $multiSellerElements = $('#multi_seller.step-pane,[data-target="#multi_seller"],.multiseller-show');
		if ($multiSeller.find('input:checked').val() == 1) {
			$multiSellerElements.removeClass('hidden');
		} else {
			$multiSellerElements.addClass('hidden');
		}
	}).triggerHandler('click');

	var $priceDisplay = $('#jform_com_sellacious_allowed_price_display');

	$priceDisplay.find('input').on('click change', function () {
		var queryForm = $priceDisplay.find('#jform_com_sellacious_allowed_price_display3').is(':checked');
		$('#jform_com_sellacious_query_form_recipient').closest('.form-group').toggleClass('hidden', !queryForm);
	}).triggerHandler('click');

	var $flatShip = $('#jform_com_sellacious_flat_shipping');

	$flatShip.find('input').on('click change', function () {
		var flatShip = $flatShip.find('input:checked').val() == 1;
		$('#jform_com_sellacious_shipping_flat_fee').closest('.form-group').toggleClass('hidden', !flatShip);
	}).triggerHandler('click');

	var $freeListing = $('#jform_com_sellacious_free_listing');

	$freeListing.find('input').on('click change', function () {
		var freeListing = $freeListing.find('input:checked').val() == 1;
		$('#jform_com_sellacious_listing_fee').closest('.form-group').toggleClass('hidden', freeListing);
	}).triggerHandler('click');

	var $checkout = $('#jform_com_sellacious_allow_checkout');

	$checkout.find('input').on('click change', function () {
		var checkout = $checkout.find('input:checked').val() == 1;
		var $inputs = $(['#jform_com_sellacious_purchase_return',
			'#jform_com_sellacious_purchase_exchange',
			'#jform_com_sellacious_flat_shipping',
			'#jform_com_sellacious_shipping_flat_fee',
			'#jform_com_sellacious_on_sale_commission',
			'#jform_com_sellacious_shipped_by'].join(','));
		$inputs.closest('.form-group').toggleClass('hidden', !checkout);

		// Check dependency fields
		$('#jform_com_sellacious_flat_shipping').triggerHandler('change');
	}).triggerHandler('click');

	var $trial = $('#jform_premium_trial');

	$trial.find('input').on('click change', function () {
		var trial = $trial.find('input:checked').val() == 1;
		var $spinner = $('.setup-spinner');
		$spinner.find('h5.no-trial').toggleClass('hidden', trial);
		$spinner.find('h5.with-trial').toggleClass('hidden', !trial);
	}).triggerHandler('click');

});
