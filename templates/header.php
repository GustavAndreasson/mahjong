<div class="header">
    <h1><?= $T->__("Mahjong Points Table") ?></h1>
    <button id="btn_show_options">
        <span class="web"><?= $T->__("New game") ?>&hellip;</span><span class="phone">&hellip;</span>
    </button>
    <?php if ($mj->is_logged_in()): ?>
        <button id="btn_saveload">
            <span class="web"><?= $T->__("Load game") ?>&hellip;</span><span class="phone">&hellip;</span>
        </button>
        <form id="frm_logout" action="library/user_controller.php" method="POST">
            <input type="hidden" name="action" value="logout" />
            <button id="btn_logout" type="submit">
                <span class="web"><?= $T->__("Logout") ?></span><span class="phone">LO</span>
            </button>
        </form>
    <?php else: ?>
        <button id="btn_show_login">
            <span class="web"><?= $T->__("Login") ?></span><span class="phone">LI</span>
        </button>
    <?php endif; ?>
</div>
<div class="headerspacer"></div>
