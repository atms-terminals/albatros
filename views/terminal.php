<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>СОК "Альбатрос"</title>

    <!-- env:prod --#>
        <link rel="stylesheet" href="views/css/style.min.css?<?= filemtime("views/css/login.min.css")?>">
    <!-- env:prod:end -->

    <!-- env:dev -->
        <link href='../bower_components/bootstrap/dist/css/bootstrap.css' rel="stylesheet">
        <link href='views/css/style-sass.css?<?= filemtime(ROOT.'/views/css/style-sass.css')?>' rel="stylesheet">
        <link href='views/css/flex-sass.css?<?= filemtime(ROOT.'/views/css/flex-sass.css')?>' rel="stylesheet">
    <!-- env:dev:end -->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <input type="hidden" id="idScreen" value="<?= $firstScreen;?>">
    <input type="hidden" id="sid" value="<?= $sid;?>">
    <div class="container-fluid">
        <div class="header">
            <div class="logos">
                <div class="lines line1">Федеральное государственное бюджетное образовательное учреждение высшего образования</div>
                <div class="lines line2">Сибирский Государственный университет физической культуры и спорта</div>
                <div class="row">
                    <div class="col-md-2 logo1"><img src='views/img/sibgufk.png' alt=""></div>
                    <div class="col-md-8 title"><h2>Спортивно-оздоровительный комплекс</h2><h1>альбатрос</h1></div>
                    <div class="col-md-2 logo2"><img src='views/img/albatross.png' alt=""></div>
                </div>
            </div>

            <div class="row time">
                <div class='col-md-6'>
                    <div class="currDate"></div>
                </div>
                <div class='col-md-6 text-right'>
                    <div style='display: inline; width: 100px; text-align: right' class='currHour'></div>
                    <div style='display: inline; width: 100px; text-align: center' class='currDelim'>:</div>
                    <div style='display: inline; width: 100px; text-align: left' class='currMinute'></div>
                </div>
            </div>
        </div>

        <div id="main">
        </div>
    </div>

    <!-- env:prod --#>
        <script src='views/js/terminal.min.js?<?= filemtime("views/js/terminal.min.js")?>'></script>
    <!-- env:prod:end -->

    <!-- env:dev -->
        <script src='../bower_components/jquery/dist/jquery.js'></script>
        <script src='../bower_components/bootstrap/dist/js/bootstrap.js'></script>
        <script src='views/js/terminal.js?<?= filemtime("views/js/terminal.js")?>'></script>
        <script src='views/js/cashcode.js?<?= filemtime("views/js/cashcode.js")?>'></script>
        <script src='views/js/rfid.js?<?= filemtime("views/js/rfid.js")?>'></script>
        <script src='views/kbd/kbd.js?<?= filemtime("views/kbd/kbd.js")?>'></script>
    <!-- env:dev:end -->
</body>
</html>