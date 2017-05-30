<?php
include("Mahjong.php");

if (isset($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
    $mj = new Mahjong();
    
    switch($action) {
    case "input_points":
        input_points($mj);
        break;
    case "undo_round":
        undo_round($mj);
        break;
    }
}

function input_points($mj) {
    $points = explode(",", $_REQUEST["points"]);
    $mahjong = $_REQUEST["mahjong"];
    $mj->input_points($points, $mahjong);
    echo $mj->get_scoreboard();
}

function undo_round($mj) {
    $mj->undo_round();
    echo $mj->get_scoreboard();
}