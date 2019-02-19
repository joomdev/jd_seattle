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
		var checks = $('#jform_billing_type1,#jform_billing_type0');
		checks.change(function() {
			var val = checks.filter(':checked').val();
            if (val == '1') {
				$('.ot-domestic').hide();
				$('.ot-international').show();
			}
			else {
				$('.ot-domestic').show();
				$('.ot-international').hide();
			}
		}).trigger('change');
	});
});
