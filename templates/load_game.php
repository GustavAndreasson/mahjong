<div id="saveload" class="pop-up closed">
    <div class="pu_bkgrnd"></div>
    <form id="frm_saveload" action="library/load_controller.php" method="POST">
        <input type="hidden" name="action" value="load_game" />
        <input type="hidden" name="id" id="slct_sl_value" />
        <div class="select" id="slct_sl">
            <?php echo $mj->get_save_games(); ?>
        </div>
        <div class="confirm">
            <button type="button" id="btn_sl_cancel"><?= $T->__("Cancel") ?></button>
            <button type="button" id="btn_sl_remove" disabled><?= $T->__("Remove") ?></button>
            <button type="submit" id="btn_sl_load" disabled><?= $T->__("Load") ?></button>
        </div>
    </form>
</div>
