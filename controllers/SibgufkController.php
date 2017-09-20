<?php
namespace controllers\SibgufkController;

include 'AjaxController.php';

use components\DbHelper as dbHelper;
use components\User as user;
use controllers\AjaxController as ajaxController;

define('CONTRAGENTS_SIBGUFK', 17);
define('SERVICE_LIST_SCREEN_SIBGUFK', 16);
define('INPUT_FIO_SIBGUFK', 19);
define('SAVE_PASSPORT_SIBGUFK', 20);
define('GET_MONEY_SCREEN_SIBGUFK', 21);
/**
 * обработка запросов ajax.
 */

class SibgufkController extends ajaxController\AjaxController
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Обработка команды оплаты
     */
    public function actionPay()
    {
        $idContragent = empty($_POST['values']['idContragent']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['values']['idContragent']);
        $amount = (empty($_POST['values']['amount'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['amount']);
        $nextScreen = empty($_POST['nextScreen']) ? user\User::getFirstScreen() : dbHelper\DbHelper::mysqlStr($_POST['nextScreen']);
        $uid = user\User::getId();

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "CALL payments_add_sibgufk($uid, '$idContragent', '$amount')";
        $row = dbHelper\DbHelper::call($query);

        $replArray = $this->makeReplaceArray($nextScreen);
        $this->putPostIntoReplaceArray($replArray);
        $response = $this->getScreen($nextScreen, $replArray);
        $response['message'] = '';
        $response['code'] = 0;

        //отправляем результат
        echo json_encode($response);
        return true;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Обработка команды сохранения контрагента
     */
    public function actionSaveContragent()
    {
        $contragent = empty($_POST['values']['contragent']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['values']['contragent']);
        $idContragent = empty($_POST['values']['idContragent']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['values']['idContragent']);
        $passport = empty($_POST['values']['passport']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['values']['passport']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT custom_contragents_add_term(1, 'subgufk', '$idContragent', '$contragent', '$passport') res";
        $row = dbHelper\DbHelper::selectRow($query);

        $_POST['values']['idContragent'] = $row['res'];
        
        $nextScreen = $row['res'] == 0 ? FIRST_SCREEN : GET_MONEY_SCREEN_SIBGUFK;

        $replArray = $this->makeReplaceArray($nextScreen);
        $this->putPostIntoReplaceArray($replArray);
        $response = $this->getScreen($nextScreen, $replArray);
        $response['message'] = '';
        $response['code'] = 0;

        //отправляем результат
        echo json_encode($response);
        return true;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Обработка команды получения списка контрагентов
     */
    public function actiongetContragents()
    {
        $nextScreen = empty($_POST['nextScreen']) ? user\User::getFirstScreen() : dbHelper\DbHelper::mysqlStr($_POST['nextScreen']);

        $contragentSrc = empty($_POST['values']['contragent']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['values']['contragent']);
        $contragentsParts = explode(' ', $contragentSrc);
        $contragent = implode('%', $contragentsParts);
        
        $replArray = $this->makeReplaceArray($nextScreen);

        $contList = '';

        if (strlen($contragentSrc) > 5) {
            // добавляем список контрагентов
            $query = "/*".__FILE__.':'.__LINE__."*/ ".
                "SELECT c.id, c.fio, c.passport
                from custom_contragents_sgufk c
                where upper(c.fio) like upper('$contragent%')
                order by c.fio";
            $rows = dbHelper\DbHelper::selectSet($query);

            if ($rows) {
                foreach ($rows as $onePiece) {
                    $next = $onePiece['passport'] ? GET_MONEY_SCREEN_SIBGUFK : SAVE_PASSPORT_SIBGUFK;
                    $contList .= "<tr>
                            <td>{$onePiece['fio']}</td>
                            <td class='text-center'>{$onePiece['passport']}</td>
                            <td class='text-center'>
                                <input class='nextScreen' type='hidden' value='$next' />
                                <input class='activity' type='hidden' value='move' />
                                <input class='value idContragent' type='hidden' value='{$onePiece['id']}' />
                                <input class='value contragent' type='hidden' value='{$onePiece['fio']}' />
                                <input class='value passport' type='hidden' value='{$onePiece['passport']}' />
                                <a class='btn btn-primary action small'>Выбрать</a>
                            </td>
                        </tr>";
                }
            } else {
                $nextScreen = INPUT_FIO_SIBGUFK;
            }
        } else {
            $nextScreen = INPUT_FIO_SIBGUFK;
        }

        // добавляем список сервисов
        $replArray['patterns'][] = '{CONTRAGENTS_LIST}';
        $replArray['values'][] = $contList;

        $response = $this->getScreen($nextScreen, $replArray);

        $response['message'] = '';
        $response['code'] = 0;
        
        //отправляем результат
        echo json_encode($response);
        return true;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Обработка команды получения списка услуг
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

                        <input class='nextScreen' type='hidden' value='".CONTRAGENTS_SIBGUFK."' />
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
