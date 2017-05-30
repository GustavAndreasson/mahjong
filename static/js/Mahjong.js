/**
 * Mahjong.js
 *
 * Javascripts for the Mahjong points table page.
 *
 * Made by Gustav Andréasson
 */
//"use strict";
 
var MJDictionary = []; //Translations, loaded in external files
var language = navigator.language || navigator.userLanguage; //Browser language. If there is a saved language, this is overwritten
language = language.substr(0,2);
$.getJSON("library/get_translations.php", {lang: language}, function (data) {
    MJDictionary = data;
});

var round; //The number of the current round (first round is 0)

var settings = {};

$(function() {
    //Add event listeners to the buttons and start the game when page is loaded
    $("#btn_undo").click(undo_round);
    $("#btn_show_login").click(function() {toggle_popup("login")});
    $("#btn_show_register").click(function(e) {
	e.preventDefault();
	toggle_popup("login");
	toggle_popup("register");
    });
    $("#frm_register").submit(function() {
	if ($("#register_password").val() == $("#register_conf_password").val()) {
	    return true;
	} else {
	    mj_alert("Inte likadant lösenord");
	    return false;
	}
    });
    $("#btn_show_options").click(function() {
	update_settings();
	toggle_popup("options");
    });
    $("#btn_opt_cancel").click(function() {toggle_popup("options")});
    $("#btn_login_cancel").click(function() {toggle_popup("login")});
    $("#btn_register_cancel").click(function() {toggle_popup("register")});
    $("[name='no_players']").click(function() {
	var opt_no_players = get_radio_value("no_players");
	if (opt_no_players == 5) {
	    $("#fifth_player_pause_setting").show();
	} else {
	    $("#fifth_player_pause_setting").hide();
	}
    });
    $("[name='points_distribution']").click(update_points_distribution_setting);
    $("#frm_settings").attr("action", "javascript:save_options()");
    $("#btn_restart").click(restart_game);
    $("#btn_custom").click(show_custom_start);
    $("#btn_cstm_start").click(custom_start);
    $("#btn_cstm_cancel").click(show_custom_start);
    $("[name='custom_wind']").click(update_custom_wind);
    $("#btn_saveload").click(show_saveload);
    $("#slct_sl").change(save_selected);
    $("#btn_sl_cancel").click(show_saveload);
    $("#btn_sl_remove").click(remove_save);
    //$("#btn_sl_load").click(load_game);
    $("#frm_game").attr("action", "javascript:update_table()");
    $("#mj_alert").click(function () {
	toggle_popup("mj_alert");
    });
    $("#btn_mj_confirm_cancel").click(function () {
	toggle_popup("mj_confirm");
    });
    $("#btn_mj_confirm_ok").click(function () {
	toggle_popup("mj_confirm");
    });
    $("#btn_mj_prompt_cancel").click(function () {
	toggle_popup("mj_prompt");
    });
    $("#mj_prompt_form").attr("action", "javascript:toggle_popup('mj_prompt');");
    $("#goodwork").click(function() {this.style.display = 'none';});
    update_settings();
    round = $(".round_row").length;
    if (round > 0) {
	$("#btn_undo").attr("disabled", false);
    } else {
	$("#btn_undo").attr("disabled", true);
    }
});

function mahjong_selected(player) {
    /**
     * Called when mahjong player is selected
     * Focuses on players point input. If only mahjong player gets points, the other inputs are disabled.
     * @param {number} player The number of the player that got mahjong
     */
    if (settings.points_distribution & 1) { //if only mahjong player gets points 
	$("[name='points']").each(function (i) {
	    if (i == player) {
		this.disabled = false;
	    } else {
		this.disabled = true;
	    }
	});
    }
    $("[name='points']")[player].focus();
}

function clear_inputs() {
    /**
     * Clears all inputs for a new round.
     */
    var point_inputs = $("[name='points']");
    var input_cells = $("[name='input_cell']");
    $("[name='mahjong']").each(function(i) { //loops through all inputs and clears them
	this.checked = false;
	point_inputs[i].value = "";
	if (settings.points_distribution & 1) { //if only mahjong player get points, disable all inputs
	    point_inputs[i].disabled = true;
	}
	if (is_player_active(i)) {
	    input_cells[i].style.visibility = "visible";
	} else {
	    input_cells[i].style.visibility = "hidden";
	}
    });
    point_inputs[0].focus();
}

