$(function() {

//    $('.choose_some').click(function() {
//        $('#car_models').toggle();
//    });
//    $('#search_toggle').click(function() {
//        $('#filter_block').toggle(0,function () {
//            if ($(this).css('display') == 'none') {
//                $('#search_toggle').html('<img class="coll" src="/sprites/null.png" width="20" height="18" alt="" />Изменить параметры поиска');
//            } else {
//                $('#search_toggle').html('<img class="coll" src="/sprites/null.png" width="20" height="18" alt="" />Свернуть параметры поиска');
//            }
//        });        
//    });
//    $('.filter_models_else').click(function() {
//        $('#else_brand_list').toggle(0,function () {
//            if ($(this).css('display') == 'none')
//                $('.filter_models_else').text('Показать все марки');
//            else
//                $('.filter_models_else').text('Оставить самые популярные');
//        });         
//    });

//    $('#sliders_field').find(':input').change( countItems );
//    $.each( ['body', 'brand'], function(i,n) {
//            $('#'+n+'_list').find('input:checkbox').change( function() {
//                updateModelList();
//                $(this).parent('label').toggleClass('active');
//            })
//            .each( function() {
//                $(this).filter(':checked').parent('label').addClass('active');
//            });
//            $("label img").live("click", function() {
//              $("#" + $(this).parents("label").attr("for")).click();
//            });
//    });
//
//    var body_ids = $('#body_list').find(':checked').map( function(i, n) {
//        return $(n).val();
//    });
//    var brand_ids = $('#brand_list').find(':checked').map( function(i, n) {
//        return $(n).val();
//    });
    //updateModelList();
    $('#mark').change( updateSeries );    
    $('#serie').change( updateRanges );
    //updateSeries();

    //if (body_ids.length > 0 || brand_ids.length > 0) $('#sliders_field').show();
            
    initSliders();
    
//    $('#model_list').find('input:checkbox').change( updateRanges );
    
//    // чекбоксы
//    $('#selectall').live('click',function(){
//        var el = $(this);
//        $('#' + $(this).attr('rel')).find('input:checkbox').attr('checked', true);              
//        el.attr('id', 'deselectall');
//        el.html('снять все <img class="coll mod-todos" src="/sprites/null.png" width="14" height="11" alt="" />');
//        updateRanges();
//    });
//    $('#deselectall').live('click',function(){
//        var el = $(this);
//        $('#' + $(this).attr('rel')).find('input:checkbox').attr('checked', false);              
//        el.attr('id', 'selectall');
//        el.html('выбрать все <img class="coll mod-todos1" src="/sprites/null.png" width="14" height="11" alt="" />');
//        updateRanges();
//    });
//    
//    $(window).load(function(){
//        $("#sticker").sticky({topSpacing: 50});
//        $('div#sticker').click(function () {
//            $('body,html').animate({
//                    scrollTop: 410
//            }, 400);
//            return false;
//        });
//    });

});

function initSliders()
{
    $('.range').each( function() {
        var from = $('#'+$(this).attr('id')+'_from');
        var to = $('#'+$(this).attr('id')+'_to');
        var slider = $('<div>')
        .attr('id', $(this).attr('id')+'_slider')
        .slider({
            range: true,
            slide: function( event, ui ) {
                from.val( ui.values[0] );
                to.val( ui.values[1] );
            },
            change: countItems
        }).insertBefore($(this));
        from.change( function() {
            slider.slider('values', 0, from.val());
        });
        to.change( function() {
            slider.slider('values', 1, to.val());
        });
    });
    $('#mileage_slider').slider('option', 'step', 1000);
    $('#engine_volume_slider').slider('option', 'step', 100);
}

function updateSeries() {
    
    $.ajax({
        url: '/auto/model/getseries',
        type: 'get',
        dataType: 'json',
        data: {
            mark: $('#mark').val(),
            with_null: 1
        },
        error: function() {
            throw 'Error on retrieving series select form-part';
        },
        success: function(data) {
            $("#serie").empty();
            if (data.length > 0) {                
                for (var i = 0; i < data.length; i++) {
                    $("<option/>").attr("value", data[i].id).text(data[i].name).appendTo($("#serie"));
                }
                //updateModels();
                updateRanges();
            }
        }
    });
    
}

