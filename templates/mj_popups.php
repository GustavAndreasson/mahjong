<?php if (isset($_SESSION["MESSAGE"])): ?>
  <div id="mj_alert" class="pop-up open"><div class="pu_bkgrnd" id="mj_alert_bkgrnd"></div><div id="mj_alert_text">
    <?php
    echo $_SESSION["MESSAGE"];
unset($_SESSION["MESSAGE"]);
    ?>
  </div></div>
<?php else: ?>
  <div id="mj_alert" class="pop-up closed"><div class="pu_bkgrnd" id="mj_alert_bkgrnd"></div><div id="mj_alert_text"></div></div>
<?php endif; ?>
<div id="mj_confirm" class="pop-up closed"><div class="pu_bkgrnd" id="mj_confirm_bkgrnd"></div>
  <div id="mj_confirm_question"></div>
  <div class="confirm">
    <button type="button" id="btn_mj_confirm_cancel"><?= $T->__("Cancel") ?></button>
    <button type="button" id="btn_mj_confirm_ok"><?= $T->__("OK") ?></button>
  </div>
</div>
<div id="mj_prompt" class="pop-up closed"><div class="pu_bkgrnd" id="mj_prompt_bkgrnd"></div>
  <form id="mj_prompt_form">
    <div id="mj_prompt_question"></div>
    <div class="mj_prompt_answer"><input type="text" id="mj_prompt_answer"></div>
    <div class="confirm">
    <button type="button" id="btn_mj_prompt_cancel"><?= $T->__("Cancel") ?></button>
    <button type="submit" id="btn_mj_prompt_ok"><?= $T->__("OK") ?></button>
    </div>
  </form>
</div>
