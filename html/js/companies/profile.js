$(function() {
	initLike();
});

function initLike() {
	$('#company-like').click( function() { 
		var el = $(this);
		$.ajax({
			url: el.attr('href'),
			type: 'post',
			success: function( data ) {
				if ( parseInt(data) > 0 ) {
					el.html( '<a class="pink-grey nl" href="/company18/likeit" id="company-like"><img class="COLLECTION men-lk-1" src="/sprites/null.png" width="22" height="22" alt="" /></a>' );
				} else {
					el.html( '<a class="grey-pink nl" href="/company18/likeit" id="company-like"><img class="COLLECTION men-lk-0" src="/sprites/null.png" width="22" height="22" alt="" /></a>' );
				}
			}
		});
		return false;
	});
}
	