<?php
return array(
    '.*?/(terminal.php)' => 'terminal/index',
    '.*?/(admin.php)' => 'admin/index',
    '.*?/ajax/move' => 'ajax/move',
    '.*?/ajax/writeLog' => 'ajax/writeLog',
    '.*?/ajax/collection' => 'ajax/collection',

    '.*?/ajax/getServiceListSibgufk' => 'sibgufk/getServiceList',
    '.*?/ajax/getContragentsSibgufk' => 'sibgufk/getContragents',
    '.*?/ajax/saveContragentSibgufk' => 'sibgufk/saveContragent',
    '.*?/ajax/paySibgufk' => 'sibgufk/pay',

    '.*?/ajax/getBalance' => 'albatros/getBalance',
    '.*?/ajax/getMoneyScreen' => 'albatros/getMoneyScreen',
    '.*?/ajax/pay' => 'albatros/pay',
    '.*?/ajax/getServiceList' => 'albatros/getServiceList',

    '.*?/admin/getHwsState' => 'admin/getHwsState',
    '.*?/admin/getCollections' => 'admin/getCollections',
    '.*?/admin/getCollectionDetails' => 'admin/getCollectionDetails',
    '.*?/admin/downloadPaymentsSibgufk' => 'admin/downloadPaymentsSibgufk',
    '.*?/admin/uploadContragentsSibgufk' => 'admin/uploadContragentsSibgufk',

    '.*?/admin/getPriceGroup' => 'admin/getPriceGroup',
    '.*?/admin/setPriceGroupStatus' => 'admin/setPriceGroupStatus',
    '.*?/admin/setClientsDesc' => 'admin/setClientsDesc',
    '.*?/admin/setColor' => 'admin/setColor',
    '.*?/admin/setPrice' => 'admin/setPrice',
    '.*?/admin/setNds' => 'admin/setNds',
    '.*?/admin/deletePriceItem' => 'admin/deletePriceItem',
    '.*?/admin/loadPriceList' => 'admin/loadPriceList',

    '.*?/admin/getTerminals' => 'admin/getTerminals',
    '.*?/admin/changeStatus' => 'admin/changeStatus',
    '.*?/admin/addTerminal' => 'admin/addUser',
    '.*?/admin/editTerminal' => 'admin/editUser',
    '.*?/admin/deleteTerminal' => 'admin/deleteUser',

    '.*?/admin/getUsers' => 'admin/getUsers',
    '.*?/admin/addUser' => 'admin/addUser',
    '.*?/admin/editUser' => 'admin/editUser',
    '.*?/admin/deleteUser' => 'admin/deleteUser',
    '.*?/admin/changePassword' => 'admin/changePassword',

    '.*?/admin/getPrepaidStatus' => 'admin/getPrepaidStatus',
    '.*?/admin/changePrepaid' => 'admin/changePrepaid',
);
