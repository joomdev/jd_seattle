/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */


Joomla = window.Joomla || {};

Joomla.submitbutton = function (task, form) {
    form = form || document.getElementById('adminForm');

    if (document.formvalidator.isValid(form)) {
        Joomla.submitform(task, form);
    } else {
        alert('Please fill all the required values before placing an order.');
    }
};

var SellaciousViewCartShipper = function (shippers) {
    return this;
};

jQuery(function ($) {
    SellaciousViewCartShipper.prototype = {
        init: function (shippers) {
            var that = this;
            that.shippers = shippers || {};

            $(document).ready(function () {
                $('select.shipper-list').change(function () {
                    that.calculate(this);
                }).trigger('change');
            });

            return this;
        },

        calculate: function (el) {
            var uid = $(el).data('uid');
            var val = $(el).val();

            var rates = this.shippers[uid] || {};
            var rate = rates[val] || {};

            rate = isNaN(rate) ? 0 : parseFloat(rate);
            $(el).data('rate', rate);

            var $row = $(el).closest('tr');
            $row.find('.ship-cost').text('$' + rate.toFixed(2));
            $row.find('.shipping_charge').val(rate.toFixed(2));

            var total = 0;
            $('select.shipper-list').each(function () {
                total += $(el).data('rate') || 0;
            });

            $('#ship-total').text(total.toFixed(2));
        }
    };
});
