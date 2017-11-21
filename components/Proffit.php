<?php

namespace components\Proffit;

/**
 * Работа с сервером интеграции Проффит
 */
class Proffit
{
    /**
     * @var string $timeout Таймаут ответа сервера Проффит
     * @var string $url Адрес сервера
     * @var string $user Пользователь
     * @var string $password Пароль
     * @var string $termId Вид платежа - код кассы
     */
    private static $timeout = 10;
    private static $url = 'http://192.168.20.100:5353';
    // private static $url = 'http://192.168.0.105:8000';
    // private static $url = 'http://192.168.3.71:8000';
    private static $user = 'User';
    private static $password = 'Password';
    private static $termId = 4;

    /**
     * Формирование запроса на баланс
     *
     * @param string $card Номер карты
     *
     * @return string Строка запроса;
     */
    private static function makeReqBalance($card)
    {
        $user = self::$user;
        $password = self::$password;

        return "<?xml version='1.0'?>
            <proffit code='PS' ver='1' lang='RU'>
            <user name='$user' pass='$password'/>
            <command name='GET_STATUS_CARD' CARD_CODE='$card' DAYS='900'/>
            </proffit>";
    }

    private static function makeReqGetServices()
    {
        $user = self::$user;
        $password = self::$password;

        return "<?xml version='1.0'?>
            <proffit code='PS' ver='1' lang='RU'>
            <user name='$user' pass='$password'/>
            <command name='GET_SERVICES'/>
            </proffit>";
    }

    /**
     * Формирование запроса на зачисление платежа.
     *
     * @param string $card   Номер карты
     * @param string $amount Сумма
     *
     * @return string Строка запроса;
     */
    private static function makeReqPay($card, $itemId, $amount, $cnt)
    {
        $user = self::$user;
        $password = self::$password;
        $termId = self::$termId;

        return "<?xml version='1.0'?>
            <proffit code='PS' ver='1' lang='RU'>
            <user name='$user' pass='$password'/>
            <command name='ADD_PAYMENT' CARD_CODE='$card' />
            <ITEM ID='$itemId' SYMA='$amount' KASSA_ID='$termId' CNT='$cnt'/>
            </proffit>";
    }

    /**
     * Проверка соединения с Проффит
     *
     * @return bool результат проверки
     */
    public static function checkConnection()
    {
        $streamOptions = array(
            'http' => array(
               'method' => 'GET',
               'timeout' => self::$timeout,
               'header' => "Content-type: application/xml\r\n",
            ),
        );

        $context = stream_context_create($streamOptions);

        return strstr(@file_get_contents(self::$url, null, $context), "Proffit Sport") ? true : false;
    }

    /**
     * Отправка запроса в Проффит
     *
     * @param string $post строка запроса
     *
     * @return array результат запроса
     */
    private static function sendRequest($post)
    {
        $streamOptions = array(
            'http' => array(
               'method' => 'POST',
               'timeout' => self::$timeout,
               'header' => "Content-type: application/xml\r\n",
               'content' => $post,
            ),
        );

        $context = stream_context_create($streamOptions);
        if ($response = @file_get_contents(self::$url, null, $context)) {
            // convert the XML result into array
            $data = json_decode(json_encode(simplexml_load_string($response)), true);

            if (!empty($data['result']['@attributes']['code'])) {
                throw new \Exception($data['result']['@attributes']['text'], -2);
            }
            return $data;
        }
        throw new \Exception('Нет связи с сервером Проффит', -1);
    }

    /**
     * Сохранение одной услуги
     */
    private static function saveServiceItem($paid, $abon, &$result)
    {
        $id = $abon['@attributes']['ID'];
        $dtPay = (empty($abon['@attributes']['PURCHASE_DATE'])) ? '' : $abon['@attributes']['PURCHASE_DATE'];
        $price = (empty($abon['@attributes']['PRICE'])) ? 0 : (int)$abon['@attributes']['PRICE'];

        if (preg_match('~(\d{2}).(\d{2}).(\d{4})~', $dtPay, $dtParts)) {
            $dt = $dtParts[2].$dtParts[1].$dtParts[0];
        } else {
            $dt = 0;
        }

        // проверяем дату покупки. нужно сохранить самую последнюю
        // если цены нет, то сохраняем в любом случае
        if (!empty($result['services'][$id])) {
            if ($dt < $result['services'][$id]['dt'] && !$price) {
                return;
            }
        }

        if (!$price) {
            $id = $id.count($result['services']);
        }

        $cpaid = empty($abon['@attributes']['PAID']) ? 0 : 1;
        if ($cpaid == $paid) {
            $result['services'][$id] = array (
                'dt' => $dt,
                'id' => $abon['@attributes']['ID'],
                'name' => $abon['@attributes']['NAME'],
                'dtFinish' => (empty($abon['@attributes']['PURCHASE_FINISH'])) ? 'Бессрочный' : $abon['@attributes']['PURCHASE_FINISH'],
                'dtPay' => $dtPay,
                'balance' => "{$abon['@attributes']['QTY']} {$abon['@attributes']['UNIT']}",
                'purchaseAmount' => $abon['@attributes']['PURCHASE_SYMA'],
                'price' => $price,
                'paid' => $abon['@attributes']['PAID'],
                'active' => $abon['@attributes']['ACTIVE'],
                );
        } elseif ($paid) {
            $result['debts'] = '';
        }
    }

    /**
     * Получение списка подключенных услуг.
     *
     * @param string $card номер карты
     *
     * @return array статус карты;
     */
    public static function getBalance($card, $paid)
    {
        // $mockBalance = include 'mockBalance.php';
        // $card = '64FA32000D'; // есть в базе
        // $card = '92FC820003'; // есть в базе
        // $card = '179AFF0029'; // корпоративная
        // $card = '5714270030'; // корпоративная с 2-мя одинаковыми услугами
        // $card = 'C985FF0029'; // корпоративная без услуг
        // $card = '256702006A'; // реальный клиент
        // $card = '4F97670088'; // клиент с долгами
        // $raw = $mockBalance[$card];

        $raw = self::sendRequest(self::makeReqBalance($card));

        $result = array(
            'customer' => (empty($raw['answer']['CLIENT']['@attributes']['NAME'])) ? '' : $raw['answer']['CLIENT']['@attributes']['NAME'],
            'services' => array(),
            'debts' => 'noDisplay'
        );

        if (!empty($raw['answer']['ITEM'])) {
            if (empty($raw['answer']['ITEM']['@attributes'])) {
                foreach ($raw['answer']['ITEM'] as $abon) {
                    self::saveServiceItem($paid, $abon, $result);
                }
            } else {
                self::saveServiceItem($paid, $raw['answer']['ITEM'], $result);
            }
        }
        
        uksort($result['services'], "self::cmp");

        return $result;
    }

    private static function cmp($a, $b)
    {
        return $a['dt'] == $b['dt'] ? 0 : ($a['dt'] < $b['dt'] ? 1 : -1);
    }

    /**
     * Получение полного списка услуг.
     *
     * @return array список услуг;
     */
    public static function loadPriceList()
    {
        $raw = self::sendRequest(self::makeReqGetServices());
        return $raw;
    }

    /**
     * Зачисление платежа.
     *
     * @param string $card номер карты
     */
    public static function pay($card, $idAbonement, $amount, $countUnits)
    {
        return self::sendRequest(self::makeReqPay($card, $idAbonement, $amount, $countUnits));
    }
}
