/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	var $filters = $('.mod-sellacious-filters');
	var $clearFilter = $('.btn-clear-filter');

	$filters.each(function () {
		var $filter = $(this);

		$filter.find('.search-filter').on('keyup', 'input[type="text"]', function (e) {
			var $this = $(e.target);
			var val = $this.val();
			var choices = $this.closest('.filter-snap-in').find('.filter-choice');
			var regex = new RegExp(val);

			$.each(choices, function (i, choice) {
				var input = $(choice).find('input');
				regex.test(input.val()) ? $(choice).show('fast') : $(choice).hide('fast');
			});
		});

		$filter.on('click', '.clear-filter', function (e) {
			var $this = $(e.target);
			$filter.find('.search-filter').find('input[type="text"]').val('').trigger('keyup');
			$filter.find('.filter-price-area').find('input[type="number"]').val('').trigger('keyup');
			var choices = $this.closest('.filter-snap-in').find('input[type="checkbox"]');
			choices.not(':disabled').prop('checked', false);

			$(this).closest('form').submit();
		});

		$filter.find('.filter-title').click(function (e) {
			$(e.target).is('.clear-filter') ||
			$(this).closest('.filter-snap-in').toggleClass('filter-collapse');
		});

		$filter.find('.filter-head').click(function () {
			$filter.toggleClass('closed-on-phone');
		});

		$filter.on('click change', '.store-location-options', function (e) {
			var $this = $(e.target);

			if ($this.val() == 2) {
				$('.s-l-custom-block').removeClass('hidden');
			} else {
				$('.s-l-custom-text').val('');
				$('.s-l-custom-block').addClass('hidden');
				$(this).closest('form').submit();
			}
		});

		$filter.find('.filter-title').click(function (e) {
			$(e.target).is('.clear-filter') ||
			$(this).closest('.filter-snap-in').toggleClass('filter-collapse');
		});

	});

	$clearFilter.on('click', function(e){
		e.preventDefault();

		var filterFields = $filters.find('select,input[name^=\'filter\']');
		var form         = $(this).closest('form');
		var action       = $(this).data('redirect');

		filterFields.each(function (i, element) {
			if ($(element).attr('type') == 'radio') {
				$(element).attr('checked', false);
			} else if ($(element).attr('name') == 'filter[category_id]'){
				$(element).val(1);
			} else {
				$(element).val('');
			}
		});

		$filters.find('input[type="radio"][value="0"]').each(function (i, element) {
			$(element).attr('checked', true);
		});

		form.attr('action', action);

		$(document).trigger('onClearFilters', [form]);

		form.submit();
	});

	$(document).on('onClearFilters', function(form, callback) {
		var $this = this;

		var data = {
			option : 'com_ajax',
			module : 'sellacious_filters',
			format : 'json',
			method : 'clearFilters'
		};

		$.ajax({
			url: 'index.php',
			type: 'POST',
			dataType: 'json',
			cache: false,
			async: false,
			data: data,
			beforeSend: function () {
			},
			complete: function () {
			}
		}).done(function (response) {
			if (response.success) {
				if (typeof callback == 'function') callback(response, $this);
			} else {
				Joomla.renderMessages({warning: [response.message]});
			}
		}).fail(function (jqXHR) {
			console.log(jqXHR.responseText);
		});
	});

	// Filter Category List Accordion
	$("#filter-list-group").treeview({
		collapsed: true,
		animated:"normal",
		unique: true,
		persist: "location"
	}).find('li>a.active').parentsUntil('.treeview', 'ul').css('display', 'block');
});
