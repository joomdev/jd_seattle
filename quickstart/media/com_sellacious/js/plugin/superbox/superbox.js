/*
	SuperBox v1.0.0 (modified by bootstraphunter.com)
	by Todd Motto: http://www.toddmotto.com
	Latest version: https://github.com/toddmotto/superbox

	Copyright 2013 Todd Motto
	Licensed under the MIT license
	http://www.opensource.org/licenses/mit-license.php

	SuperBox, the lightbox reimagined. Fully responsive HTML5 image galleries.
*/
;(function($) {

	// we want single instance superbox
	var superbox = null;

	$.fn.SuperBox = function(options) {
		
		$superbox    = $(this);

		if (typeof options == 'undefined') {
			options  = {};
		}

		var buttons = options.buttons || '<a href="javascript:void(0);" class="btn btn-primary btn-sm">Edit Image</a> '+'<a href="javascript:void(0);" class="btn btn-danger btn-sm">Delete</a>';

		if (superbox == null) {
			superbox      = $('<div class="superbox-show"></div>'),
			superboximg   = $('<img src="" class="superbox-current-img" style="border: #ffffee 1px solid">'),
			superboxdiv   =	$('<div class="inline-block">'+
									'<p>'+ buttons +'</p>'+
									'<span>'+
									'<h1></h1>'+
									'<p class="superbox-img-description"></p>'+
									'</span>'+
								'</div>'),
			superboxclose = $('<div class="superbox-close txt-color-white clear-both"><i class="fa fa-times fa-lg"></i></div>');

			superbox.append(superboxclose).append(superboximg);
			superbox.append(superboxdiv);
		}

		return this.each(function() {

			$superbox.find('.superbox-list').each(function(key, element) {
				if (!$(element).hasClass('hasSuperBox')) {
					
					$(element).addClass('hasSuperBox');

					$(element).click(function() {
						$this = $(this);

						superboximg.trigger('hidden');

						var currentimg = $this.find('.superbox-img'),
							imgData = currentimg.data('img'),
							imgDescription = currentimg.attr('alt') || "",
							imgLink = imgData,
							imgTitle = currentimg.attr('title') || "",
							imgRef = currentimg.data('id') || 0;

							//console.log(imgData, imgDescription, imgLink, imgTitle)

						superboximg.attr('src', imgData);
						superboximg.data('id', imgRef);

						$superbox.find('.superbox-list').removeClass('active');
						$this.addClass('active');

						superboxdiv.find('h1').text(imgTitle);
						superboxdiv.find('.superbox-img-description').text(imgDescription);

						if(superboximg.css('opacity') == 0) {
							superboximg.animate({opacity: 1}, 300);
						}

						if ($(this).next().hasClass('superbox-show')) {
							$superbox.find('.superbox-list').removeClass('active');
							superbox.toggle();
						} else {
							superbox.insertAfter(this).css('display', 'block');
							$this.addClass('active');
						}

						$('html, body').animate({
							scrollTop:superbox.position().top // - currentimg.height()
						}, 'medium');

						superboximg.trigger('shown');
					});
				}
			});

			$superbox.on('click', '.superbox-close', function() {
				$superbox.find('.superbox-list').removeClass('active');
				$superbox.find('.superbox-current-img').trigger('hidden');
				$superbox.find('.superbox-current-img').animate({opacity: 0}, 300, function() {
					$superbox.find('.superbox-show').slideUp();
				});
			});

		});
	};
})(jQuery);