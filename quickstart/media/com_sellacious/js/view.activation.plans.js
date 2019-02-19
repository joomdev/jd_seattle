/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var SellaciousPlanActivation = function () {
	return this;
};

(function ($) {

	$(document).ready(function () {

		SellaciousPlanActivation.prototype = {
			init: function (wrapper) {
				var $this = this;
				$this.wrapper = $(wrapper);

				$this.loadPlans();

				$this.wrapper
					.on('click', '.btn-activate-free', function () {
						window.location.href = $this.wrapper.data('free-activation');
					})
					.on('click', '.btn-buynow', function () {
						var url = Joomla.getOptions('sellacious.jarvis_site') + '/index.php';
						var form = $('<form>', {action: url, method: 'post', target: '_top'});
						$('<input>', {type: 'hidden', name: 'option', value: 'com_jarvis'}).appendTo(form);
						$('<input>', {type: 'hidden', name: 'task', value: 'site.addToCart'}).appendTo(form);
						$('<input>', {type: 'hidden', name: 'format', value: 'json'}).appendTo(form);
						$('<input>', {type: 'hidden', name: 'siteurl', value: $this.wrapper.data('siteurl')}).appendTo(form);
						$('<input>', {type: 'hidden', name: 'baseurl', value: $this.wrapper.data('baseurl')}).appendTo(form);
						$('<input>', {type: 'hidden', name: 'uid', value: $(this).data('product-id')}).appendTo(form);
						$('<input>', {type: 'hidden', name: 'price', value: $(this).data('price')}).appendTo(form);
						form.appendTo($('body')).submit().remove();
					});
			},

			loadPlans: function () {
				var $this = this;
				$.ajax({
					url: Joomla.getOptions('sellacious.jarvis_site') + '/index.php',
					type: 'GET',
					cache: false,
					data: {
						option: 'com_jarvis',
						task: 'subscription.getPlansHtml',
						site_id: Joomla.getOptions('sellacious.site_id'),
					}
				}).done(function (response) {
					$this.wrapper.html(response);
				}).fail(function (xhr) {
					var err = '<div style="text-align: center; margin: 50px; color: #d00000; font-weight: bold; font-size: 18px;">' +
						'Failed to load the available plans. Please try again.</div>';
					$this.wrapper.html(err);
					console.log(xhr.responseText);
				});
			}
		};

		// Now assign the object
		var o = new SellaciousPlanActivation;
		o.init('#activation-plans');

	});

})(jQuery);
