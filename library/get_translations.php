<?php

include("Translate.php");

$lang = $_REQUEST["lang"];

$T = new Translate($lang);

echo json_encode($T->get_translations());