/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var SellaciousBanner = function () {
	this.sitekey = null;
	this.positions = {
		"3": "#sellacious-banner-top-a",
		"8": "#sellacious-banner-top-b",
		"9": "#sellacious-banner-right-a",
		"10": "#sellacious-banner-right-b"
	};
	return this;
};

jQuery(document).ready(function ($) {

	SellaciousBanner.prototype = {

		init: function (sitekey) {
			var $this = this;
			$this.sitekey = sitekey;
			$this.fetch(function (banners) {
				$this.display(banners);
			});
		},

		fetch: function (callback) {
			var $this = this;
			if (!$this.sitekey) return false;
			$.ajax({
					url: Joomla.getOptions('sellacious.jarvis_site') + '/index.php?option=com_jarvis&view=banners&format=json',
					type: 'POST',
					dataType: 'json',
					cache: false,
					data: {sitekey: $this.sitekey}
				})
				.done(function (response) {
					if (response.status == 1)
						callback(response.data);
					else {
						console.log('Unable to fetch banners!');
						$.each($this.positions, function (i, el) {
							$(el).hide();
						})
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
					if (banner.type == 1) $this.text(banner);
					else if (banner.params['imageurl']) $this.image(banner);
				});
		},

		image: function (banner) {
			var element = this.positions[banner.catid];
			if (element && $(element).length) {
				var img = $('<img'+'/>', {
					src: Joomla.getOptions('sellacious.jarvis_site') + '/' + banner.params['imageurl'],
					alt: banner.params['alt'],
					"class": 'hasTooltip',
					"style": 'max-width: 100%; max-height: 100%',
					title: banner.name
				})
					.css('width', banner.params['width'] || null)
					.css('height', banner.params['height'] || null);

				if (banner['clickurl']) {
					var a = $('<a/>', {
						href: banner.link,
						target: '_blank',
						"class": 'hasTooltip',
						title: banner.name
					}).append(img);
					$(element).html(a);
				} else
					$(element).html(img);
			}
		},

		text: function (banner) {
			var element = this.positions[banner.catid];
			if (element && $(element).length && banner['custombannercode']) {
				var text = banner['custombannercode'];
				var html = text.replace(/\{CLICKURL}/, banner.link)
					.replace(/\{NAME}/, banner.name)
					.replace(/\{DESCRIPTION}/, banner.description);
				$(element).html(html);
			}
		}
	}
});
