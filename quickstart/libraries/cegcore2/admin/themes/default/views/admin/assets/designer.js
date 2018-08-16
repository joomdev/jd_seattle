jQuery(document).ready(function($){
	/*var tools_menu = $('<div class="ui label tools_menu">ggg</div>');
	$('.delement').each(function(i, element){
		var tools = tools_menu.clone();
		$(element).append(tools);
	});*/
	
	$('body').on('click', '.tools_menu', function(e){
		$(this).closest('.delement').children('.delement_config').first().transition('fly left');
	});
	
	$('body').on('mouseover', '.delement', function(e){
		if($(this).hasClass('active') == false){
			$(this).addClass('active');
		}
	});
	$('body').on('mouseleave', '.delement', function(e){
		if($(this).hasClass('active') == true){
			$(this).removeClass('active');
		}
	});
});