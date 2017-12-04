<?php
require_once ROOT.'/components/DbHelper.php';
use components\DbHelper as dbHelper;

define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

function writeXls($data, $fn)
{
    /** Include PHPExcel */
    require_once ROOT.'/components/PHPExcel/Classes/PHPExcel.php';

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set document properties
    $objPHPExcel->getProperties()->setCreator("Sergey Lutsko")
                                 ->setTitle("Albatros")
                                 ->setCategory("Payments file");

    $col = 'A';
    $row = 1;
    $objPHPExcel->setActiveSheetIndex(0);

    $styleHeader = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                'rgb' => 'CCCCCC'
            )
        ),
        'font' => array(
            'bold' => true,
        )
    );

    // пишем первую строку
    foreach ($data['header'] as $key => $value) {
        $objPHPExcel->getActiveSheet()->setCellValue("$col$row", $value);
        $col++;
    }

    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->applyFromArray($styleHeader);

    // пишем дальше
    foreach ($data['data'] as $record) {
        $col = 'A';
        $row++;
        foreach ($data['header'] as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue("$col$row", $record[$key]);
            $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(-1);
    }

    // Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle('sheet1');

    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);

    // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    // $objWriter->save(ROOT."/download/$fn");
    return $objPHPExcel;
}

// function getPayments()
// {
//     $query = "/*".__FILE__.':'.__LINE__."*/ ".
//         "UPDATE payments_sibgufk p
//         set p.sent = -1
//         where p.sent = 0";
//     $result = dbHelper\DbHelper::call($query);

//     $query = "/*".__FILE__.':'.__LINE__."*/ ".
//         "SELECT p.id, date_format(p.dt_insert, '%d.%m.%Y %H:%i:%s') dt, p.id_contragent, p.contragent, p.passport, p.amount
//         from payments_sibgufk p
//         where p.sent = -1";
//     $data = dbHelper\DbHelper::selectSet($query);

//     $query = "/*".__FILE__.':'.__LINE__."*/ ".
//         "UPDATE payments_sibgufk p
//         set p.sent = 1
//         where p.sent = -1";
//     // $result = dbHelper\DbHelper::call($query);
//     return $data;
// }

function getPaymentsInterval($dt1, $dt2)
{
    $query = "/*".__FILE__.':'.__LINE__."*/ ".
        "SELECT p.id, date_format(p.dt_insert, '%d.%m.%Y %H:%i:%s') dt, p.id_contragent, p.contragent, p.passport, p.amount, p.id_service
        from payments_sibgufk p
        where p.dt_insert > $dt1
            and p.dt_insert < $dt2
        order by p.dt_insert";
    $data = dbHelper\DbHelper::selectSet($query);
    return $data;
}

// function getContragents()
// {
//     $query = "/*".__FILE__.':'.__LINE__."*/ ".
//         "SELECT c.id, c.id_contragent, c.fio, c.passport
//         from custom_contragents_sibgufk c
//         where c.is_term = 1";
//     $data = dbHelper\DbHelper::selectSet($query);

//     $query = "/*".__FILE__.':'.__LINE__."*/ ".
//         "UPDATE custom_contragents_sibgufk c
//         set c.is_term = 0
//         where c.is_term = 1";
//     // $result = dbHelper\DbHelper::call($query);
//     return $data;
// }

$data = array(
    // 'contragents' => array(
    //     'header' => array(
    //         'id_contragent' => 'id',
    //         'fio' => 'контрагент',
    //         'passport' => 'паспорт',
    //     ),
    //     'data' => getContragents()
    // ),
    'payments' => array(
        'header' => array(
            'id' => 'id платежа',
            'dt' => 'дата платежа',
            'id_contragent' => 'id',
            'id_service' => 'id услуги',
            'contragent' => 'контрагент',
            'passport' => 'паспорт',
            'amount' => 'сумма',
        ),
        'data' => getPaymentsInterval($dt1, $dt2),
    )
);
// writeXls($data['contragents'], 'changedContragents.xls');
$xls = writeXls($data['payments'], 'payments.xlsx');

// Save Excel 2007 file
$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');

ob_start();
$objWriter->save('php://output');
$xlsData = ob_get_contents();
ob_end_clean();

$response['code'] = 0;
$response['file'] = "data:application/vnd.ms-excel;base64,".base64_encode($xlsData);

echo json_encode($response);
?>
