
(function ($) {
    // Skillset Number Counter
    var initskillsetcounter = function () {
        $('.count').each(function () {
                $(this).prop('Counter', 0).animate({
                    Counter: $(this).text()
                }, {
                    duration: 4000,
                    easing: 'swing',
                    step: function (now) {
                            $(this).text(Math.ceil(now));
                    }
                });
        });
    };
    // Events
    var docReady = function () {
        initskillsetcounter();
    };
    $(docReady);
})(jQuery);