function updateModels() {
//    var body_ids = $('#body_list').find(':checked').map( function(i, n) {
//        return $(n).val();
//    });
//    var brand_ids = $('#brand_list').find(':checked').map( function(i, n) {
//        return $(n).val();
//    });
//    $.ajax({
//        url: '/auto/model/formpart',
//        type: 'get',
//        data: {
//            body: body_ids,
//            brand: brand_ids
//        },
//        error: function() {
//            throw 'Возникли проблемы при подготовке результатов операции';
//        },
//        success: function( data ) {
//            $('#model_list').replaceWith( data );
//            if ( $('#model_list').find('input:checkbox').is('*') ) {
//                $('#model_list').find('input:checkbox').change( function() {
//                    if ($.browser.msie) {
//                        $(this).toggleClass('proveril_menyaetsya');
//                    }    
//                    updateRanges();
//                });                
//                $('#model_list1').find('input:checkbox').change( function() {
//                    if ($.browser.msie) {
//                        $(this).toggleClass('proveril_menyaetsya1');
//                    }    
//                    updateRanges();
//                });
//                updateRanges();
//                $('#sliders_field').show();
//            } else {
//                $('#sliders_field').hide();
//                $('#auto_load_icon').hide();
//                $('#search_results').html('<div class="res-showme"><span><nobr>По заданным условиям, предложений нет</nobr></span></div>');
//            }
//        }
//    });
}

var updating = false; // to block unnecessary requests

function updateRanges( reset ) {
    
    
    
//    if (typeof reset == "undefined") reset = true;
//    var body_ids = $('#body_list').find(':checked').map( function(i, n) {
//        return $(n).val();
//    });
//    var brand_ids = $('#brand_list').find(':checked').map( function(i, n) {
//        return $(n).val();
//    });
    var series_ids = $('#model_list').find(':checked').map( function(i, n) {
        return $(n).val();
    });
    $.ajax({
        url: '/auto/car/ranges',
        type: 'get',
        data: {
            //body: body_ids,
            mark: $('#mark').val(),
            series: $('#serie').val()
        },
        error: function() {
            throw 'Возникли проблемы при подготовке результатов операции';
        },
        success: function( data ) {
            if ( !data) return;
            updating = true;
            $.each( data, function(name, v) {
                var from = parseInt( $('#'+name+'_from').val() );
                if ( reset || isNaN(from) || (from < v[0]) || (from > v[1]) ) {
                    $('#'+name+'_from').val(v[0]);
                    from = v[0];
                }
                
                var to = parseInt( $('#'+name+'_to').val() );
                
                if ( reset || isNaN(to) || (to > v[1]) || (to < v[0]) ) {
                    $('#'+name+'_to').val(v[1]);
                    to = v[1];
                }
                if (from > to) {
                    $('#'+name+'_to').val(from);
                    to = from;
                }
                $('#'+name+'_slider').slider('option', {
                    min: v[0],
                    max: v[1],
                    values: [from, v[1]]
                });
            });
            updating = false;
            countItems();
        }
    });
}


function countItems() {
    if ( updating ) return;

    //$("#car_search_form").ajaxSend(function(evt, request, settings){
        $('#search_results').html('');
        $('#auto_load_icon').show();
    //});
    $('#car_search_form').ajaxSubmit({
        url: '/auto/car/count',
        error: function() {
            $('#auto_load_icon').hide();
            throw 'Небольшие проблемы в гараже. Пожалуйста, попробуйте еще раз.';
        },
        complete: function() {
            $('#auto_load_icon').hide();
        },
        success: function( data ) {
            $('#search_button').hide();
            if (data.count > 0) {
                $('#search_results').html('<div id="estate_load_icon" class="auto-finded" onclick="javascript:document.car_search_form.submit()"><span>Найдено '+data.count+' авто</span></div>');
                $('#search_button').show();	    
	    } else {
                $('#search_results').html('<div id="estate_load_icon" class="auto-finded estate_her_tebe">Таких предложений нет</div>');
                $('#search_button').show();
            }
        }
    });
}