function is_player_active(player) {
    /**
     * Checks if a player is participating in the current round.
     * @param {number} player The player that should be checked
     * @return {boolean} Returns true if the player is active, otherwise false
     */
    if (player >= settings.no_players) {
	return false;
    } else if (settings.no_players == 5 && settings.fifth_player_pause == "1") {
	var inactive_player = ((wind_players[round] * 1) + 4) % 5;
	return player != inactive_player;
    } else {
	return true;
    }
}

function update_table() {
    /**
     * Calculates all changes for the round and updates the table.
     */
    var goodwork = false;
    $("#goodwork").hide();//style.display = "none";
    var round_points = [];
    var mahjong_player = get_radio_value("mahjong");
    if (mahjong_player == 99) { //checks that mahjong player is selected
	mj_alert(translate("You have to mark who got Mahjong"));
	return;
    }
    var point;
    $("[name='points']").each(function (i) { //gets the points from input
	if ((!(settings.points_distribution & 1) || i == mahjong_player)) { //&& is_player_active(i)) {
	    point = this.value;
	    if (isNaN(point)) { //checks that input is valid
		mj_alert(translate("You can only write numbers in the fields"));
		return;
	    } else if (point == "") {
		mj_alert(translate("You have to fill all the fields"));
		return;
	    } else if (point < 0 || point % 1 != 0) {
		mj_alert(translate("You can only enter positive integers"));
		return;
	    }
	    if (point >= 200) { //if more that 200 points prepare to show the good work image
		goodwork = true;
	    }
	    round_points[i] = 1*point;
	} else {
	    round_points[i] = 0;
	}
    });
    if (goodwork) { //if more that 200 points show the good work image (after all input checks)
	$("#goodwork").show();//style.display = "block";
    }

    $("#mjtbody").load("library/round_controller.php", {action: "input_points", points: round_points.toString(), mahjong: mahjong_player});
    round = $(".round_row").length;
    
    clear_inputs(); //clear inputs
    $("#btn_undo").attr("disabled", false); //enable undo
}

function undo_round() {
    /**
     * Removes the last round and updates the table.
     * Fills the inputs with the input data of last round.
     */

    var point_inputs = $("[name='points']");
    var radios = $("[name='mahjong']");
    
    $(".round_row").last().children().each(function (i) {
	if ($(this).text().charAt(0) == "M") {
	    radios[i].checked = true;
	    point_inputs[i].value = $(this).text().substr(1);
	    point_inputs[i].focus();
	} else {
	    point_inputs[i].value = $(this).text();
	}
    });
    
    $("#mjtbody").load("library/round_controller.php", {action: "undo_round"});

    round -= 1;

    if (round == 0) {
	$("#btn_undo").attr("disabled", true); //Disables undo if first round
    }
}

function update_name(player) {
    /**
     * Updates the name of the specified player.
     * @param {number} player The number of the player that should change name
     */
    mj_prompt(translate("What is the name of the player?"), $("#player_" + player).text(), function () {
	var new_player = sanitize($("#mj_prompt_answer").val());
	if (new_player != null) {
	    $("#player_" + player).html(new_player);
	    $.post("library/settings_controller.php", {action: "update_name", player: player, name: new_player })
	}
    });
}

function restart_game() {
    /**
     * Restarts the game.
     */
    var restart_game_run = function() {
	$("#table").load("library/game_controller.php",  {action: "restart_game", save: true});
	toggle_popup("options");
    };
    if (round > 0) {
	mj_confirm(translate("Are you sure that you want to restart the game? Your current game will not be saved."),
		   restart_game_run);
    } else {
	restart_game_run();
    }
}

function get_wind(wind) {
    var winds = ["東", "南", "西", "北", "X"];
    return winds[wind];
}

function get_radio_value(radio) {
    /**
     * Gets the value of the checked radio button. 
     * If no button is checked, 99 is returned.
     * @param {string} radio The name of the radio buttons to check
     * @return {value} Returns the value of the checked radio button
     */
    var radios = document.getElementsByName(radio);
    for (var i = 0; i < radios.length; i++) {
	if (radios[i].checked) {
	    return radios[i].value;
	}
    }
    return 99;
}

