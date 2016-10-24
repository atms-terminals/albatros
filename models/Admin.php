<?php
namespace models\Admin;

use components\DbHelper as dbHelper;

/**
* model News
*/
class Admin
{
    /**
     * список оборудования на терминале
     * @var array список оборудования
     */
    public static $devices = array(
        'cash' => 'Купюроприемник',
        'fr' => 'Фискальный регистратор',
        'proffit' => 'Сервер Проффит',
        'webSocket' => 'Ubuntu',
        );

    /**
     * получение массива состояний оборудования
     * @return array массива состояний
     */
    public static function getHwsState()
    {
        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT u.id, u.address, h.`type`, h.is_error, date_format(h.dt, '%d.%m.%Y %H:%i') dt, h.message
            from hws_status h
                join users u on h.id_user = u.id
            order by u.address, h.`type`";
        $list = dbHelper\DbHelper::selectSet($query);

        // преобразуем
        $result = array();
        foreach ($list as $row) {
            if (empty($result[$row['address']])) {
                foreach (self::$devices as $key => $nameRus) {
                    $result[$row['address']][$key] = array (
                        'dt' => '',
                        'isError' => -1,
                        'message' => ''
                    );
                }
            }
            $result[$row['address']][$row['type']]['dt'] = $row['dt'];
            $result[$row['address']][$row['type']]['isError'] = $row['is_error'];
            $result[$row['address']][$row['type']]['message'] = $row['message'];
        }

        return $result;
    }

    /**
     * получение массива статусов предоплат
     * @return array массива состояний
     */
    public static function findPrepaid($searchStr)
    {
        $searchStr = dbHelper\DbHelper::mysqlStr($searchStr);
        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT c.id, c.card, c.name, ifnull(p.amount, 0) amount
            from cards c
                left join prepayments p on c.id = p.id_card
            where c.card regexp '$searchStr'
                or upper(c.name) regexp upper('$searchStr')
            order by c.card";
        $list = dbHelper\DbHelper::selectSet($query);

        return $list;
    }

    /**
     * получение списка пользователей
     * @return array список
     */
    public static function getUsers()
    {
        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT u.id, u.login, u.status
            from v_real_users u
            where u.id_role != 2
            order by u.login";
        $list = dbHelper\DbHelper::selectSet($query);

        return $list;
    }

    /**
     * получение списка терминалов
     * @return array список
     */
    public static function getTerminals()
    {
        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT u.id, u.ip, u.address, u.`status`
            from v_real_users u
            where u.id_role = 2
            order by u.login";
        $list = dbHelper\DbHelper::selectSet($query);

        return $list;
    }
}
