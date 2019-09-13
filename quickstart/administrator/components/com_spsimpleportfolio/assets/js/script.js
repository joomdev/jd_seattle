jQuery(function($) {
    $('.action-edit-width-sppb').on('click', function(event) {
        event.preventDefault();

        var data = {
            'title': $(this).data('title'),
            'extension': 'com_spsimpleportfolio',
            'extension_view': 'item',
            'view_id': $(this).data('id'),
        }

        $.ajax({
            url: 'index.php?option=com_sppagebuilder&task=page.createNew',
            type: 'POST',
            data: data,
            success: function (response) {
                var data = $.parseJSON(response);
                if (data.status) {
                    $(this).removeClass('action-edit-width-sppb').attr('href', data.url);
                    window.location.href = data.url;
                }
            }
        });
    })
});