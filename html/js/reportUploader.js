// $action - скрипт обработки загрузки изображений
// $multi - true/false мультизагрузка
//

function initReportUploader(action, multi, item, pathtogallery) {

    var path = '/resources/uploadify/';
    var files = [];
    var createPreview = function( index, name, path ) {
        if (name != '')            
            return $('<span></span>')
            .append('<img class="Qloadedphoto" src="/files/' + path + name + '" />')
            .append( $('<img class="Icancel" width="73" height="21" src="/zeta/0.png" />').click( function() {
                files[index] = undefined;
                $('#'+item).val( JSON.stringify(files) );
                $(this).parent().remove();
            }) );
    };
    $('#'+item).before('<div id="'+item+'-list"></div>');
    $('#'+item).after('<div id="'+item+'-uploader"></div>');
    $('#'+item+'-uploader').uploadify({
        'swf'            : path + 'uploadify.swf',
        'uploader'       : action,
        'checkExisting'  : false,
        'cancelImage'    : path + 'uploadify-cancel.png',
        'multi'          : multi,
        'auto'           : true,
        'requeueErrors'  : false,
        'fileObjName'    : 'file',
        'fileTypeExts'   : '*.jpg;*.jpeg;*.gif;*.png',
        'fileTypeDesc'   : 'Изображения',
        'buttonText'     : 'Seleccione imagenes',
        'maxQueueSize'   : 1,
        'onUploadSuccess' : function(file,data,response) {
            if (response) {
                if (!multi) {
                    $('#' + item + '-list').empty();
                    files[0] = data.replace("\r\n","");
                } else {
                    files.push(data.replace("\r\n",""));
                }
                $('#'+item).val( JSON.stringify(files) );
                $('#'+item+'-list').append( createPreview(file.index, data, 'tmp/bage_') );
            }
        }
    });

    if ( $('#'+item).val() != '' ) {
        files = JSON.parse( $('#'+item).val() );
        $.each( files, function(i,name) {
            $.ajax({
                url: '/files/' + pathtogallery + '/' + name ,
                type: 'HEAD',
                error: function(){
                    if ( name ) $('#' + item + '-list').append( createPreview(i, name, 'tmp/') );
                },
                success: function(){
                    if ( name ) $('#' + item + '-list').append( createPreview(i, name, pathtogallery) );
                }
            });            
        });
    }
}
