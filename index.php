<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8');
setlocale(LC_ALL, 'ru_RU.utf8');

session_start();

require 'app/const.class.php';
require 'app/mysql.class.php';
require 'app/main.class.php';


if (!empty($_REQUEST["logout"])) {
    session_unset();
    session_destroy();
    $_SESSION = array();
}

//сохранеение имени и пароля в сессии
if (!empty($_REQUEST["enter"])) {
    $_SESSION["login"] = $_POST["login"];
    $_SESSION["passw"] = $_POST["passw"];
}

$aftn = new TAftn();

if (empty($_SESSION["login"])) { //первый раз
    $retr = false;
    $aftn->viewpage('login');
    die;
    exit;
}

if (!$aftn->authTest($_SESSION["login"], $_SESSION["passw"])) {//неправильный пароль
    $retr = true;
    $aftn->viewpage('login');
    die;
    exit;
}

if (isSet($_GET["adm"])) {//если админ интерфейс
    if (empty($_GET["f"])) {
        $aftn->viewpage('admin');
        exit;
    }
    include("app/admin.class.php");
    $aftnadm = new TAftnAdmin();
    $method = "ajax_" . $_GET["f"];
    if (method_exists($aftnadm, $method)) {
        $aftnadm->$method();
    } else {
        die("Вызов несуществующего метода " . $_GET["f"]);
    }
    exit;
}

if (empty($_GET["f"])) {
    $aftn->viewpage('main');
    exit;
}

$method = "ajax_" . $_GET["f"];

if (method_exists($aftn, $method)) {
    $aftn->$method();
} else {
    die("Вызов несуществующего метода " . $_GET["f"]);
}
