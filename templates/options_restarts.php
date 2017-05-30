<div class="restarts">
     <button type="button" id="btn_restart"><?= $T->__("Restart game") ?></button>
     <button type="button" id="btn_custom"><?= $T->__("Start custom game") ?></button>
  <?php if ($mj->is_logged_in()): ?>
     <button type="button" id="btn_saveload"><?= $T->__("Save or load game") ?></button>
  <?php endif; ?>
</div>