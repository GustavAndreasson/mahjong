<?php
include("Mahjong.php");

if (isset($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
    $mj = new Mahjong();
    
    switch($action) {
    case "restart_game":
        restart_game($mj);
        break;
    }
}

function restart_game($mj) {
    $save = $_REQUEST["save"];
    $mj->restart_game($save);
    echo $mj->get_table();
}

