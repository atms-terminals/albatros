var currScreen;
var timer, iAmAliveTimer, clockTimer = 0;
var flash = 1;
var currDate = new Date();
var stopAjax = 0;

///////////////////////////////////////////////////////////////////////////////////
// Добавление лидирующего нуля
function addZero(i) {
    'use strict';
    return (i < 10) ? '0' + i : i;
}
///////////////////////////////////////////////////////////////////////////////////
// получить текущую дату с учетом часового пояса
// diff в минутах
function getCurrDate() {
    'use strict';
    return addZero(currDate.getDate()) + '.' + addZero(currDate.getMonth() + 1) + '.' + currDate.getFullYear();
}
///////////////////////////////////////////////////////////////////////////////////
// получить текущее время с оффсетом с учетом часового пояса
// diff в минутах
function getCurrTime(needDot) {
    'use strict';
    var hour = addZero(currDate.getHours());
    var minutes = addZero(currDate.getMinutes());
    var secs = addZero(currDate.getSeconds());
    var ret = {
        delimeter: (needDot) ? ':' : ' ',
        hours: hour, 
        minutes: minutes, 
        secs: secs
    };

    return ret;
}


/////////////////////////////////////////////////////////////////////////////////////
// получение содержимого экрана с сервера
function doAction(activity, nextScreen, value){
    'use strict';
    value = value || 0;
    if (stopAjax === 1) {
        return false;
    }
    stopAjax = 1;

    // останавливаем "я живой"
    clearTimeout(iAmAliveTimer);

    var sid = $('#sid').val();

    var req = {
        nextScreen: nextScreen,
        value: value
    };

    // $('#loadingMessage').show();
    $.post(sid + '/ajax/' + activity, req, function (response) {
        stopAjax = 0;
        if (response.code === 0) {
            // сохраняем время
            currDate = new Date(response.dt.year, response.dt.month, response.dt.date, 
                response.dt.hours, response.dt.minutes, response.dt.seconds);

            if (response.html !== '') {
                $('#main').html(response.html);
            }
            $('#main').show();
            
            // если есть печатная форма - печатаем
            if (response.printForm !== undefined && response.printForm !== '') {
                var htmlText = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' +
                    '</head><body>' + response.printForm + '</body></html>';

                // $('div.print').html(htmlText);
                // window.print(); 
                // $('div.print').html('');
                var detailWindow = window.open('', '_blank', 'left=10000, top=20000, height=1, width=1, menubar=no, toolbar=no, location=no, directories=no, status=no, resizable=no, scrollbars=no, visible=no');
                detailWindow.resizeTo(0, 0);
                detailWindow.blur();
                detailWindow.document.write(htmlText);
                detailWindow.document.close();
                detailWindow.print();
                detailWindow.close();
            }

            // если есть таймер и нет аудио для автоматического перехода
            if (response.tScreen !== undefined && response.tScreen !== '') {
                timer = setTimeout(function() {doAction(response.tAction, response.tScreen);} , response.tTimeout * 1000);
            }
        }
    }, 'json')
        .fail(function () {
            // скрываем сообщение "подождите"
            $('#main').hide();
            stopAjax = 0;
            $('#loadingMessage').hide();
            clockTimer =  setTimeout(function() {
                    // первый скрин, который надо запросить
                    currScreen = $('#idScreen').val();
                    doAction('move', currScreen);
                } , 3000);
        });
}

$(document).ready(function () {
    'use strict';

    clockTimer =  setInterval(function() {
            var today = new Date();
            var time = getCurrTime(today.getSeconds() % 2);
            $('.currHour').html(time.hours);
            $('.currMinute').html(time.minutes);

            $('.currDate').html(getCurrDate());
            $('.currTime').html(getCurrTime());

            if (flash === 1) {
                flash = 0;
                $('.flashing').css('visibility', 'visible');
                $('.currDelim').css('visibility', 'visible');
            } else {
                flash = 1;
                $('.flashing').css('visibility', 'hidden');
                $('.currDelim').css('visibility', 'hidden');
            }
            currDate.setSeconds(currDate.getSeconds() + 1);

        } , 1000);

    clockTimer =  setTimeout(function() {
            // первый скрин, который надо запросить
            currScreen = $('#idScreen').val();
            doAction('move', currScreen);
        } , 3000);

    $(document).on('click', '.action', function(event) {
        event.preventDefault();
        // следующий экран куда перейти
        var nextScreen = $(this).siblings('.nextScreen').val();
        // действие
        var activity = $(this).siblings('.activity').val();
        // значение
        var value = $(this).siblings('.value').val();

        // останавливаем таймеры
        clearTimeout(timer);

        doAction(activity, nextScreen, value);
    });
});
