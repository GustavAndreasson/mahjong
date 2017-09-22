<div class="header">
    <h1><?= $T->__("Mahjong Points Table") ?></h1>
    <?php if ($mj->is_logged_in()): ?>
        <form id="frm_logout" action="library/user_controller.php" method="POST">
            <input type="hidden" name="action" value="logout" />
            <button id="btn_logout" type="submit">
                <span class="web"><?= $T->__("Logout") ?></span><span class="phone">LO</span>
            </button>
        </form>
        <button id="btn_saveload">
            <span class="web"><?= $T->__("Load game") ?>&hellip;</span><span class="phone">&hellip;</span>
        </button>
    <?php else: ?>
        <button id="btn_show_login">
            <span class="web"><?= $T->__("Login") ?></span><span class="phone">LI</span>
        </button>
    <?php endif; ?>
    <button id="btn_show_options">
        <span class="web"><?= $T->__("New game") ?>&hellip;</span><span class="phone">&hellip;</span>
    </button>
</div>
<div class="headerspacer"></div>
