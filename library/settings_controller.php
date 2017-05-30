<?php
include("Mahjong.php");

if (isset($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
    $mj = new Mahjong();
    
    switch($action) {
    case "get_settings":
        get_settings($mj);
        break;
    case "set_settings":
        set_settings($mj);
        break;
    case "update_name":
        update_name($mj);
        break;
    }
}

function get_settings($mj) {
    echo $mj->get_settings();
}

function set_settings($mj) {
    $settings = json_decode($_REQUEST["settings"]);
    $save = $_REQUEST["save"];
    $mj->restart_game($save);
    $mj->set_settings($settings);
    echo $mj->get_table();
}

function update_name($mj) {
    $player = $_REQUEST["player"];
    $name = $_REQUEST["name"];
    $mj->update_name($player, $name);
}