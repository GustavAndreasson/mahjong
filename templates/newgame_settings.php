<div class="settings">
    <form id="frm_settings" action="library/settings_controller.php" method="POST">
        <input type="hidden" name="action" value="set_settings" />
	<div class="setting">
            <span class="opt_title"><?= $T->__("Language") ?></span>
	    <span class="opt_options" id="languages">
                <?php
                $langs = $T->get_available_languages();
                foreach ($langs as $l) {
                    $checked = "";
                    if ($l == $T->get_language()) {
                        $checked = "checked";
                    }
                    echo "<input type='radio' name='language' value='$l' id='language_$l' $checked><label for='language_$l'>$l</label>";
                }
                ?>
            </span>
	</div>
	<div class="setting">
            <span class="opt_title"><?= $T->__("Number of players") ?></span>
	    <span class="opt_options">
                <input type="radio" name="no_players" value="2" id="no_players_2"><label for="no_players_2"><?= $T->__("Two") ?></label><input type="radio" name="no_players" value="3" id="no_players_3"><label for="no_players_3"><?= $T->__("Three") ?></label><input type="radio" name="no_players" value="4" id="no_players_4"><label for="no_players_4"><?= $T->__("Four") ?></label><input type="radio" name="no_players" value="5" id="no_players_5"><label for="no_players_5"><?= $T->__("Five") ?></label>
	    </span>
	</div>
	<div class="setting" id="fifth_player_pause_setting">
            <span class="opt_title"><?= $T->__("Fifth player does not participate") ?></span>
	    <span class="opt_options">
                <input type="radio" name="fifth_player_pause" id="fifth_player_pause_yes" value="1"><label for="fifth_player_pause_yes"><?= $T->__("Yes") ?></label><input type="radio" name="fifth_player_pause" id="fifth_player_pause_no" value="0"><label for="fifth_player_pause_no"><?= $T->__("No") ?></label>
	    </span>
	</div>
	<div class="setting">
            <span class="opt_title"><?= $T->__("Points distribution") ?></span>
	    <span class="opt_options">
                <input type="radio" name="points_distribution" value="0" id="points_distribution_0"><label for="points_distribution_0"><?= $T->__("All gets points") ?></label><br/>
                <input type="radio" name="points_distribution" value="1" id="points_distribution_1"><label for="points_distribution_1"><?= $T->__("Mahjong gets points") ?></label><br/>
                <input type="radio" name="points_distribution" value="2" id="points_distribution_2"><label for="points_distribution_2"><?= $T->__("All pays all") ?></label><br/>
                <input type="radio" name="points_distribution" value="3" id="points_distribution_3"><label for="points_distribution_3"><?= $T->__("All pays Mahjong") ?></label>
	    </span>
	</div>
	<div class="setting">
            <span class="opt_title"><?= $T->__("Start points") ?></span>
	    <span class="opt_options">
	        <input type="number" step="1" id="start_points" value="2000">
	    </span>
	</div>
	<div class="setting">
	    <span class="opt_options">
		<input type="checkbox" name="chk_custom" value="1" id="chk_custom"><label for="chk_custom"><?= $T->__("Start custom game") ?></label>
	    </span>
	</div>
        <?php require_once(TEMPLATES_PATH . "newgame_custom.php"); ?>
        <div class="confirm">
            <button type="button" id="btn_opt_cancel"><?= $T->__("Cancel") ?></button>
            <button type="submit" id="opt_ok"><?= $T->__("Start game") ?></button>
        </div>
    </form>
</div>
