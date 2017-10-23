/**
* запрос чего-то с сервера
**/
function get(action, $area, values) {
    'use strict';
    values = values || false;

    var sid = $('#sid').val();
    $.get(sid + '/admin/' + action, values, function(data) {
        $area.find('.resultArea').html(data);
    });
}

$(document).ready(function() {
    'use strict';

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        switch (e.target.hash) {
            case '#hws': 
                get('getHwsState', $('#hws'));
                break;
            case '#collections': 
                get('getCollections', $('#collections'));
                break;
            case '#priceGroup': 
                get('getPriceGroup', $('#priceGroup'), {
                    type: $('.day-type .active').val(),
                    active: $('#priceStatus').prop('checked') ? 1 : 0
                    }
                );
                break;
            case '#admin': 
                get('getTerminals', $('#terminals'));
                get('getUsers', $('#users'));
                break;
        }
    });

    // удаление услуги
    $(document).on('click', 'button.delete', function() {
        var sid = $('#sid').val(),
            $checkbox = $(this).siblings('.serviceItem'),
            req = {
                type: $('.day-type .active').val(),
                id: $checkbox.attr('id'), 
                text: $(this).val()
            };

        $.post(sid + '/admin/deletePriceItem', req, function() {

        }, 'json')
            .fail(function(){
                get('getPriceGroup', $('#priceGroup'), {
                    type: $('.day-type .active').val(),
                    active: $('#priceStatus').prop('checked') ? 1 : 0
                    });
            });
    });

    // детализация инкассации
    $(document).on('click', 'button.getCollectionDetails', function() {
        var sid = $('#sid').val(),
            idCollection = $(this).siblings('.id').val(),
            type = $(this).siblings('.type').val(),
            req = {
                idCollection: idCollection,
                type: type
            };

        $.post(sid + '/admin/getCollectionDetails', req, function(response) {
            var $a = $('<a>');
            $a.attr('href', response.file);
            $('body').append($a);
            $a.attr('download', 'collectionDetail.xls');
            $a[0].click();
            $a.remove();

        }, 'json')
            .fail(function(){
            });
    });

    // загрузка прайса
    $(document).on('click', 'button.loadPriceList', function() {
        var sid = $('#sid').val(),
            req = {
            };

        $.post(sid + '/admin/loadPriceList', req, function() {
            get('getPriceGroup', $('#priceGroup'), {
                type: $('.day-type .active').val(),
                active: $('#priceStatus').prop('checked') ? 1 : 0
                }
            );
        }, 'json')
            .fail(function(){
                get('getPriceGroup', $('#priceGroup'), {
                    type: $('.day-type .active').val(),
                    active: $('#priceStatus').prop('checked') ? 1 : 0
                    });
            });
    });

    // редактирование названия услуги для терминала
    $(document).on('change', '.clientsDesc', function() {
        var sid = $('#sid').val(),
            $checkbox = $(this).siblings('.serviceItem'),
            req = {
                id: $checkbox.attr('id'), 
                type: $('.day-type .active').val(),
                text: $(this).val()
            };

        $.post(sid + '/admin/setClientsDesc', req, function() {

        }, 'json')
            .fail(function(){
                get('getPriceGroup', $('#priceGroup'), {
                    type: $('.day-type .active').val(),
                    active: $('#priceStatus').prop('checked') ? 1 : 0
                    });
            });
    });

    // редактирование цены
    $(document).on('change', '.price', function() {
        var sid = $('#sid').val(),
            $checkbox = $(this).siblings('.serviceItem'),
            req = {
                id: $checkbox.attr('id'), 
                type: $('.day-type .active').val(),
                price: $(this).val()
            };

        $.post(sid + '/admin/setPrice', req, function() {

        }, 'json')
            .fail(function(){
                get('getPriceGroup', $('#priceGroup'), {
                    type: $('.day-type .active').val(),
                    active: $('#priceStatus').prop('checked') ? 1 : 0
                    });
            });
    });

    // редактирование цвета кнопки
    $(document).on('change', '.color input', function() {
        var sid = $('#sid').val(),
            $this = $(this).closest('li'),
            color = $this.find('.color input:checked').val(),
            $checkbox = $this.find('.serviceItem'),
            req = {
                id: $checkbox.attr('id'), 
                type: $('.day-type .active').val(),
                color: color
            };

        $.post(sid + '/admin/setColor', req, function() {

        }, 'json')
            .fail(function(){
                get('getPriceGroup', $('#priceGroup'), {
                    type: $('.day-type .active').val(),
                    active: $('#priceStatus').prop('checked') ? 1 : 0
                    });
            });
    });

    // разрешение/запрещение услуги
    $(document).on('click', '.serviceItem', function() {
        var sid = $('#sid').val(),
            req = {
                id: $(this).attr('id'), 
                type: $('.day-type .active').val(),
                status: $(this).prop('checked') ? 1 : 0
            };

        $.post(sid + '/admin/setPriceGroupStatus', req, function() {

        }, 'json')
            .fail(function(){
                get('getPriceGroup', $('#priceGroup'), {
                    type: $('.day-type .active').val(),
                    active: $('#priceStatus').prop('checked') ? 1 : 0
                    });
            });
    });

    // показ услуг
    $(document).on('click', '#priceStatus', function() {
        get('getPriceGroup', $('#priceGroup'), {
            type: $('.day-type .active').val(),
            active: $(this).prop('checked') ? 1 : 0
            }
        );
    });

    // изменение типа меню
    $('.day-type button').click(function() {
        $('.day-type button').removeClass('active');
        $(this).addClass('active');
        get('getPriceGroup', $('#priceGroup'),
            {
                type: $('.day-type .active').val(),
                active: $('#priceStatus').prop('checked') ? 1 : 0
            }
        );
    });

    // сворачивание-разворачивание меню
    $(document).on('click', '#priceGroup .dropdown', function() {
        var $span = $(this).children('span'),
            $ul = $(this).siblings('ul');

        if ($span.hasClass('glyphicon-triangle-bottom')) {
            $span.removeClass('glyphicon-triangle-bottom').addClass('glyphicon-triangle-top');
            $ul.addClass('hidden');
        } else {
            $span.removeClass('glyphicon-triangle-top').addClass('glyphicon-triangle-bottom');
            $ul.removeClass('hidden');
        }
    });

    // запрос инкассаций
    $('#refreshCollections').click(function(event) {
        event.preventDefault();
        get('getCollections', $('#collections'));
    });

    // запрос статусов оборудования
    $('#refreshHwsStatus').click(function(event) {
        event.preventDefault();
        get('getHwsState', $('#hws'));
    });

    // запрос авансов по карте
    $('#getPrepaid').click(function(event) {
        event.preventDefault();
        get('getPrepaidStatus', $('#prepaid'), {searchStr: $('#searchStr').val()});
    });

    // изменение авансов по карте
    $(document).on('click', '#changePrepaid', function(event) {
        event.preventDefault();
        var sid = $('#sid').val(),
            req = {
                card: $('#changePrepaymentDialog .card').val(), 
                amount: $('#changePrepaymentDialog .amount').val()
            };

        $.post(sid + '/admin/changePrepaid', req, function() {
            $('#getPrepaid').trigger('click');
        }, 'json')
            .fail(function(){
                get('getPrepaidStatus', $('#prepaid'), {searchStr: $('#searchStr').val()});
            });
    });

    // показ диалога изменения аванса
    $(document).on('click', '.changePrepayment', function(event) {
        event.preventDefault();
        var $tr = $(this).closest('tr');
        $('#changePrepaymentDialog .amount').val($tr.find('span.amount').text());
        $('#changePrepaymentDialog .card').val($tr.find('span.card').text());
    });

    // изменение статуса терминала
    $(document).on('click', '.changeStatus', function(event) {
        event.preventDefault();
        var sid = $('#sid').val(),
            $this = $(this),
            $tr = $this.closest('tr'),
            req = {
                id: $tr.find('.id').val(), 
                status: $this.hasClass('enable') ? 1 : 0
            };

        $.post(sid + '/admin/changeStatus', req, function() {
            if ($this.hasClass('user')) {
                get('getUsers', $('#users'));
            } else {
                get('getTerminals', $('#terminals'));
            }
        }, 'json')
            .fail(function(){
                if ($this.hasClass('user')) {
                    get('getUsers', $('#users'));
                } else {
                    get('getTerminals', $('#terminals'));
                }
            });
    });

    // окно добавление/редактирования пользователя
    $(document).on('click', '.changeUser', function(event) {
        event.preventDefault();
        var $this = $(this),
            $tr = $this.closest('tr'),
            action = '';

        if ($this.hasClass('user')) {
            $('#changeUserDialog .user').show();
            $('#changeUserDialog .terminal').hide();
            action = 'User';
        } else {
            $('#changeUserDialog .user').hide();
            $('#changeUserDialog .terminal').show();
            action = 'Terminal';
        }

        $('#changeUserDialog input[type=text]').val('');
        $('#changeUserDialog .id').val(0);
        $('#changeUserDialog .confirm').show();

        if ($this.hasClass('add')) {
            action = 'add' + action;
            $('#changeUserDialog .edit').hide();
        } else {
            action = 'edit' + action;
            $('#changeUserDialog .add').hide();
            $('#changeUserDialog .id').val($tr.find('.id').val());

            if ($this.hasClass('user')) {
                $('#changeUserDialog .login').val($tr.find('.login').text());
            } else {
                $('#changeUserDialog .address').val($tr.find('.address').text());
                $('#changeUserDialog .ip').val($tr.find('.ip').text());
            }
        }

        $('#changeUserDialog .action').val(action);

    });

    $('#changeUserDialog .confirm').click(function(event) {
        event.preventDefault();
        var sid = $('#sid').val(),
            action = $('#changeUserDialog .action').val(), 
            req = {
                id: $('#changeUserDialog .id').val(), 
                ip: $('#changeUserDialog .ip').val(), 
                address: $('#changeUserDialog .address').val(), 
                login: $('#changeUserDialog .login').val(), 
            };

        $.post(sid + '/admin/' + action, req, function() {
            if (action === 'addUser' || action === 'editUser') {
                get('getUsers', $('#users'));
            } else {
                get('getTerminals', $('#terminals'));
            }
        }, 'json')
            .fail(function(){
            if (action === 'addUser' || action === 'editUser') {
                    get('getUsers', $('#users'));
                } else {
                    get('getTerminals', $('#terminals'));
                }
            });

    });

    // окно ввода нового пароля
    $(document).on('click', '.changeUserPassword', function(event) {
        event.preventDefault();
        var $tr = $(this).closest('tr');
        $('#changePasswordDialog .password').val('');
        $('#changePasswordDialog .modal-body .id').val($tr.find('.id').val());
    });

    // сохранение нового пароля
    $(document).on('click', '#changePassword', function(event) {
        event.preventDefault();
        var sid = $('#sid').val(),
            action = 'changePassword', 
            req = {
                id: $('#changePasswordDialog .id').val(), 
                new: $('#changePasswordDialog .password').val(), 
            };

        $.post(sid + '/admin/' + action, req, function() {
        }, 'json')
            .fail(function(){
            });

    });

    // окно подтверждения удаления
    $(document).on('click', '.confirmDelete', function(event) {
        event.preventDefault();
        var $this = $(this);
        if ($this.hasClass('user')) {
            $('#confirmDeleteDialog .modal-body span').html('пользователя');
            $('#confirmDeleteDialog .modal-body .id').val($this.siblings('.id').val());
            $('#confirmDeleteDialog .modal-body .action').val('deleteUser');
        } else if ($this.hasClass('terminal')) {
            $('#confirmDeleteDialog .modal-body span').html('терминал');
            $('#confirmDeleteDialog .modal-body .id').val($this.siblings('.id').val());
            $('#confirmDeleteDialog .modal-body .action').val('deleteTerminal');
        } else if ($this.hasClass('price')) {
            $('#confirmDeleteDialog .modal-body span').html('элемент меню');
            $('#confirmDeleteDialog .modal-body .id').val($this.siblings('.serviceItem').attr('id'));
            $('#confirmDeleteDialog .modal-body .action').val('deletePriceItem');
        }
    });

    // удаление
    $(document).on('click', '#deleteThis', function(event) {
        event.preventDefault();
        var sid = $('#sid').val(),
            action = $('#confirmDeleteDialog .action').val(), 
            req = {
                id: $('#confirmDeleteDialog .id').val(), 
                type: $('.day-type .active').val(),
            };

        $.post(sid + '/admin/' + action, req, function() {
            if (action === 'deleteUser') {
                get('getUsers', $('#users'));
            } else if (action === 'deleteTerminal') {
                get('getTerminals', $('#terminals'));
            } else if (action === 'deletePriceItem') {
                get('getPriceGroup', $('#priceGroup'), {
                    type: $('.day-type .active').val(),
                    active: $('#priceStatus').prop('checked') ? 1 : 0
                    }
                );
            }
        }, 'json')
            .fail(function(){
                if (action === 'deleteUser') {
                    get('getUsers', $('#users'));
                } else if (action === 'deleteTerminal') {
                    get('getTerminals', $('#terminals'));
                } else if (action === 'deletePriceItem') {
                    get('getPriceGroup', $('#priceGroup'), {
                        type: $('.day-type .active').val(),
                        active: $('#priceStatus').prop('checked') ? 1 : 0
                        }
                    );
                }
            });

    });
});