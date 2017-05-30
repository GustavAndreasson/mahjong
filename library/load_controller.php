<?php
include("Mahjong.php");

if (isset($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
    $mj = new Mahjong();
    
    switch($action) {
    case "get_save_games":
        get_save_games($mj);
        break;
    case "load_game":
        load_game($mj);
        break;
    case "delete_game":
        delete_game($mj);
        break;
    }
}

function get_save_games($mj) {
    echo $mj->get_save_games();
}

function load_game($mj) {
    $save = true; //$_REQUEST["save"];
    $id = $_REQUEST["id"];    
    $mj->load_game($id, $save);

    header("Location: /mahjong");
    exit();
}

function delete_game($mj) {
    $id = $_REQUEST["id"];   
    $mj->delete_game($id);
    echo $mj->get_save_games();
}