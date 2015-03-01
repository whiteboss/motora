function initDeleteDialog() {
    var dialog = $('#dialog-del-form').dialog({
        autoOpen: false,
        height: 300,
        width: 450,
        modal: true
    });
    $('.del-button').click( function() {
        var el = $(this);
        dialog.dialog('option', 'buttons', {
            "Удалить": function() {
                $('#delete_item_form').ajaxSubmit({
                    url: el.attr('href'),
                    error: function() {
                        throw 'Error on deleting item';
                    },
                    success: function() {
                        el.replaceWith( 'Удален' );
                        dialog.dialog('close');
                    }
                });
            },
            "Отменить": function() {
                dialog.dialog('close');
            }
        });
        dialog.dialog('open');
        return false;
    });
}