
function initSingleUploader(action, item, pathtoavatar, prefix_name) {
    var path = '/resources/uploadify/';
    var avafiles = '';
    
    if (prefix_name == null){
        prefix_name = '';
    }

    var createAvaPreview = function( name, path ) {
        if (!path) path = 'tmp';
        return $('<span></span>')
        .append('<img src="/files/' + path + '/' + name + '" />')
    };

    var createOriginalPreview = function( name, path ) {
        if (!path) path = 'post';
        return $('<span></span>')
        .append('<img src="/files/' + path + '/' + prefix_name + name + '" />')
    };

    $('#' + item).before('<div id="' + item + '-list"></div>');
    $('#' + item).after('<div id="' + item + '-uploader"></div>');
    $('#' + item+'-uploader').uploadify({
        'swf'            : path + 'uploadify.swf',
        'uploader'       : action,
        'checkExisting'  : true,
        'cancelImage'    : path + 'uploadify-cancel.png',
        'multi'          : false,
        'auto'           : true,
        'requeueErrors'  : false,
        'fileObjName'    : 'file',
        'fileTypeExts'   : '*.jpg;*.jpeg;*.gif;*.png',
        'fileTypeDesc'   : 'Изображения',
        'buttonText'     : 'Seleccione imagen',
        'fileSizeLimit'  : 10240,
        'onUploadSuccess' : function(file,data,response) {
            if (response) {
                $('#' + item + '-list').empty();
                var file = $.parseJSON(data);
                $('#' + item).val(file.name);
                $('#' + item + '-list').append(createAvaPreview( file.name, 'tmp'));
            }
        }

    });
    if ( $('#'+item).val() != '' ) {
        avafiles = $('#'+item).val();
        $.ajax({
            url: '/files/' + pathtoavatar + '/' + prefix_name + avafiles ,
            type: 'HEAD',
            error: function(){
                if ( avafiles ) $('#' + item + '-list').append( createAvaPreview(avafiles, 'tmp') );                
            },
            success: function(){                
                if (pathtoavatar == 'tmp') {
                    if ( avafiles ) $('#' + item + '-list').append( createAvaPreview(avafiles, pathtoavatar) );
                } else {
                    if ( avafiles ) $('#' + item + '-list').append( createOriginalPreview(avafiles, pathtoavatar) );
                }
            }
        });            
        
    }

}