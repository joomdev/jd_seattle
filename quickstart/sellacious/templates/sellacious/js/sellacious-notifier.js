/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var SellaciousNotifier = function () {
	return this;
};

jQuery(document).ready(function ($) {

	SellaciousNotifier.prototype = {

		init: function () {
			var $this = this;
			$this.fetch(function (banners) {
				$this.display(banners);
			});
		},

		fetch: function (callback) {
			$.ajax({
					url: Joomla.getOptions('sellacious.jarvis_site') + '/index.php?option=com_jarvis&view=views&format=json',
					type: 'POST',
					dataType: 'json',
					cache: false,
					data: {
						// This is ok until we use non-SEF url
						page: encodeURIComponent(window.location.href)
					}
				})
				.done(function (response) {
					if (response.status === 1)
						callback(response.data);
					else {
						console.log('Unable to fetch news!');
						$('#context-news').hide();
					}
				})
				.fail(function (jqXHR) {
					console.log(jqXHR.responseText);
				});
		},

		display: function (banners) {
			var $this = this;
			if (banners.length)
				$(banners).each(function (index, banner) {
					banner.link = Joomla.getOptions('sellacious.jarvis_site') + '/index.php?option=com_banners&task=click&id=' + banner.id;
					if (parseInt(banner.type) === 1) $this.text(banner);
					else if (banner.params['imageurl']) $this.image(banner);
				});
		},

		image: function (banner) {
			// Get image size and set it
			var $element = $('#context-news');
			if ($element.length) {
				$('<img/>').load(function () {
					var w = this.naturalWidth;
					var h = this.naturalHeight;
					var width = parseInt(38.0 * w / h);
					$(this).css({'width': width, 'height': 38, 'box-sizing': 'border-box'});
					if (banner['clickurl']) {
						var a = $('<a/>', {
							href: banner.link,
							target: '_blank',
							"class": 'hasTooltip',
							title: banner.name
						}).append(this);
						$element.html(a);
					} else
						$element.html(this);
				}).attr({
					src: Joomla.getOptions('sellacious.jarvis_site') + '/' + banner.params['imageurl'],
					alt: banner.params['alt'],
					title: banner.name
				});
			}
		},

		text: function (banner) {
			var $element = $('#context-news');
			if ($element.length && banner['custombannercode']) {
				var text = banner['custombannercode'];
				var html = text.replace(/\{CLICKURL}/, banner.link)
					.replace(/\{NAME}/, banner.name)
					.replace(/\{DESCRIPTION}/, banner.description);
				$element.html(html);
			}
		}
	};

	var o = new SellaciousNotifier();
	o.init();
});
