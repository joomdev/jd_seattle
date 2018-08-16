/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

jQuery(function($) {

	var $container 	= $('.sp-simpleportfolio-items');

	$(window).load(function() {
		var $sizer = $container.find('.shuffle__sizer');

		$container.shuffle({
			itemSelector: '.sp-simpleportfolio-item',
			sequentialFadeDelay: 150,
			sizer: $sizer
		});
	});


	// Filters
	$('.sp-simpleportfolio-filter li a').on('click', function(event){
		event.preventDefault();
		var $self = $(this);
		var $this = $(this).parent();

		if($this.hasClass('active')) {
			return;
		}

		$self.closest('ul').children().removeClass('active');
		$self.parent().addClass('active');

		var $local = $self.closest('.sp-simpleportfolio').children('.sp-simpleportfolio-items');
		
		$local.shuffle( 'shuffle', $this.data('group') );
	});

});
