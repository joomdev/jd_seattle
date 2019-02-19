(function($) {
	$(document).ready(function () {
		  // The rate at which the menu expands revealing child elements on click
		  var $menu_speed = 235;

		// Initialize Left NAV
		$('nav ul').jarvismenu({
			accordion : true,
			speed : $menu_speed,
			closedSign : '<em class="fa fa-plus-square-o"></em>',
			openedSign : '<em class="fa fa-minus-square-o"></em>'
		});
	});
})(jQuery);
