<?php
require_once("library/config.php");
require_once(LIBRARY_PATH . "Mahjong.php");
require_once(LIBRARY_PATH . "Translate.php");
$mj = new Mahjong();
if ($mj->is_logged_in()) {
    $T = new Translate($mj->get_user_language());
} else {
    $T = new Translate();
}
?>
<!DOCTYPE html>
<html lang="<?= $T->get_language(); ?>">
  <head>
    <title><?= $T->__("Mahjong Points Table") ?></title>
    <meta content = "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name = "viewport" />
    <link rel="stylesheet" type="text/css" href="static/css/stylesheet.css">
    <link rel="icon" type="image/x-icon" href="static/img/favicon.ico">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="static/js/Mahjong.js"></script>
  </head>
  <body>
    <div class="container">
      <?php require_once(TEMPLATES_PATH . "header.php"); ?>
      <div class="main">
        <?php if (!$mj->is_logged_in()) require_once(TEMPLATES_PATH . "login.php"); ?>
        <?php require_once(TEMPLATES_PATH . "newgame.php"); ?>
        <?php if ($mj->is_logged_in()) require_once(TEMPLATES_PATH . "load_game.php"); ?>
        <form id="frm_game">
          <div id="table"><?php echo $mj->get_table(); ?></div>
          <div class="buttons">
    <button type="submit"><?= $T->__("Submit") ?></button>
    <button type="button" id="btn_undo"><?= $T->__("Undo") ?></button>
          </div>
        </form>
      </div>
    </div>
    <?php require_once(TEMPLATES_PATH . "mj_popups.php"); ?>
    <div id="goodwork"></div>
  </body>
</html>
