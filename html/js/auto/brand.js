$(function() {

    $('#brand').click(function() {
        $('#brand-list').toggle();
        $('#model-list').hide();
    });
    
    $('#brand-close').click(function() {
        $('#brand-list').hide();
    });
    
    $('#model').click(function() {
        $('#model-list').toggle();
        $('#brand-list').hide();        
    });
    
    $('#model-close').click(function() {
        $('#model-list').hide();
    });    

});