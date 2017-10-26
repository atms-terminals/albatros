<?php
ini_set("display_errors", "1");
ini_set("error_reporting", E_ALL | E_STRICT | E_NOTICE);

require_once ROOT.'/components/DbHelper.php';
use components\DbHelper as dbHelper;

define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

function readXls($filename)
{
    /** Include PHPExcel */
    require_once ROOT.'/components/PHPExcel/Classes/PHPExcel.php';

    //  Read your Excel workbook
    try {
        $inputFileType = PHPExcel_IOFactory::identify($filename);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($filename);
    } catch(Exception $e) {
        die('Error loading file "'.pathinfo($filename, PATHINFO_BASENAME).'": '.$e->getMessage());
    }

    //  Get worksheet dimensions
    $sheet = $objPHPExcel->getSheet(0); 
    $highestRow = $sheet->getHighestRow(); 
    $highestColumn = $sheet->getHighestColumn();

    $rowData = false;
    $reference = false;
    // считываем лист

    return $sheet->rangeToArray('A1:'.$highestColumn.$highestRow,
                                    NULL,
                                    TRUE,
                                    FALSE);
}

// function loadPrice($rowData)
// {
//     $k = 0;
//     $names = array('Родитель код' => 'idParent',
//                 'Родитель наименование' => false,
//                 'Услуга код' => 'id',
//                 'Услуга наименование' => 'desc');
//     if ($rowData) {
//         // создаем таблицу соответствия названий в excel с нашими параметрами
//         foreach ($rowData[0] as $key => $value) {
//             if ($names[trim($value)]) {
//                 $reference[$names[trim($value)]] = $key;
//             }
//         }

//         if (count($rowData) > 1)
//         for ($i = 1; $i < count($rowData); $i++) { 
//             $idParent = empty($rowData[$i][$reference['idParent']]) ? 0 : $rowData[$i][$reference['idParent']];
//             $query = "/*".__FILE__.':'.__LINE__."*/ ".
//                 "SELECT custom_price_add(1, 'sgufk', '{$rowData[$i][$reference['id']]}', '$idParent', '{$rowData[$i][$reference['desc']]}', '', '', '', '', '', '');";
//             $result = dbHelper\DbHelper::selectRow($query);
//             $k++;
//         }
//     }
//     return $k;
// }

function loadContragents($rowData)
{
    $k = 0;
    $names = array('Код' => 'id',
                'ФИО' => 'fio',
                'Пасорт (серия, номер)' => 'passport');
    if ($rowData) {
        if ($rowData[0])
        // создаем таблицу соответствия названий в excel с нашими параметрами
        foreach ($rowData[0] as $key => $value) {
            if ($names[trim($value)]) {
                $reference[$names[trim($value)]] = $key;
            }
        }

        if (count($rowData) > 1) {
            for ($i = 1; $i < count($rowData); $i++) { 
                $query = "/*".__FILE__.':'.__LINE__."*/ ".
                    "SELECT custom_contragents_add(1, 'sibgufk', '{$rowData[$i][$reference['id']]}', '{$rowData[$i][$reference['fio']]}', '{$rowData[$i][$reference['passport']]}')";
                $result = dbHelper\DbHelper::selectRow($query);
                $k++;
            }
        }
    }
    return $k;
}



// $filename = ROOT.'/download/uslugi.xlsx';
// $rowData = readXls($filename);
// $k = loadPrice($rowData);
// echo "Загрузка списка услуг. Загружено $k строк<br>";

// $filename = ROOT.'/download/contragents.xlsx';
$rowData = readXls($filename);
$response['message'] = "Неверный формат";
if ($rowData[0][0] == 'Код' && $rowData[0][1] == 'ФИО') {
    if ($rowData) {
        $query = "/*".__FILE__.':'.__LINE__."*/ ".
            "DELETE from custom_contragents_sibgufk where 1;";
        $result = dbHelper\DbHelper::call($query);

        $k = loadContragents($rowData);
        $response['message'] = "Загрузка Контрагентов. Загружено $k строк";
    }
}

?>
