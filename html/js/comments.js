var mouse_is_inside = false;
$(function() {

    $("#addCommentGeneral").click(function(){
        $.post("/comments/addcomment",
        {
            object_id: $("#object_id").val(),
            object_type: $("#object_type").val(),
            comment: $("#comment").val()
        },
        function(data)
        {
             $(data).appendTo('#commentsList');
             $('#comment').val('');
        });
    });

    $('#comment').keyup(function() {
        if ($('#comment').val().length > 1) {
            $('#addCommentGeneral').removeAttr('disabled');
        } else {
            $('#addCommentGeneral').attr('disabled', 'disabled');
        }
    });

    $('#comment').keypress(function(event)
    {
        if ((event.ctrlKey) && ((event.keyCode == 0xA)||(event.keyCode == 0xD))) {
            $("#addCommentGeneral").click();
        }
    })
    
    $(document).on('click', '#rate_in', function(){
        var el = $(this);
        $.post('comments/ratein', {id : $(this).attr('rel')}, function(data){
            el.parent().html('<span id="rate_out" rel="' + el.attr('rel') + '" class="pseudoactive f10">Me gusta</span> <em id="rate_value" class="grey f10">' + data + '</em>');
        });
    })
    
    $(document).on('click', '#rate_out', function(){
        var el = $(this);
        $.post('comments/rateout', {id : $(this).attr('rel')}, function(data){
            el.parent().html('<span id="rate_in" rel="' + el.attr('rel') + '" class="pseudolink f10">Me gusta</span> <em id="rate_value" class="grey f10">' + data + '</em>');            
        });
    })
    
});

function addCommentForm(objectId)
{
    $("#parent").val("");
    $("#subComment").remove();

    $("#parent").val(objectId);

    var commentForm = "<li class='mb30 pull-left w100' id='subComment'><div class='Cavatar'></div><div class='Ctext'><textarea id='textComment' placeholder='Añade un comentario...' class='cm-nw'></textarea><input id='addComment' class='standard_save_button' onclick='addComment()' type='button' value='Comentar' disabled='' /><span class='f9 grey ctrlenter'>Ctrl+Enter</span></div></li>";
    var childrenObj = $('#commentId_'+objectId).children("ul");
    if (childrenObj.length)
        $(commentForm).insertBefore(childrenObj);
    else
        $(commentForm).appendTo('#commentId_'+objectId);
    
    $('#textComment').focus();

    $('#textComment').keyup(function() {
        if ($('#textComment').val().length > 1) {
            $('#addComment').removeAttr('disabled');
        } else {
            $('#addComment').attr('disabled', 'disabled');
        }
    });
    
    $('#textComment').keypress(function(event) {
        if ((event.ctrlKey) && ((event.keyCode == 0xA)||(event.keyCode == 0xD))) {
            $("#addComment").click();
        }
    }) 
    
    $("#subComment").hover(function(){ 
        mouse_is_inside=true; 
    }, function(){ 
        mouse_is_inside=false; 
    });
    
    $("body").mouseup(function(){ 
        if(! mouse_is_inside) $("#subComment").hide();
    });
    
}

function addComment()
{
    
    var ulLevel = $("#textComment").parent().parent().parent("li").parent("ul");
    var ulId = $(ulLevel).attr("id");
    if (ulId != undefined) ulId = ulId.split("_");
    
    $.ajax({
        url: '/comments/addcomment/',
        type: 'post',
        data: { 
            object_id: $("#object_id").val(),
            object_type: $("#object_type").val(),
            comment: $("#textComment").val(),
            parent:  $("#parent").val(),
            ulId: ulId[1]
        },
        error: function() {
            throw 'Internal error';
        },
        success: function(data) {
            if(ulId != undefined && ulId[1]==2){
                $(data).appendTo($(ulLevel));
            } else {
                $(data).appendTo('#commentId_'+$("#parent").val());
            }
            $("#parent").val("");
            $("#subComment").remove();
        }
    });

}