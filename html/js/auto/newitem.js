var mods;

$(function() {
    $('#mark').change(updateSeries);
    $('#serie').change(updateModels);
    $('#id_car_model').change(updateModification);
    $('#id_car_modification').change(changeModification);
});

function updateSeries() {
    $.ajax({
        url: '/auto/model/getseries',
        type: 'get',
        dataType: 'json',
        data: {mark: $('#mark').val()},
        error: function() {
            throw 'Error on retrieving series select form-part';
        },
        success: function(data) {
            if (data.length > 0) {
                $("#serie").empty();
                //$("#serie").find("option:first").text("");
                for (var i = 0; i < data.length; i++) {
                    $("<option/>").attr("value", data[i].id).text(data[i].name).appendTo($("#serie"));
                }
                updateModels();
            }
        }
    });
}

function updateModels() {
    $.ajax({
        url: '/auto/model/getmodels',
        type: 'get',
        dataType: 'json',
        data: {serie: $('#serie').val()},
        error: function() {
            throw 'Error on retrieving model select form-part';
        },
        success: function(data) {
            if (data.length > 0) {
                $("#id_car_model").empty();
                //$("#id_car_model").find("option:first").text("");
                for (var i = 0; i < data.length; i++) {
                    $("<option/>").attr("value", data[i].id).text(data[i].name).appendTo($("#id_car_model"));
                }
                updateModification();
            }
        }
    });
}

function updateModification() {
    $.ajax({
        url: '/auto/model/getmodification',
        type: 'get',
        dataType: 'json',
        data: {id_model: $('#id_car_model').val()},
        error: function() {
            throw 'Error on retrieving modifications select form-part';
        },
        success: function(data) {
            if (data.length > 0) {
                mods = data;
                $("#id_car_modification").empty();
                //$("#id_car_model").find("option:first").text("");
                for (var i = 0; i < data.length; i++) {
                    $("<option/>").attr("value", data[i].id_car_modification).text(data[i].name).appendTo($("#id_car_modification"));
                }
                changeModification();
            }
        }
    });
}

function changeModification() {
    
    var mod = getParamsByMod($('#id_car_modification').val()); 
    //alert(mod.name);
    if (mod) {
        // años
        if (!mod.year_release)
            year_release = 1960;
        else
            year_release = mod.year_release;        
        
        if (!mod.year_finish)
            year_finish = (new Date).getFullYear();
        else
            year_finish = mod.year_finish;
        
        $("#year").empty();
        for (var year = year_release; year <= year_finish; year++) {
            $("<option/>").attr("value", year).text(year).appendTo($("#year"));
        }    
        // ======
       
        $("#engine_volume").val(mod.engine_volume);        
        $("#gearbox").val(mod.gear_box);
        $("#engine_type").val(mod.fuel_kind);
        $("#gear_type").val(mod.drive_gear_type);        
        
    }
    
}

function getParamsByMod(id) {
    for (var i = 0; i < mods.length; i++) {
        if (mods[i].id_car_modification == id) {
            return mods[i];
        }
    }
    return null;
}