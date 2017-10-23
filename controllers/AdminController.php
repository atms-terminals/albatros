<?php
namespace controllers\AdminController;

include_once ROOT.'/models/Admin.php';
use models\Admin as admin;
use components\User as user;
use components\DbHelper as dbHelper;
use components\Proffit as proffit;

/**
* productController
*/
class AdminController
{
    public function actionIndex()
    {
        $statuses = admin\Admin::getHwsState();
        $devices = admin\Admin::$devices;
        $sid = user\User::getSid();

        require_once(ROOT.'/views/admin.php');
        return true;
    }

    public function actionGetHwsState()
    {
        $statuses = admin\Admin::getHwsState();
        $devices = admin\Admin::$devices;

        require_once(ROOT.'/views/hwsState.php');
        return true;
    }

    public function actionDownloadPaymentsSibgufk()
    {
        $dt1 = empty($_POST['dt1']) ? 'now()' : "str_to_date('".dbHelper\DbHelper::mysqlStr($_POST['dt1'])."', '%d.%m.%Y')";
        $dt2 = empty($_POST['dt2']) ? 'now() + interval 1 day' : "str_to_date('".dbHelper\DbHelper::mysqlStr($_POST['dt2'])."', '%d.%m.%Y')";
        
        require_once(ROOT.'/views/exportReestrXls.php');
        return true;
    }

    public function actionGetCollectionDetails()
    {
        $idCollection = empty($_POST['idCollection']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['idCollection']);
        $type = empty($_POST['type']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['type']);
        
        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT date_format(co.dt, '%d.%m.%Y %H:%i') dt_collection, u.address
            from collections co
                join users u on u.id = co.id_user
            where co.id = '$idCollection'";
        $collectionParams = dbHelper\DbHelper::selectSet($query);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT date_format(p.dt_confirm, '%d.%m.%Y %H:%i') dt_oper, a.name `client`, a.card, c.`desc` service, p.amount, p.deposit, p.summ
            from v_payments p
                left join custom_price_albatros c on p.abonement = c.id
                left join cards a on p.id_card = a.id
            where p.id_collection = '$idCollection'
                and p.type = '$type'
            order by p.dt_confirm";
        $opers = dbHelper\DbHelper::selectSet($query);

        require_once(ROOT.'/views/collectionDetailsXls.php');
        return true;
    }

    public function actionGetCollections()
    {
        $collections = admin\Admin::getCollections();

        require_once(ROOT.'/views/collections.php');
        return true;
    }

    public function actionGetTerminals()
    {
        $list = admin\Admin::getTerminals();

        require_once(ROOT.'/views/terminalsList.php');
        return true;
    }

    public function actionGetPriceGroup()
    {
        $active = empty($_GET['active']) ? 0 : dbHelper\DbHelper::mysqlStr($_GET['active']);
        $type = empty($_GET['type']) ? 0 : dbHelper\DbHelper::mysqlStr($_GET['type']);
        $list = admin\Admin::getPriceGroup($type, $active);

        require_once(ROOT.'/views/priceGroup.php');
        return true;
    }

