/*====
*
*  Common Js file for all layouts
*
*/

(function ($) {
	$.fn.extend({
		rollover: function () {
			var interval = [];
			$(this).each(function (i) {
				var $roller         = $(this);
				var $img            = $roller.find('img[data-rollover]');
				var $bgrollover     = $roller.find('.bgrollover');

				if ($img.length) {
					var images = $img.data('rollover');
					$img.removeAttr('data-rollover');
					interval[i] = 0;
					var count;
					if (count = images.length) {
						$roller.hover(function () {
							interval[i] = setInterval(function () {
								var ci = $img.data('rolloverIndex') || 0;
								$img.attr('src', images[ci]);
								$img.data('rolloverIndex', ci + 1 < count ? ci + 1 : 0);
							}, 1500);
						}, function () {
							interval[i] && clearInterval(interval[i]);
						});
					}
				}else if($bgrollover.length){
					var bgimages = $bgrollover.data('rollover');
					$bgrollover.removeAttr('data-rollover');
					interval[i] = 0;
					var bgcount;
					if (bgcount = bgimages.length) {
						$roller.hover(function () {
							interval[i] = setInterval(function () {
								var ci = $bgrollover.data('rolloverIndex') || 0;
								$bgrollover.css('background-image', 'url("'+bgimages[ci]+'")');
								$bgrollover.data('rolloverIndex', ci + 1 < bgcount ? ci + 1 : 0);

							}, 1500);
						}, function () {
							interval[i] && clearInterval(interval[i]);
						});
					}
				}else{
					return;
				}


			});
		}
	});

	$(document).ready(function () {
		// Set macro for autoload
		$('[data-rollover="container"]').rollover();

		$('div.modal').each(function() {
			$(this).appendTo($('body'));
		});

		$('.modal-header button.close').click(function() {
			$('body').removeClass('modal-open');
		});
	});
})(jQuery);
