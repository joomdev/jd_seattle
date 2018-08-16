(function($){
	if($.G2 == undefined){
		$.G2 = {};
	}
	$.G2.composer = {};
	
	$.G2.scrollTo = function(Elem){
		if(Elem.length > 0){
			$('html, body').animate({
				scrollTop: Elem.offset().top - 50
			}, 'slow');
		}
	};
	
	$.G2.centerOn = function(Elem){
		if(Elem.length > 0){
			$('html, body').animate({
				scrollTop: Elem.offset().top - $(window).height()/2
			}, 'slow');
		}
	};
	
	$.G2.split = function(inputs, maxcount){
		var data = {};
		if(inputs.length > maxcount){
			for(i = 0; i <= inputs.length; i = i + maxcount){
				data[i] = inputs.slice(i, i + maxcount).serialize();
			}
		}else{
			data[0] = inputs.serialize();
		}
		
		return data;
	};
	
	$.G2.composer.init = function(){
		var section = arguments[0];
		var args = arguments[1];
		
		$.G2.composer[section] = {};
		$.G2.composer[section].params = args;
	};
	
	$.G2.composer.ready = function(){
		var section = arguments[0];
		var args = arguments[1];
		
		$.extend($.G2.composer[section].params, args);
		
		$.each($.G2.composer[section].params, function(i, arr){
			$.G2[i]['ready'].apply($.G2[i], arr);
		});
	};
}(jQuery));