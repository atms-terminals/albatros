<?php
namespace controllers\AlbatrosController;

include 'AjaxController.php';

use components\DbHelper as dbHelper;
use components\User as user;
use components\Proffit as proffit;
use controllers\AjaxController as ajaxController;

define('SERVICE_LIST_SCREEN_ALBATROS', 15);
define('GET_MONEY_SCREEN_ALBATROS', 4);
define('NO_CARD_SCREEN_ALBATROS', 13);

/**
 * обработка запросов ajax.
 */
class AlbatrosController extends ajaxController\AjaxController
{
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Обработка команды оплаты
     */
    public function actionPay()
    {
        $nextScreen = (empty($_POST['nextScreen'])) ? user\User::getFirstScreen() : dbHelper\DbHelper::mysqlStr($_POST['nextScreen']);
        $idAbonement = (empty($_POST['values']['idAbonement'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['idAbonement']);
        $amount = (empty($_POST['values']['amount'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['amount']);
        $price = (empty($_POST['values']['price'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['price']);
        $card = (empty($_POST['values']['card'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['card']);
        $customer = (empty($_POST['values']['customer'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['customer']);
        $serviceName = (empty($_POST['values']['serviceName'])) ? '' : dbHelper\DbHelper::mysqlStr($_POST['values']['serviceName']);

        // $amount = 800;

        $prepayment = (empty($_POST['values']['prepayment'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['prepayment']);
        $purchaseAmount = (empty($_POST['values']['purchaseAmount'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['purchaseAmount']);

        $uid = user\User::getId();

        if (!$idAbonement /*|| !$amount*/ || !$card || !$price) {
            // уходим на первый экран
            $_POST['nextScreen'] = user\User::getFirstScreen();
            $this->actionMove('Не все поля переданы');
            exit();
        }

        if (!$amount) {
            // уходим на первый экран
            $_POST['nextScreen'] = user\User::getFirstScreen();
            $this->actionMove();
            exit();
        }

        $replArray = $this->makeReplaceArray($nextScreen);

        // записываем запрос на оплату
        $query = '/*'.__FILE__.':'.__LINE__.'*/ '.
            "call payments_prepare($uid, '$idAbonement', '$card', '$customer', '$amount', $price, '$purchaseAmount', @idPayment, @countUnits, @prepayment);";
        $pay = dbHelper\DbHelper::call($query);
        $query = "/*".__FILE__.':'.__LINE__."*/ "."SELECT @idPayment idPayment, @countUnits countUnits, @prepayment prepayment";
        $payment = dbHelper\DbHelper::selectRow($query);

        $replArray['patterns'][] = '{SERVICE_NAME}';
        // $replArray['values'][] = $serviceName;
        $replArray['values'][] = 'Внесение наличных в счет оплаты';

        $prepayment = $payment['prepayment'];
        $replArray['patterns'][] = '{PREPAYMENT_BEFORE}';
        $replArray['values'][] = $prepayment;

        $idPayment = $payment['idPayment'];
        $replArray['patterns'][] = '{TRN}';
        $replArray['values'][] = $idPayment;

        $countUnits = $payment['countUnits'];
        $replArray['patterns'][] = '{COUNT_UNITS}';
        $replArray['values'][] = $countUnits;

        $replArray['patterns'][] = '{PRICE}';
        $replArray['values'][] = $price;

        $replArray['patterns'][] = '{SUMM}';
        $replArray['values'][] = $countUnits * $price;

        $replArray['patterns'][] = '{AMOUNT}';
        $replArray['values'][] = $amount;

        
        if ($countUnits) {
            // пишем платеж в проффит
            try {
                // получаем список услуг
                $servicesList = proffit\Proffit::pay($card, $idAbonement, $countUnits * $price, $countUnits);
            } catch (\Exception $e) {
                // уходим на первый экран
                $_POST['nextScreen'] = ERROR_SCREEN;
                $_POST['values']['type'] = 'proffit';
                $_POST['values']['message'] = 'Ошибка зачисления платежа: '.$e->getMessage();
                $this->actionWriteLog();
                exit();
            }
        }

        // подтверждаем оплату
        $query = '/*'.__FILE__.':'.__LINE__.'*/ '.
            "call payments_confirm($uid, $idPayment, @prepayment)";
        $pay = dbHelper\DbHelper::call($query);
        $query = "/*".__FILE__.':'.__LINE__."*/ "."SELECT @prepayment prepayment";
        $prepayment = dbHelper\DbHelper::selectRow($query);

        $preAmount = $prepayment['prepayment'];
        $replArray['patterns'][] = '{PREPAYMENT}';
        $replArray['values'][] = $preAmount;

        // добавляем список сервисов
        $replArray['patterns'][] = '{ABONEMENT_ID}';
        $replArray['values'][] = $idAbonement;

        $response = $this->getScreen($nextScreen, $replArray);

        $response['printForm']['amount'] = $amount;

        $response['message'] = '';
        $response['code'] = 0;
        
        //отправляем результат
        echo json_encode($response);
        return true;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Обработка команды получения экрана приема денег
     */
    public function actionGetMoneyScreen()
    {
        $nextScreen = (empty($_POST['nextScreen'])) ? user\User::getFirstScreen() : dbHelper\DbHelper::mysqlStr($_POST['nextScreen']);
        $idAbonement = (empty($_POST['values']['idAbonement'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['idAbonement']);
        $card = (empty($_POST['values']['card'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['card']);
        $price = (empty($_POST['values']['price'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['price']);
        $customer = (empty($_POST['values']['customer'])) ? '' : dbHelper\DbHelper::mysqlStr($_POST['values']['customer']);
        $serviceName = (empty($_POST['values']['serviceName'])) ? '' : dbHelper\DbHelper::mysqlStr($_POST['values']['serviceName']);
        $purchaseAmount = (empty($_POST['values']['purchaseAmount'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['purchaseAmount']);
        $prepayment = (empty($_POST['values']['prepayment'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['prepayment']);

        if (!$idAbonement) {
            // уходим на первый экран
            $_POST['nextScreen'] = user\User::getFirstScreen();
            $this->actionMove($e->getMessage());
            exit();
        }

        $replArray = $this->makeReplaceArray($nextScreen);
        // добавляем список сервисов
        $replArray['patterns'][] = '{ABONEMENT_ID}';
        $replArray['values'][] = $idAbonement;
        $replArray['patterns'][] = '{CARD}';
        $replArray['values'][] = $card;
        $replArray['patterns'][] = '{PRICE}';
        $replArray['values'][] = $price;
        $replArray['patterns'][] = '{CUSTOMER}';
        $replArray['values'][] = $customer;
        $replArray['patterns'][] = '{SERVICE_NAME}';
        $replArray['values'][] = $serviceName;
        $replArray['patterns'][] = '{PURCHASE_AMOUNT}';
        $replArray['values'][] = $purchaseAmount;
        $replArray['patterns'][] = '{PREPAYMENT}';
        $replArray['values'][] = number_format($prepayment, 2, '.', '');
        $replArray['patterns'][] = '{MIN_SUMM}';
        $replArray['values'][] = number_format($purchaseAmount - $prepayment, 2, '.', '');

        $response = $this->getScreen($nextScreen, $replArray);

        $response['message'] = '';
        $response['code'] = 0;
        
        //отправляем результат
        echo json_encode($response);
        return true;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Обработка команды получения баланса
     */
    public function actionGetBalance()
    {
        $nextScreen = (empty($_POST['nextScreen'])) ? user\User::getFirstScreen() : dbHelper\DbHelper::mysqlStr($_POST['nextScreen']);
        $card = (empty($_POST['values']['card'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['card']);
        $paid = (!empty($_POST['values']['notPaid'])) ? 0 : 1;

        // $card = '64FA32000D'; // есть в базе
        // $card = '92FC820003'; // есть в базе

        $servicesList = array();
        try {
            // получаем список услуг
            $servicesList = proffit\Proffit::getBalance($card, $paid);
        } catch (\Exception $e) {
            // уходим на первый экран
            $_POST['nextScreen'] = $e->getCode() == -2 ? NO_CARD_SCREEN_ALBATROS : ERROR_SCREEN;
            $_POST['values']['type'] = 'proffit';
            $_POST['values']['message'] = "Ошибка запроса баланса: {$e->getCode()} {$e->getMessage()}";
            $this->actionWriteLog();
            exit();
        }

        $servicesListFirst = $servicesList;
        $replArray = $this->makeReplaceArray($nextScreen);

        // получаем текущие авансы
        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT p.amount 
            from cards c
                join prepayments p on c.id = p.id_card
            where c.card = '$card'";
        $row = dbHelper\DbHelper::selectRow($query);
        $replArray['patterns'][] = '{PREPAYMENT}';
        $prepayment = empty($row['amount']) ? 0 : $row['amount'];
        $replArray['values'][] = $prepayment;

        $rows = '';

        if ($servicesList['services']) {
            foreach ($servicesList['services'] as $service) {
                $minSumm = $service['purchaseAmount'] - $prepayment;
                $balance = !$service['active'] ? 'НЕ АКТИВНА' : $service['balance'];
                $rows .= "<tr>
                        <td>{$service['name']}</td>
                        <td class='text-center bigDigit'>$balance</td>
                        <td class='text-center'>{$service['dtPay']}</td>
                        <td class='text-center'>{$service['dtFinish']}</td>
                        <td class='text-center'>{$service['price']}</td>
                        <td class='text-center'>$minSumm</td>
                        <td class='text-center'>
                            <input class='nextScreen' type='hidden' value='".GET_MONEY_SCREEN_ALBATROS."' />
                            <input class='activity' type='hidden' value='getMoneyScreen' />
                            <input class='value idAbonement' type='hidden' value='{$service['id']}' />
                            <input class='value price' type='hidden' value='{$service['price']}' />
                            <input class='value card' type='hidden' value='$card' />
                            <input class='value customer' type='hidden' value='{$servicesList['customer']}' />
                            <input class='value serviceName' type='hidden' value='{$service['name']}' />
                            <input class='value purchaseAmount' type='hidden' value='{$service['purchaseAmount']}' />
                            <input class='value prepayment' type='hidden' value='$prepayment' />
                            <a class='btn btn-primary action small'>Пополнить</a>
                        </td>
                    </tr>";
            }
        } else {
            $rows = '<tr><td colspan="7" class="error"><h1>Нет доступных услуг</h1><br><br></td></tr>';
        }

        // добавляем класс для кнопки долгов
        $replArray['patterns'][] = '{DEBTS}';
        $replArray['values'][] = $servicesList['debts'];
        $replArray['patterns'][] = '{DEBTS_TITLE}';
        $replArray['values'][] = !$paid ? '' : 'noDisplay';
        // добавляем номер карты
        $replArray['patterns'][] = '{CARD}';
        $replArray['values'][] = $card;
        // добавляем ФИО
        $replArray['patterns'][] = '{CLIENT}';
        $replArray['values'][] = $servicesList['customer'];
        // добавляем список сервисов
        $replArray['patterns'][] = '{SERVICES_LIST}';
        $replArray['values'][] = $rows;

        $response = $this->getScreen($nextScreen, $replArray);

        $response['servicesList'] = $servicesList;
        $response['servicesListFirst'] = $servicesListFirst;
        $response['message'] = '';
        $response['code'] = 0;
        
        //отправляем результат
        echo json_encode($response);
        return true;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Обработка команды получения новых услуг (Альбатрос)
     */
    public function actionGetServiceList()
    {

        $nextScreen = (empty($_POST['nextScreen'])) ? user\User::getFirstScreen() : dbHelper\DbHelper::mysqlStr($_POST['nextScreen']);
        $id = (empty($_POST['values']['id'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['id']);
        $start = (empty($_POST['values']['start'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['start']);
        $card = (empty($_POST['values']['card'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['card']);
        $prepayment = (empty($_POST['values']['prepayment'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['prepayment']);
        $customer = (empty($_POST['values']['customer'])) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['values']['customer']);

        $replArray = $this->makeReplaceArray($nextScreen);

        // кнопки возврата назад и на 1 уровень вверх
        $controls = '';
        $controls .= "<div class='controlDiv'>";
        if ($id) {
            if ($start) {
                $ns = $start - BUTTON_PER_SCREEN;
                $controls .= "<input class='activity' type='hidden' value='getServiceList' />
                        <input class='nextScreen' type='hidden' value='".SERVICE_LIST_SCREEN_ALBATROS."' />
                        <input class='value id' type='hidden' value='$id' />
                        <input class='value start' type='hidden' value='$ns' />
                        <input class='value prepayment' type='hidden' value='$prepayment' />
                        <input class='value card' type='hidden' value='$card' />
                        <input class='value customer' type='hidden' value='$customer' />
                        <button class='btn btn-primary action service control'>Предыдущий</button>";
            } else {
                $query = "/*".__FILE__.':'.__LINE__."*/ ".
                    "SELECT p.id_parent
                    from v_custom_pricelist p
                    where p.id = '$id'
                        and p.type = 'albatros'";
                $row = dbHelper\DbHelper::selectRow($query);

                $controls .= "<input class='activity' type='hidden' value='getServiceList' />
                        <input class='nextScreen' type='hidden' value='".SERVICE_LIST_SCREEN_ALBATROS."' />
                        <input class='value id' type='hidden' value='{$row['id_parent']}' />
                        <input class='value prepayment' type='hidden' value='$prepayment' />
                        <input class='value card' type='hidden' value='$card' />
                        <input class='value customer' type='hidden' value='$customer' />
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
                and p.type = 'albatros'
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

                        <input class='nextScreen' type='hidden' value='".GET_MONEY_SCREEN_ALBATROS."' />
                        <input class='activity' type='hidden' value='getMoneyScreen' />
                        <input class='value purchaseAmount' type='hidden' value='$minPurchase' />
                        <input class='value price' type='hidden' value='{$rows[$i]['price']}' />
                        <input class='value idAbonement' type='hidden' value='{$rows[$i]['id']}' />
                        <input class='value serviceName' type='hidden' value='{$rows[$i]['desc']}' />
                        <input class='value card' type='hidden' value='$card' />
                        <input class='value prepayment' type='hidden' value='$prepayment' />
                        <input class='value customer' type='hidden' value='$customer' />
                        <button class='btn btn-{$rows[$i]['color']} action service'>{$rows[$i]['desc']}$cost</button>   
                    </span>";
            } else {
                $buttons .= "<span>
                        <input class='activity' type='hidden' value='getServiceList' />
                        <input class='nextScreen' type='hidden' value='".SERVICE_LIST_SCREEN_ALBATROS."' />
                        <input class='value id' type='hidden' value='{$rows[$i]['id']}' />
                        <input class='value card' type='hidden' value='$card' />
                        <input class='value prepayment' type='hidden' value='$prepayment' />
                        <input class='value customer' type='hidden' value='$customer' />
                        <button class='btn btn-{$rows[$i]['color']} action service'>{$rows[$i]['desc']}$cost</button>   
                    </span>";
            }
        }

        $controls .= "<div class='controlDiv'>";
        if ($start + BUTTON_PER_SCREEN < count($rows)) {
            $start += BUTTON_PER_SCREEN;
            $controls .= "<input class='activity' type='hidden' value='getServiceList' />
                    <input class='nextScreen' type='hidden' value='".SERVICE_LIST_SCREEN_ALBATROS."' />
                    <input class='value id' type='hidden' value='$id' />
                    <input class='value start' type='hidden' value='$start' />
                    <input class='value prepayment' type='hidden' value='$prepayment' />
                    <input class='value card' type='hidden' value='$card' />
                    <input class='value customer' type='hidden' value='$customer' />
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

        $response['message'] = '';
        $response['code'] = 0;
        
        //отправляем результат
        echo json_encode($response);
        return true;
    }
}
