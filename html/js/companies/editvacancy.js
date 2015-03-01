$(function() {

	$('#vacancy-toggle').live('click',function() {
            var el = $(this);
            $.ajax({
                    url: el.attr('href'),
                    type: 'post',
                    success: function( data ) {
                        el.html(data);
                    }
            });
            return false;
	});

});
