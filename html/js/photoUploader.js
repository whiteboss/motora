// $action - скрипт обработки загрузки изображений
// $multi - true/false мультизагрузка
//

function initUploader(action, multi, item, pathtogallery, limit, prefix_name) {

    if (limit == null){
        limit = 5;
    }
    
    if (prefix_name == null){
        prefix_name = '';
    }

    var path = '/resources/uploadify/';
    var files = [];
    var createPreview = function( index, name, path ) {
        if (name != '')            
            return $('<span></span>')
            .append('<img class="Qloadedphoto" src="/files/' + path + '/' + prefix_name + name + '" />')
            .append( $('<img class="Icancel" width="73" height="21" src="/zeta/0.png" />').click( function() {
                files[index] = undefined;
                $('#'+item).val( JSON.stringify(files) );
                $(this).parent().remove();
                $('#'+item+'-uploader').uploadifySettings('uploadLimit', limit + 1)
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
        //'fileSizeLimit'  : 5000,
        'maxQueueSize'   : 2,
        'uploadLimit'    : limit,
        'onUploadSuccess' : function(file,data,response) {
            if (response) {
                if (!multi) {
                    $('#' + item + '-list').empty();
                    files[0] = data.replace("\r\n","");
                } else {
                    files.push(data.replace("\r\n",""));
                }
                $('#'+item).val( JSON.stringify(files) );
                $('#'+item+'-list').append( createPreview(file.index, data, 'tmp') );
            }
        }
    });

    if ( $('#'+item).val() != '' ) {
        files = JSON.parse( $('#'+item).val() );
        $.each( files, function(i,name) {
            $.ajax({
                url: '/files/' + pathtogallery + '/' + prefix_name + name ,
                type: 'HEAD',
                error: function(){
                    if ( name ) $('#' + item + '-list').append( createPreview(i, name, 'tmp') );
                },
                success: function(){
                    if ( name ) $('#' + item + '-list').append( createPreview(i, name, pathtogallery) );
                }
            });            
        });
    }
}
