$(function() {

    var delay = (function(){
      var timer = 0;
      return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
      };
    })();

    $('#company_search').keyup(function() {        
        delay(function(){
            filterCompanies();
        }, 1000 );        
    });
    
});

function filterCompanies() {

    $.ajax({
        url: '/companies/company/filter',
        type: 'get',
        data: {
            search_str: $('#company_search').val()
        },
        error: function() {
            throw 'Error on retrieving data';
        },
        success: function( data ) {
            $('#company_list').html( data );
        }
    });
}