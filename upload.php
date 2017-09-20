<?php
ini_set("display_errors", "1");
ini_set("error_reporting", E_ALL | E_STRICT | E_NOTICE);

define('ROOT', __DIR__);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link rel="stylesheet" href="views/css/style-sass.css">
    <link rel="stylesheet" href="views/css/flex-sass.css">
    <link href='bower_components/bootstrap/dist/css/bootstrap.css' rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <h1>Выгрузка файлов со списком контрагентов и платежей</h1>
        <?php
            include 'views/exportReestrXls.php';
        ?>

    </div>
</body>
        <script src='bower_components/jquery/dist/jquery.js'></script>
        <script src='bower_components/bootstrap/dist/js/bootstrap.js'></script>
</html>