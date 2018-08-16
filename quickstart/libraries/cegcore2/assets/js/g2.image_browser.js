(function($){
	$.G2.image_browser = {};
	
	$.G2.image_browser.ready = function(parent, image_class, container_class){
		var browser = $('.ui.page.dimmer.image-browser');
		$(parent).find(image_class).dimmer({on : 'hover'});
		
		$(parent).find(image_class).each(function(i, image){
			$(image).find('.button').on('click', function(){
				$(image).dimmer('hide');
				browser.find('.content').empty();
				browser.find('.content').append($(image).find('img').clone().removeClass('small tiny mini').css('max-height', $(window).height() - 50));
				browser.dimmer('show');
				
				browser.find('.close-button').on('click', function(){
					browser.dimmer('hide');
				});
				
				var list = $(image).closest(container_class).find(image_class);
				var index = 0;
				list.each(function(k, item){
					if(image === item){
						index = k;
					}
				});
				
				browser.find('.next-button').removeClass('inverted');
				if(index < list.length - 1){
					browser.find('.next-button').addClass('inverted');
					browser.find('.next-button').off('click');
					browser.find('.next-button').on('click', function(){
						$(list[index + 1]).find('.button').trigger('click');
					});
				}
				
				browser.find('.prev-button').removeClass('inverted');
				if(index > 0){
					browser.find('.prev-button').addClass('inverted');
					browser.find('.prev-button').off('click');
					browser.find('.prev-button').on('click', function(){
						$(list[index - 1]).find('.button').trigger('click');
					});
				}
			});
		});
	};
	
}(jQuery));