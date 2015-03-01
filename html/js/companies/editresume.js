$(function() {
	initAddWork();
	initAddInstitute();
	initAddLanguage();
});

function initAddWork() {
	if ( ! $("#newwork_company").is('*') ) return;
	$('#newwork_company').keypress(disableEnterKey);
	$('#newwork_position').keypress(disableEnterKey);
	var rows = [];
	var createRow = function( index, row ) {
		if ( !row.position ) row.position = '';
		if ( !row.responsibility ) row.responsibility = '';
		if ( row.end == '0' ) row.end = 'настоящее время';
		return $('<div class="standard_element_container"></div>')
			.text( row.start + '-' + row.end + ' ' + row.company + ' ' + row.position )
			.append( $('<img style="margin: 0 5px -3px 3px; cursor: pointer;" width="15" height="15" src="/images/cancel.png" alt="" />').click( function() {
				rows[index] = undefined;
				$('#work').val( JSON.stringify(rows) );
				$(this).parent().remove();
			})
		);
	};
	if ( $('#work').val() != '' ) {
		var rows = JSON.parse( $('#work').val() );
		$.each( rows, function(i,row) {
			if ( row ) $('#list-work').prepend( createRow(i, row) );
		});
	}
	$('#newwork_add').click( function() {
		if ( $('#newwork_company').val() == '' ) {
			alert('Название компании не заполнено.');
			return;
		}
		if ( ($('#newwork_end').val()!='0') && ($('#newwork_start').val() > $('#newwork_end').val()) ) {
			alert('Дата начала больше даты конца.');
			return;
		}
		var value = {
			start: $('#newwork_start').val(),
			end: $('#newwork_end').val(),
			company: $('#newwork_company').val(),
			position: $('#newwork_position').val(),
			responsibility: $('#newwork_responsibility').val()
		};
		var cur = rows.length;
		rows[cur] = value;
		$('#work').val( JSON.stringify(rows) );
		$('#list-work').prepend( createRow(cur, value) );
		$('#newwork_company').val('');
		$('#newwork_position').val('');
		$('#newwork_responsibility').val('');
	});
}

function initAddInstitute() {
	if ( ! $("#newinstitute_name").is('*') ) return;
	$('#newinstitute_name').keypress(disableEnterKey);
	$('#newinstitute_speciality').keypress(disableEnterKey);
	var rows = [];
	var createRow = function( index, row ) {
		if ( !row.speciality ) row.speciality = '';
		if ( row.end == '0' ) row.end = 'настоящее время';
		return $('<div class="standard_element_container"></div>')
			.text( row.start + '-' + row.end + ' ' + row.name + ' ' + row.speciality )
			.append( $('<img style="margin: 0 5px -3px 3px; cursor: pointer;" width="15" height="15" src="/images/cancel.png" alt="" />').click( function() {
				rows[index] = undefined;
				$('#institute').val( JSON.stringify(rows) );
				$(this).parent().remove();
			})
		);
	};
	if ( $('#institute').val() != '' ) {
		var rows = JSON.parse( $('#institute').val() );
		$.each( rows, function(i,row) {
			if ( row ) $('#list-education').prepend( createRow(i, row) );
		});
	}
	$('#newinstitute_add').click( function() {
		if ( $('#newinstitute_name').val() == '' ) {
			alert('Название учебного заведения не заполнено.');
			return;
		}
		if ( ($('#newinstitute_end').val()!='0') && ($('#newinstitute_start').val() > $('#newinstitute_end').val()) ) {
			alert('Дата начала больше даты конца.');
			return;
		}
		var value = {
			start: $('#newinstitute_start').val(),
			end: $('#newinstitute_end').val(),
			name: $('#newinstitute_name').val(),
			speciality: $('#newinstitute_speciality').val()
		};
		var cur = rows.length;
		rows[cur] = value;
		$('#institute').val( JSON.stringify(rows) );
		$('#list-education').prepend( createRow(cur, value) );
		$('#newinstitute_name').val('');
		$('#newinstitute_speciality').val('');
	});
}

function initAddLanguage() {
	if ( ! $("#newlang_language").is('*') ) return;
	$('#newlang_language').keypress(disableEnterKey);
	var rows = [];
	var createRow = function( index, row ) {
                var level = '';
		switch (row.level) {
			case '0': level = 'базовые знания'; break;
			case '1': level = 'владею разговорным'; break;
                        case '2': level = 'владею техническим'; break; 
			case '3': level = 'свободно владею'; break;
		}
		return $('<div class="standard_element_container"></div>')
			.text( row.language + ' ' + level )
			.append( $('<img style="margin: 0 5px -3px 3px; cursor: pointer;" width="15" height="15" src="/images/cancel.png" alt="" />').click( function() {
				rows[index] = undefined;
                                $('#languages').val( JSON.stringify(rows) );
				$(this).parent().remove();
			})
		);
	};
	if ( $('#languages').val() != '' ) {
		var rows = JSON.parse( $('#languages').val() );
		$.each( rows, function(i, langrow) {
			if ( langrow ) $('#list-languages').prepend( createRow(i, langrow) );
		});
	}
	$('#newlang_add').click( function() {
		if ( $('#newlang_language').val() == '' ) {
			alert('Название языка не заполнено.');
			return;
		}
		var value = {
			language: $('#newlang_language').val(),
			level: $('#newlang_level').val()
		};
		var cur = rows.length;
		rows[cur] = value;
		$('#languages').val( JSON.stringify(rows) );
		$('#list-languages').prepend( createRow(cur, value) );
		$('#newlang_language').val('');
                $('#newlang_level').val('');
	});
}

function disableEnterKey(event) {
	if (event.keyCode == 13) {
		event.preventDefault();
		return false;
	}
}