function toggle_popup(puid, close) {
    /**
     * Opens or closes a pop-up pane
     * @param {string} puid Id of the pop-up element
     * @param {boolean} close If true pop-up will only close, not open.
     * @return {boolean} Returns true if pop-up were opened and false if it was closed
     */
    var popup = document.getElementById(puid);
    if (popup.className == "pop-up open") {
	popup.className = "pop-up close";
	return false;
    } else if (!close) {
	popup.className = "pop-up open";
	return true;
    }
}

function update_settings() {
    /**
     * Updates all settings in the options dialog
     */
    $.getJSON("library/settings_controller.php", {action: "get_settings"}, function (data) {
	settings = data;
	document.getElementById("no_players_" + data.no_players).checked = true;
	if (data.no_players == 5) {
	    $("#fifth_player_pause_setting").show();
	} else {
	    $("#fifth_player_pause_setting").hide();
	}
	if (data.fifth_player_pause == 1) {
	    document.getElementById("fifth_player_pause_yes").checked = true;
	} else {
	    document.getElementById("fifth_player_pause_no").checked = true;
	}
	document.getElementById("points_distribution_" + data.points_distribution).checked = true;
	document.getElementById("start_points").value = data.start_points;
    });
}

function update_points_distribution_setting() {
    /**
     * Updates the start points setting value depending on points distribution setting
     */
    var opt_points_distribution = get_radio_value("points_distribution");
    var opt_start_points = $("#start_points").val();
    if((opt_points_distribution & 2) && opt_start_points == 0) {
	$("#start_points").val(2000);
    } else if (!(opt_points_distribution & 2) && opt_start_points == 2000) {
	$("#start_points").val(0);
    }	
}

function save_options() {
    /**
     * Saves changes made in the options dialog
     * @param {boolean} no_questions If true, no questions are asked.
     */
    var opt_no_players = get_radio_value("no_players");
    var opt_fifth_player_pause = settings.fifth_player_pause;
    if (opt_no_players == 5) {
	opt_fifth_player_pause = get_radio_value("fifth_player_pause");
    }
    var opt_points_distribution = get_radio_value("points_distribution");
    var opt_start_points = $("#start_points").val();
    
    if (opt_no_players != settings.no_players || opt_fifth_player_pause != settings.fifth_player_pause || opt_points_distribution != settings.points_distribution || opt_start_points != settings.start_points) {
	var save_options_run = function() {
	    settings.no_players = opt_no_players;
	    settings.fifth_player_pause = opt_fifth_player_pause;
	    settings.points_distribution = opt_points_distribution;
	    settings.start_points = opt_start_points;
	    /*var settings = {
		'no_players': no_players,
		'fifth_player_pause': fifth_player_pause,
		'points_distribution': points_distribution,
		'start_points': start_points
	    };*/
	    $("#table").load("library/settings_controller.php", {action: "set_settings", settings: JSON.stringify(settings), save: true});
	    round = 0;
	    toggle_popup("options");
	};
	if (round > 0) {
	    mj_confirm(translate("Are you sure that you want to save the settings? The game will be restarted."),
		       save_options_run);
	} else {
	    save_options_run();
	}
    } else {
	toggle_popup("options");
    }
}

function show_custom_start() {
    /**
     * Shows or hides the custom start dialog
     */
    if (toggle_popup("custom")) {
	toggle_popup("saveload", true);
	var custom_winds = "";
	var tmp_no_players = get_radio_value("no_players");
	var tmp_fifth_player_pause = get_radio_value("fifth_player_pause");
	for (var i = 0; i < 5; i++) {
	    if (i < tmp_no_players && !(tmp_no_players == 5 && i == 4 && tmp_fifth_player_pause == "1")) {
		document.getElementById("custom_pl" + (i + 1)).style.display = "block";
		document.getElementById("custom_wind_" + i).nextElementSibling.innerHTML = get_wind(i);
		document.getElementById("custom_wind_" + i).nextElementSibling.style.display = "inline-block";
		document.getElementById("custom_wind_" + i).disabled = false;
	    } else {
		document.getElementById("custom_pl" + (i + 1)).style.display = "none";
		document.getElementById("custom_wind_" + i).nextElementSibling.style.display = "none";
		document.getElementById("custom_wind_" + i).disabled = true;
	    }
	}
	update_custom_wind();
    }
}

