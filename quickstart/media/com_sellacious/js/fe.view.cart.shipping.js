/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


jQuery(function($) {
	$(document).ready(function() {
		var s_types = $('#jform_shipping_type1,#jform_shipping_type0');
		s_types.change(function() {
			var val = s_types.filter(':checked').val();
            if (val == '1') {
				$('.ot-international').show();
				$('.ot-domestic').hide();
			}
			else {
				$('.ot-international').hide();
				$('.ot-domestic').show();
			}
		}).trigger('change');

		var sab = $('#jform_shipping_same_as_billing1,#jform_shipping_same_as_billing0');
		sab.change(function() {
			var val = sab.filter(':checked').val();
            if (val == '1') {
				$('.cart-sab-yes').show();
				$('.cart-sab-no').hide();
			}
			else {
				$('.cart-sab-yes').hide();
				$('.cart-sab-no').show();
			}
		}).trigger('change');
	});
});
