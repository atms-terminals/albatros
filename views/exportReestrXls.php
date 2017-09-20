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
                'rgb' => 'FFFEFEFE'
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

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    $objWriter->save(ROOT."/download/$fn");
}

function getPayments()
{
    $query = "/*".__FILE__.':'.__LINE__."*/ ".
        "UPDATE payments_sibgufk p
        set p.sent = -1
        where p.sent = 0";
    $result = dbHelper\DbHelper::call($query);

    $query = "/*".__FILE__.':'.__LINE__."*/ ".
        "SELECT date_format(p.dt_insert, '%d.%m.%Y %H:%i:%s') dt, p.id_contragent, p.contragent, p.passport, p.amount
        from payments_sibgufk p
        where p.sent = -1";
    $data = dbHelper\DbHelper::selectSet($query);

    $query = "/*".__FILE__.':'.__LINE__."*/ ".
        "UPDATE payments_sibgufk p
        set p.sent = 1
        where p.sent = -1";
    // $result = dbHelper\DbHelper::call($query);
    return $data;
}

function getContragents()
{
    $query = "/*".__FILE__.':'.__LINE__."*/ ".
        "SELECT c.id, c.id_contragent, c.fio, c.passport
        from custom_contragents_sgufk c
        where c.is_term = 1";
    $data = dbHelper\DbHelper::selectSet($query);

    $query = "/*".__FILE__.':'.__LINE__."*/ ".
        "UPDATE custom_contragents_sgufk c
        set c.is_term = 0
        where c.is_term = 1";
    // $result = dbHelper\DbHelper::call($query);
    return $data;
}

$data = array(
    'contragents' => array(
        'header' => array(
            'id_contragent' => 'id',
            'fio' => 'контрагент',
            'passport' => 'паспорт',
        ),
        'data' => getContragents()
    ),
    'payments' => array(
        'header' => array(
            'dt' => 'дата платежа',
            'id_contragent' => 'id',
            'contragent' => 'контрагент',
            'passport' => 'паспорт',
            'amount' => 'сумма',
        ),
        'data' => getPayments(),
    )
);
writeXls($data['contragents'], 'changedContragents.xls');
writeXls($data['payments'], 'payments.xls');
