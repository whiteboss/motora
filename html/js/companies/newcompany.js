$(function() {
    $('#activity_sphere').change( updateActivities );
    $('#representative').change( updatePosition );
    //updateActivities();
    updatePosition();
    initClearPosition();
    updateCatElements();
    $('#activity_sphere').change( function() {
            $('#type-element').find('input:radio').removeAttr('checked');
            updateCatElements();
    });
    $('#type-element').find('input:radio').change( updateCatElements );        
});

function updateCatElements() {
    if ( $('#type-element').find('input:checked').is('*') ) {
        $('#activity_sphere').css('color', 'grey');
        $('#activity_type').css('color', 'grey');
        $('#type-element > label').css('color', 'inherit');
    } else {
        $('#activity_sphere').css('color', 'inherit');
        $('#activity_type').css('color', 'inherit');
        $('#type-element > label').css('color', 'grey');
    }
}

function updateActivities() {
    $('#activity_type').empty();
    $.ajax({
        url: 'companies/activities',
        type: 'get',
        data: { sphere: $('#activity_sphere').val() },
        error: function() {
            throw 'Error on retrieving activity types';
        },
        success: function( data ) {
            var ne = $(data);
            ne.find('select').change( function() {
                $('#type-element').find('input:radio').removeAttr('checked');
                updateCatElements();
            });
            $('#activity_type_pos').html( ne );
            $(".chzn-select").chosen();
            updateCatElements();            
        }
    });
}

function updatePosition() {
    
    if ($('#representative').is(':checked') )         
        $('#owner_position').show();                
    else
        $('#owner_position').hide();
}

function initClearPosition() {
    $('#position').focus( function() {
        if ( $(this).val() == 'Представитель' ) $(this).val('');
    });
    $('#position').blur( function() {
        if ( $(this).val() == '' ) $(this).val('Представитель');
    });
}