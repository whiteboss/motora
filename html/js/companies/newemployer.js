$(function() {

    var options = {
        matchContains: true,
        minChars: 2,
        max: 15,
        width: 200,
        dataType: 'json',
        cacheLength: 0,
        mustMatch: true,
        autoFill: true,
        //multiple: true,
        // задать дополнительные параметры для сервера
//                extraParams: {
//                       q: ''
//                       },
        // разбираем полученный ответ
        parse: function(data) {
            var parsed = [];
            if (data===null) data = [];
            for (var i = 0; i < data.length; i++) {
                parsed[parsed.length] = {
                    data: data[i],
                    value: data[i].id,
                    result: data[i].username
                };
            }
            return parsed;
        },
        // оформить полученный результат в человеко понятный вид
        formatItem: function(item) {
            return item.username; // + '<font>' + ' (' + item.cocount + ')' + '</font>';
        },
        formatMatch: function(item) {
            if(item && item.username)
                return item.username;
            else
                return;
        }
    };

    $("#userTo").autocomplete('/users/friends/jsonfriends', options).result(function(event, data) {
        //if(row && row.id)
        $("#id_user").val(data.id);
    });

});

