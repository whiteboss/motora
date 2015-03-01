$(function() {
	$('ul.sliding > li > ul').hide();
	$('ul.sliding > li > a').click( function() {
		$(this).nextAll('ul').slideToggle();
		return false;
	});
	$('input:hidden.imagify').each( function() {
		$(this).replaceWith( $('<img src="/files/' + $(this).val() + '" width="200" height="200">') ); 
	});
	if ( $('#description').is('textarea') ) {
		$('#description').redactor({ upload: '/auto/admin/upload/editor/1' });
	}
	if ( $("#photo").is('input') ) {
		initUploader();
	}
});

function initUploader() {
	/*var start = document.cookie.indexOf("PHPSESSID=");
	var end = document.cookie.indexOf(";", start); // First ; after start
	if (end == -1) end = document.cookie.length; // failed indexOf = -1
	var cookie = document.cookie.substring(start+10, end);*/
	var path = '/resources/uploadify/';
	var files = [];
	var createPreview = function( index, name ) {
		return $('<span></span>')
			.append('<img width="200" height="150" src="/files/model/' + name + '" />')
			.append( $('<img src="/resources/uploadify/uploadify-cancel.png" />').click( function() {
				var img = $(this);
				files[index] = undefined;
				$('#photo').val( JSON.stringify(files) );
				img.parent('span').remove();
			})
		);
	};
	$('#photo').before('<div id="photo-list"></div>');
	$('#photo').after('<div id="uploader"></div>');
	$('#uploader').uploadify({
		'swf'            : path + 'uploadify.swf',
		'uploader'       : '/auto/admin/upload',
		//'postData'       : { 'session': cookie},
		'checkExisting'  : false,
		'cancelImage'    : path + 'uploadify-cancel.png',
		'multi'          : true,
		'auto'           : true,
		//'debug'          : true,
		//'removeCompleted': false,
		'requeueErrors'  : false,
		'fileObjName'    : 'file',
		'fileTypeExts'   : '*.jpg;*.jpeg;*.gif;*.png',
		'fileTypeDesc'   : 'Изображения',
		'buttonText'     : 'Выбрать файлы',
		'fileSizeLimit'  : 5000,
		'onUploadSuccess' : function(file,data,response) {
			if (response) {
				files[file.index] = data;
				$('#photo').val( JSON.stringify(files) );
				$('#photo-list').append( createPreview(file.index, data) );
			}
		}
	});
	if ( $('#photo').val() != '' ) {
		files = $.parseJSON( $('#photo').val() );
		$.each( files, function(i,name) {
			if ( name ) $('#photo-list').append( createPreview(i, name) );
		});
	}
}