function update_custom_wind() {
    /**
     * Sets the wind symbol in the wind player radio buttons to currently selected wind.
     */
    for (var i = 1; i <= 5; i++) {
	var lbl = document.getElementById("custom_wind_pl" + i).nextElementSibling;
	lbl.innerHTML = get_wind(get_radio_value("custom_wind"));
    }
}

function custom_start() {
    /**
     * Starts a game with user defined initialization values.
     */
    var tmp_no_players = get_radio_value("no_players");
    var tmp_wind_player = get_radio_value("custom_wind_player");
    if (tmp_wind_player == 99) {
	mj_alert(translate("You have to select a player to have the current wind."));
	return;
    }
    var tmp_wind = get_radio_value("custom_wind");
    if (tmp_wind == 99) {
	mj_alert(translate("You have to select the current wind."));
	return;
    }
    var tmp_points = [];
    for (var i = 0; i < tmp_no_players; i++) {
	tmp_points[i] = $("#custom_start_pl" + (i + 1)).val();
	if (isNaN(tmp_points[i]) || tmp_points[i] % 1 != 0) {
	    mj_alert(translate("You have to enter player points as numbers."));
	    return;
	}
    }
    var custom_start_start = function() {
	round = 0;
	wind = tmp_wind;
	settings.no_players = tmp_no_players
	settings.fifth_player_pause = get_radio_value("fifth_player_pause");
	settings.points_distribution = get_radio_value("points_distribution");
	settings.start_wind = wind;
	settings.start_wind_player = tmp_wind_player - 1;
	for (var i=0; i < no_players; i++) {
	    settings["start_player" + i] = tmp_points[i];
	}
	$("#table").load("library/settings_controller.php", {action: "set_settings", settings: JSON.stringify(settings), save: true});
	show_custom_start();
	toggle_popup("options");
    };
    if (round > 0) {
	mj_confirm(translate("Are you sure that you want to start a new game? Your current game will not be saved."),
		   custom_start_start);
    } else {	
	custom_start_start();
    }
}
			  
function show_saveload() {
    /**
     * Shows or hides the save/load dialog
     */
    if (toggle_popup("saveload")) {
	toggle_popup("custom", true);
	load_saves();
	no_save_selected();
    }
}

function load_saves() {
    /**
     * Loads all saved games to the select
     */
    $("#slct_sl").load("library/load_controller.php", {action: "get_save_games"}, function() {
	$("#slct_sl .option:not(.disabled)").click(function() {
	    $("#slct_sl .option.selected").removeClass("selected");
	    $(this).addClass("selected");
	    $("#slct_sl_value").val($("#slct_sl .option.selected").data("value"));
	    save_selected();
	});
    });
}

function save_selected() {
    /**
     * Set the buttons to disabled or not depending on selected save
     */
    document.getElementById("btn_sl_remove").disabled = false;
    document.getElementById("btn_sl_load").disabled = false;
}

function no_save_selected() {
    /**
     * Set the buttons to disabled or not depending on selected save
     */
    document.getElementById("btn_sl_remove").disabled = true;
    document.getElementById("btn_sl_load").disabled = true;
}

function remove_save() {
    /**
     * Remove the selected saved game from the list
     */
    var id = $("#slct_sl .option.selected").data("value");
    if (!id) {
	return;
    }
    mj_confirm(printf(translate("Are you sure that you want to remove %s?"), id), function() {
	$("#slct_sl").load("library/load_controller.php", {action: "delete_game", id: id});
	nosave_selected();
    });
}

//function load_game() {
    /**
     * Load the selected saved game
     */
//    var id = $("#slct_sl .option.selected").data("value");
//    $("#table").load("library/load_controller.php", {action: "load_game", id: id, save: true});
//    show_saveload();
//    toggle_popup("options");
//}

