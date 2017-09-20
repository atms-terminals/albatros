<?php
namespace controllers\SibgufkController;

include 'AjaxController.php';

use components\DbHelper as dbHelper;
use components\User as user;
use controllers\AjaxController as ajaxController;

define('CONTRAGENT_SIBGUFK', 17);
define('SERVICE_LIST_SCREEN_SIBGUFK', 16);

/**
 * обработка запросов ajax.
 */
class SibgufkController extends ajaxController\AjaxController
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Обработка команды получения новых услуг (Альбатрос)
     */
    public function actionGetServiceList()
    {
        $nextScreen = (empty($_POST['nextScreen'])) ? user\User::getFirstScreen() : dbHelper\DbHelper::mysqlStr($_POST['nextScreen']);
        $id = (empty($_POST['values']['id'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['id']);
        $start = (empty($_POST['values']['start'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['start']);

        $replArray = $this->makeReplaceArray($nextScreen);

        // кнопки возврата назад и на 1 уровень вверх
        $controls = '';
        $controls .= "<div class='controlDiv'>";
        if ($id) {
            if ($start) {
                $ns = $start - BUTTON_PER_SCREEN;
                $controls .= "<input class='activity' type='hidden' value='getServiceListSibgufk' />
                        <input class='nextScreen' type='hidden' value='".SERVICE_LIST_SCREEN_SIBGUFK."' />
                        <input class='value id' type='hidden' value='$id' />
                        <input class='value start' type='hidden' value='$ns' />
                        <button class='btn btn-primary action service control'>Предыдущий</button>";
            } else {
                $query = "/*".__FILE__.':'.__LINE__."*/ ".
                    "SELECT p.id_parent
                    from v_custom_pricelist p
                    where p.id = '$id'
                        and p.type = 'sibgufk'";
                $row = dbHelper\DbHelper::selectRow($query);

                $controls .= "<input class='activity' type='hidden' value='getServiceListSibgufk' />
                        <input class='nextScreen' type='hidden' value='".SERVICE_LIST_SCREEN_SIBGUFK."' />
                        <input class='value id' type='hidden' value='{$row['id_parent']}' />
                        <button class='btn btn-primary action service control'>Предыдущий</button>";
            }
        } else {
            $controls .= "&nbsp;";
        }
        $controls .= "</div>";

        $controls .= "<div class='controlDiv'>
                <input class='nextScreen' type='hidden' value='".FIRST_SCREEN."' />
                <input class='activity' type='hidden' value='move' />
                <button class='btn btn-primary action service control'>Отмена</button>   
            </div>";

        // добавляем список сервисов
        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT p.id, p.`desc`, p.price, p.price_unit, p.price_min_unit, p.period, p.period_unit, p.color
            FROM v_clients_custom_pricelist p
            WHERE p.id_parent = '$id'
                and p.type = 'sibgufk'
            ORDER BY p.id_parent, p.color, p.`desc`";
        $rows = dbHelper\DbHelper::selectSet($query);
        $buttons = '';

        for ($i = $start; $i < $start + BUTTON_PER_SCREEN && $i < count($rows); $i++) {
            $cost = $rows[$i]['price'] && $rows[$i]['price'] != '0.00' ? "<hr>{$rows[$i]['price']} руб." : '';
            if ($cost) {
                $minPurchase = $rows[$i]['price'] * $rows[$i]['price_min_unit'];
                $buttons .= "<span>
                        <!--input class='activity' type='hidden' value='move' />
                        <input class='nextScreen' type='hidden' value='".FIRST_SCREEN."' />
                        <button class='btn btn-{$rows[$i]['color']} action service'>{$rows[$i]['desc']}$cost</button-->   

                        <input class='nextScreen' type='hidden' value='".CONTRAGENT_SIBGUFK."' />
                        <input class='value id' type='hidden' value='{$rows[$i]['id']}' />
                        <input class='activity' type='hidden' value='move' />
                        <button class='btn btn-{$rows[$i]['color']} action service'>{$rows[$i]['desc']}$cost</button>   
                    </span>";
            } else {
                $buttons .= "<span>
                        <input class='activity' type='hidden' value='getServiceListSibgufk' />
                        <input class='nextScreen' type='hidden' value='".SERVICE_LIST_SCREEN_SIBGUFK."' />
                        <input class='value id' type='hidden' value='{$rows[$i]['id']}' />
                        <button class='btn btn-{$rows[$i]['color']} action service'>{$rows[$i]['desc']}$cost</button>   
                    </span>";
            }
        }

        $controls .= "<div class='controlDiv'>";
        if ($start + BUTTON_PER_SCREEN < count($rows)) {
            $start += BUTTON_PER_SCREEN;
            $controls .= "<input class='activity' type='hidden' value='getServiceListSibgufk' />
                    <input class='nextScreen' type='hidden' value='".SERVICE_LIST_SCREEN_SIBGUFK."' />
                    <input class='value id' type='hidden' value='$id' />
                    <input class='value start' type='hidden' value='$start' />
                    <button class='btn btn-primary action service control'>Следующий</button>";
        } else {
            $controls .= "&nbsp;";
        }
        $controls .= "</div>";

        $replArray['patterns'][] = '{CONTROLS_LIST}';
        $replArray['values'][] = $controls;

        $replArray['patterns'][] = '{SERVICES_LIST}';
        $replArray['values'][] = $buttons;

        $response = $this->getScreen($nextScreen, $replArray);

        $response['nextScreen'] = $nextScreen;
        $response['replArray'] = $replArray;
        $response['message'] = '';
        $response['code'] = 0;
        
        //отправляем результат
        echo json_encode($response);
        return true;
    }
}