    public function actionSetPriceGroupStatus()
    {
        $uid = user\User::getId();
        $id = empty($_POST['id']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['id']);
        $status = empty($_POST['status']) ? 0 : 1;
        $type = empty($_POST['type']) ? 'albatros' : dbHelper\DbHelper::mysqlStr($_POST['type']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT custom_price_set_status($uid, '$type', '$id', $status)";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }

    public function actionLoadPriceList()
    {
        $uid = user\User::getId();
        $response['code'] = 0;
        $response['message'] = '';

        try {
            // получаем список услуг
            $servicesList = proffit\Proffit::loadPriceList();
        } catch (\Exception $e) {
            $response['code'] = $e->getCode();
            $response['message'] = $e->getMessage();
            echo json_encode($response);
            exit;
        }

        if (!empty($servicesList['answer']['ITEM'][0])) {
            foreach ($servicesList['answer']['ITEM'] as $item) {
                $id = empty($item['@attributes']['ID']) ? 0 : dbHelper\DbHelper::mysqlStr($item['@attributes']['ID']);
                $idParent = empty($item['@attributes']['ID_UPPER']) ? 0 : dbHelper\DbHelper::mysqlStr($item['@attributes']['ID_UPPER']);
                $desc = empty($item['@attributes']['NAME']) ? '' : dbHelper\DbHelper::mysqlStr($item['@attributes']['NAME']);
                $price = empty($item['@attributes']['PRICE']) ? '' : dbHelper\DbHelper::mysqlStr($item['@attributes']['PRICE']);
                $priceUnit = empty($item['@attributes']['UNIT']) ? '' : dbHelper\DbHelper::mysqlStr($item['@attributes']['UNIT']);
                $priceMinUnit = empty($item['@attributes']['CNT_MIN']) ? '' : dbHelper\DbHelper::mysqlStr($item['@attributes']['CNT_MIN']);
                $period = empty($item['@attributes']['SROK']) ? '' : dbHelper\DbHelper::mysqlStr($item['@attributes']['SROK']);
                $periodUnit = empty($item['@attributes']['SROK_VID']) ? '' : dbHelper\DbHelper::mysqlStr($item['@attributes']['SROK_VID']);

                if ($id) {
                    $query = "/*".__FILE__.':'.__LINE__."*/ ".
                        "SELECT custom_price_add($uid, 'albatros', $id, $idParent, '$desc', '$price', '$priceUnit', '$priceMinUnit', '$period', '$periodUnit')";
                    $result = dbHelper\DbHelper::selectRow($query);
                }
            }
        }

        $response['servicesList'] = $servicesList;
        echo json_encode($response);
        return true;
    }

    public function actionDeletePriceItem()
    {
        $uid = user\User::getId();
        $id = empty($_POST['id']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['id']);
        $type = empty($_POST['type']) ? 'albatros' : dbHelper\DbHelper::mysqlStr($_POST['type']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT custom_price_delete($uid, '$type', '$id')";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }

    public function actionSetClientsDesc()
    {
        $uid = user\User::getId();
        $id = empty($_POST['id']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['id']);
        $text = empty($_POST['text']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['text']);
        $type = empty($_POST['type']) ? 'albatros' : dbHelper\DbHelper::mysqlStr($_POST['type']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT custom_price_set_clients_desc($uid, '$type', '$id', '$text')";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }

    public function actionSetColor()
    {
        $uid = user\User::getId();
        $id = empty($_POST['id']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['id']);
        $color = empty($_POST['color']) ? 'primary' : dbHelper\DbHelper::mysqlStr($_POST['color']);
        $type = empty($_POST['type']) ? 'albatros' : dbHelper\DbHelper::mysqlStr($_POST['type']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT custom_price_set_color($uid, '$type', '$id', '$color')";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }

    public function actionSetPrice()
    {
        $uid = user\User::getId();
        $id = empty($_POST['id']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['id']);
        $price = empty($_POST['price']) ? '0' : dbHelper\DbHelper::mysqlStr($_POST['price']);
        $type = empty($_POST['type']) ? 'albatros' : dbHelper\DbHelper::mysqlStr($_POST['type']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT custom_price_set_price($uid, '$type', '$id', '$price')";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }

    public function actionChangeStatus()
    {
        $uid = user\User::getId();
        $id = empty($_POST['id']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['id']);
        $status = empty($_POST['status']) ? 0 : 1;

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT users_change_status($uid, '$id', $status)";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }

    public function actionChangePassword()
    {
        $uid = user\User::getId();
        $id = empty($_POST['id']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['id']);
        $password = empty($_POST['new']) ? '123' : dbHelper\DbHelper::mysqlStr($_POST['new']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT users_change_password($uid, '$id', '$password')";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }

    public function actionAddUser()
    {
        $uid = user\User::getId();
        // если есть ip то роль - терминал, иначе - пользователь
        $ip = empty($_POST['ip']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['ip']);
        $idRole = $ip ? 2 : 1;
        $address = empty($_POST['address']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['address']);
        $login = empty($_POST['login']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['login']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT users_add($uid, $idRole, '$ip', '$login', '$address')";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }

    public function actionEditUser()
    {
        $uid = user\User::getId();
        $id = empty($_POST['id']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['id']);
        $ip = empty($_POST['ip']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['ip']);
        $address = empty($_POST['address']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['address']);
        $login = empty($_POST['login']) ? '' : dbHelper\DbHelper::mysqlStr($_POST['login']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT users_edit($uid, '$id', '$ip', '$login', '$address')";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }

    public function actionDeleteUser()
    {
        $uid = user\User::getId();
        $id = empty($_POST['id']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['id']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT users_delete($uid, '$id')";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }

    public function actionGetUsers()
    {
        $list = admin\Admin::getUsers();

        require_once(ROOT.'/views/usersList.php');
        return true;
    }

    public function actionGetPrepaidStatus()
    {
        $searchStr = empty($_GET['searchStr']) ? false : $_GET['searchStr'];
        if ($searchStr) {
            $statuses = admin\Admin::findPrepaid($searchStr);
            require_once(ROOT.'/views/showPrepaids.php');
        }
        return true;
    }

    public function actionChangePrepaid()
    {
        $uid = user\User::getId();
        $card = empty($_POST['card']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['card']);
        $amount = empty($_POST['card']) ? 0 : dbHelper\DbHelper::mysqlStr($_POST['amount']);

        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "SELECT prepayments_change($uid, c.id, '$amount')
            from cards c
            where c.card = '$card'";
        $result = dbHelper\DbHelper::selectRow($query);
        $response['code'] = 0;

        echo json_encode($response);
        return true;
    }
}
