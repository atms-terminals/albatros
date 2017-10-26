<?php
define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

/** Include PHPExcel */
require_once ROOT.'/components/PHPExcel/Classes/PHPExcel.php';

// Create new PHPExcel object
$xls = new PHPExcel();

// Set document properties
$xls->getProperties()->setCreator("")
    ->setLastModifiedBy("")
    ->setTitle(__FILE__)
    ->setSubject('Детализация инкассации')
    ->setDescription('Подробная информация по операциям в инкассации');

// Add some data
$xls->setActiveSheetIndex(0)
    ->setCellValue('a1', 'Детализация инкассации')
    ->setCellValue('a2', "Дата формирования отчета ".date('d.m.Y H:i'))
    ->setCellValue('a3', "Дата инкассации {$collectionParams[0]['dt_collection']}")
    ->setCellValue('a4', "Адрес инкассации {$collectionParams[0]['address']}");

$col = "a";
$row = 6;

if ($opers) {
    $xls->setActiveSheetIndex(0)
        ->setCellValue($col++.$row, '№ п/п')
        ->setCellValue($col++.$row, 'Дата')
        ->setCellValue($col++.$row, 'Клиент')
        ->setCellValue($col++.$row, 'Карта')
        ->setCellValue($col++.$row, 'Услуга')
        ->setCellValue($col++.$row, 'Внесено')
        ->setCellValue($col++.$row, 'Депозит')
        ->setCellValue($col++.$row++, 'Зачислено');

    $xls->getActiveSheet()->getStyle("A1:X1")->getFont()->setBold(true);
    $xls->getActiveSheet()->getStyle("A6:X6")->getFont()->setBold(true);
    
    $xls->getActiveSheet()->getColumnDimension("a")->setWidth(10);
    $xls->getActiveSheet()->getColumnDimension("b")->setAutoSize(true);
    $xls->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
    $xls->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
    $xls->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
    $xls->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
    $xls->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
    $xls->getActiveSheet()->getColumnDimension("h")->setAutoSize(true);

    $i = 1;
    foreach ($opers as $item) {
        $col = 'A';

        $xls->setActiveSheetIndex(0)
            ->setCellValue($col++.$row, $i++)               // A
            ->setCellValue($col++.$row, $item['dt_oper'])   // B
            ->setCellValue($col++.$row, $item['client'])    // C
            ->setCellValue($col++.$row, $item['card'])      // D
            ->setCellValue($col++.$row, $item['service'])   // E
            ->setCellValue($col++.$row, $item['amount'])    // F
            ->setCellValue($col++.$row, $item['deposit'])   // G
            ->setCellValue($col++.$row, $item['summ']);     // H
        $row++;
    }

    $style = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        )
    );

    $xls->getActiveSheet()->getStyle("B6")->applyFromArray($style);
    $xls->getActiveSheet()->getStyle("A6:B$row")->applyFromArray($style);
    $xls->getActiveSheet()->getStyle("D6:D$row")->applyFromArray($style);
    $xls->getActiveSheet()->getStyle("F6:H$row")->applyFromArray($style);
} else {
    $xls->setActiveSheetIndex(0)->setCellValue($col.$row, 'Нет данных');
}

// Rename worksheet
$xls->getActiveSheet()->setTitle('Отчет');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$xls->setActiveSheetIndex(0);

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
