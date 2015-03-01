$(function() {
    initAddPhone();
    initAddEmail();
});

function initAddPhone() {
    if ( ! $("#newphone_nubmer").is('*') ) return;
    $('#newphone_nubmer').keypress(disableEnterKey);
    $('#newphone_name').keypress(disableEnterKey);
    var phones = [];
    var createPhone = function( index, phone ) {
        if ( !phone.name ) phone.name = '';
        return $('<div></div>')
            .text( phone.number + ' ' + phone.name )
            .append( $('<img style="margin: 0 5px -3px 3px; cursor: pointer;" width="15" height="15" src="/images/cancel.png" alt="">').click( function() {
                phones[index] = undefined;
                $('#phone_add').val( JSON.stringify(phones) );
                $(this).parent().remove();
            })
        );
    };
    if ( $('#phone_add').val() != '' ) {
        var phones = JSON.parse( $('#phone_add').val() );
        $.each( phones, function(i,phone) {
            if ( phone ) $('#additional_phones').prepend( createPhone(i, phone) );
        });
    }
    $('#newphone_add').click( function() {
        if ( $('#newphone_nubmer').val() == '' ) return;
        var value = {number: $('#newphone_nubmer').val(), name: $('#newphone_name').val()};
        $.ajax({
            url: '/companies/company/validate',
            type: 'post',
            data: { field: 'phone', value: $('#newphone_nubmer').val() },
            success: function( data ) {
                if ( data.success ) {
                    var cur = phones.length;
                    phones[cur] = value;
                    $('#phone_add').val( JSON.stringify(phones) );
                    $('#additional_phones').prepend( createPhone( cur, {number: data.value, name: $('#newphone_name').val()} ) );
                    if ( data.value == $('#newphone_nubmer').val() ) {
                        $('#newphone_nubmer').val('+56 2 ');
                        $('#newphone_name').val('');
                    }
                } else {
                    alert( data.message ); 
                }
            }
        });
    });
}

function initAddEmail() {
	if ( ! $("#newemail_email").is('*') ) return;
	$('#newemail_email').keypress(disableEnterKey);
	var emails = [];
	var createEmail = function( index, email ) {
		return $('<div></div>')
			.text( '' + email )
			.append( $('<img style="margin: 0 5px -3px 3px; cursor: pointer;" width="15" height="15" src="/images/cancel.png" alt="" />').click( function() {
				emails[index] = undefined;
				$('#email').val( JSON.stringify(emails) );
				$(this).parent().remove();
			})
		);
	};
	if ( $('#email').val() != '' ) {
		var emails = JSON.parse( $('#email').val() );
		$.each( emails, function(i,email) {
			if ( email ) $('#additional_emails').prepend( createEmail(i, email) );
		});
	}
	$('#newemail_add').click( function() {
		$.ajax({
			url: '/companies/company/validate',
			type: 'post',
			data: { field: 'email', value: $('#newemail_email').val() },
			success: function( data ) {
				if ( data.success ) {
					var cur = emails.length;
					emails[cur] = data.value;
					$('#email').val( JSON.stringify(emails) );
					$('#additional_emails').prepend( createEmail( cur, data.value ) );
					if ( data.value == $('#newemail_email').val() ) $('#newemail_email').val('');
				} else {
					alert( data.message ); 
				}
			}
		});
	});
}

function disableEnterKey(event) {
	if (event.keyCode == 13) {
		event.preventDefault();
		return false;
	}
}