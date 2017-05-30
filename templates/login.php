<div id="login" class="pop-up closed">
  <div class="pu_bkgrnd"></div>
  <form id="frm_login" action="library/user_controller.php" method="POST">
    <input type="hidden" name="action" value="login" />
     <h2><?= $T->__("Log in") ?></h2>
     <div class="row"><span class="login_label"><?= $T->__("Username") ?></span><input type="text" name="login_name"></div>
     <div class="row"><span class="login_label"><?= $T->__("Password") ?></span><input type="password" name="login_password"></div>
    <div class="buttons">
     <a id="btn_show_register" href=""><?= $T->__("Register new user") ?></a>
     <button id="btn_login_cancel" type="button"><?= $T->__("Cancel") ?></button>
     <button id="btn_login" type="submit"><?= $T->__("Login") ?></button>
    </div>
  </form>
</div>
<div id="register" class="pop-up closed">
  <div class="pu_bkgrnd"></div>
  <form id="frm_register" action="library/user_controller.php" method="POST">
    <input type="hidden" name="action" value="register" />
    <h2><?= $T->__("Register new user") ?></h2>
    <div class="row"><span class="login_label"><?= $T->__("Username") ?></span><input type="text" name="register_name"></div>
    <div class="row"><span class="login_label"><?= $T->__("Password") ?></span><input type="password" name="register_password" id="register_password"></div>
    <div class="row"><span class="login_label"><?= $T->__("Repeat password") ?></span><input type="password" name="register_conf_password" id="register_conf_password"></div>
    <div class="buttons">
     <button id="btn_register_cancel" type="button"><?= $T->__("Cancel") ?></button>
     <button id="btn_register" type="submit"><?= $T->__("Register") ?></button>
    </div>
  </form>
</div>
