<!DOCTYPE html>
<html lang="ru">
    <head>
        <title><?= TConfig::$config['title'] ?></title>
        <meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="favicon.ico" rel="shortcut icon">
        <link href="assets/apple-touch-icon.png" rel="apple-touch-icon">
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/normalize.min.css">
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/font.css">
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/<?php ($mode === 'main') ? 'ui-lightness' : 'dark-hive'; ?>/jquery-ui.css">
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/font-awesome.min.css">
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/ui.jqgrid.css">
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/bootstrap.min.css">
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/css_main.css">
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/all.css">
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/<?= $mode ?>.css">
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/jquery-ui.css">  <!--  media="screen"  -->
        <link type = "text/css" rel = "stylesheet" media="screen" href="css/ui.jqgrid.css">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/jquery.timer.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/ecmascript" src="js/jquery.jqGrid.min.js"></script>
        <script type="text/ecmascript" src="js/grid.locale-ru.js"></script>
        <script type="text/javascript" src="js/<?= $mode ?>.js"></script>
        <script type="text/javascript" src="index.php?<?= ($mode === 'main') ? '' : 'adm=1&'; ?>f=js_conf"></script>
    </head>
    <body>
        <?= include($mode . ".php"); ?>