function mj_alert(str, callback, args) {
    /**
     * Shows a message to the user
     * @param {string} str The message that will be shown
     * @param {function} callback Function to be called when alert is closed (optional)
     * @param {array} args Arguments to callback function
     */
    document.getElementById("mj_alert_text").innerHTML = str;
    toggle_popup("mj_alert");
    if (callback) {
	popup.addEventListener("click", function closeFunc() {
	    callback.apply(this, args);
	    popup.removeEventListener("click", closeFunc);
	    toggle_popup("mj_alert");
	});
    }
}

function mj_confirm(str, callback, args) {
    /**
     * Asks for confirmation from the user
     * @param {string} str The question
     * @param {function} callback Function to be called if confirmed (optional)
     * @param {array} args Arguments to callback function
     */
    document.getElementById("mj_confirm_question").innerHTML = str;
    toggle_popup("mj_confirm");
    if (callback) {
	function okFunc() {
	    callback.apply(this, args);
	    document.getElementById("btn_mj_confirm_ok").removeEventListener("click", okFunc);
	    document.getElementById("btn_mj_confirm_cancel").removeEventListener("click", cancelFunc);
	}
	function cancelFunc() {
	    document.getElementById("btn_mj_confirm_ok").removeEventListener("click", okFunc);
	    document.getElementById("btn_mj_confirm_cancel").removeEventListener("click", cancelFunc);
	}
	document.getElementById("btn_mj_confirm_ok").addEventListener("click",  okFunc);
	document.getElementById("btn_mj_confirm_cancel").addEventListener("click",  cancelFunc);
    }
}

function mj_prompt(str, pre, callback, args) {
    /**
     * Asks a question to the user
     * @param {string} str The question 
     * @param {string} pre The prefilled answer
     * @param {function} callback Function to be called when prompt is closed (optional)
     */
    document.getElementById("mj_prompt_question").innerHTML = str;
    document.getElementById("mj_prompt_answer").value = pre;
    document.getElementById("mj_prompt_answer").focus();
    toggle_popup("mj_prompt");
    if (callback) {
	function okFunc() {
	    callback.apply(this, args);
	    document.getElementById("mj_prompt_form").removeEventListener("submit", okFunc);
	    document.getElementById("btn_mj_prompt_cancel").removeEventListener("click", cancelFunc);
	}
	function cancelFunc() {
	    document.getElementById("mj_prompt_form").removeEventListener("submit", okFunc);
	    document.getElementById("btn_mj_prompt_cancel").removeEventListener("click", cancelFunc);
	}
	document.getElementById("mj_prompt_form").addEventListener("submit",  okFunc);
	document.getElementById("btn_mj_prompt_cancel").addEventListener("click",  cancelFunc);
    }
}

function translate(str) {
    /**
     * Translates a string to the current language
     * @param {string} str The string to be translated
     * @return {string} Return a translated version of the string if exests, otherwise same string is returned
     */
    if (language in MJDictionary && str in MJDictionary[language]) {
	return MJDictionary[language][str];
    } else {
	return str;
    }
}

function translateAll() {
    /**
     * Translates all HTML elements with attribute data-trans
     */
    var elements = document.querySelectorAll("[data-trans]");
    for (var i = 0; i < elements.length; i++) {
	elements[i].innerHTML = translate(elements[i].getAttribute("data-trans"));
    }
}

function sanitize(str) {
    /**
     * Sanitizes a string by removing any characters that are not letters, numbers, ".", "-" or " " 
     * @param {string} str The string to be sanitized
     * @return {value} Returns a sanitized version of the string
     */
    if (str == null) {
	return null;
    }
    return str.replace(/[^a-zA-Z0-9åäöæøéüÅÄÖÆØÉÜ.-\s]/g,"?");
}

function printf(str, insrt) {
    /**
     * Very simplified printf, only works with strings 
     * @param {string} str The string to be formatted
     * @param {string} insrt Value to be inserted
     * @return {value} Returns the string with other string inserted where %s
     */
    return str.replace("%s", insrt);
}

function addZero(nr) {
    /**
     * Adds a "0" to any number below 10
     * @param {number} nr Thu number
     * @return {value} Returns the number with fixed width
     */
    if (nr < 10) {
	return "0" + nr;
    } else {
	return nr;
    }
}

