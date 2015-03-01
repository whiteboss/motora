$(function() {
    initToggleVisible();
    initToggleBest();
    initLikeDish();
});

function initToggleVisible() {
    $('#toggle-button').live('click', function() {
        var el = $(this);
        $.ajax({
            url: el.attr('href'),
            type: 'post',
            success: function( data ) {
                if ( data == '1' )
                    el.text('Скрыть');
                else
                    el.text('Показать');
            }
        });
        return false;
    });
}

function initToggleBest() {
    $('#best-button').live('click', function() {
        var el = $(this);
        $.ajax({
            url: el.attr('href'),
            type: 'post',
            success: function( data ) {
                if ( data == '1' )
                    el.css('font-weight', 'bold');
                else
                    el.css('font-weight', '');
            }
        });
        return false;
    });
}

function initLikeDish() {
    $('#dish-like').live('click', function() {
        var el = $(this);
        $.ajax({
            url: el.attr('href'),
            type: 'post',
            success: function( data ) {
                el.html( data );
            }
        });
        return false;
    });
}