<?php

include("Mahjong.php");

if (isset($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
    $mj = new Mahjong();
    
    switch($action) {
    case "login":
        login($mj);
        break;
    case "logout":
        logout($mj);
        break;
    case "register":
        register($mj);
        break;
    default:
        break;
    }
}

function login($mj) {
    $name = $_REQUEST["login_name"];
    $password = $_REQUEST["login_password"];
    
    if (!$mj->login($name, $password)) {
        $_SESSION["MESSAGE"] = "Fel användarnamn eller lösenord";
    }
    header("Location: /mahjong");
    exit();
}

function logout($mj) {
    $mj->logout();
    header("Location: /mahjong");
    exit();
}

function register($mj) {
    $name = $_REQUEST["register_name"];
    $password = $_REQUEST["register_password"];
    
    if (!$mj->register($name, $password)) {
        $_SESSION["MESSAGE"] = "Det finns redan en användare med det här användarnamnet";
    }
    header("Location: /mahjong");
    exit();
}