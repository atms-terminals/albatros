<?php
return array(
    '.*?/(terminal.php)' => 'terminal/index',
    '.*?/(admin.php)' => 'admin/index',
    '.*?/ajax/move' => 'ajax/move',
    '.*?/ajax/getBalance' => 'ajax/getBalance',
    '.*?/ajax/getMoneyScreen' => 'ajax/getMoneyScreen',
    '.*?/ajax/pay' => 'ajax/pay',
    '.*?/ajax/writeLog' => 'ajax/writeLog',
    '.*?/ajax/collection' => 'ajax/collection',

    '.*?/admin/getHwsState' => 'admin/getHwsState',
    '.*?/admin/getCollections' => 'admin/getCollections',

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
