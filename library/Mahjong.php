<?php
class Mahjong {
    private $conn;
    private $game_id;
    private $settings;
    private $standard_player_names;

    public function __construct() {
        $this->standard_player_names = array("Spelare 1","Spelare 2","Spelare 3","Spelare 4","Spelare 5");
        
        $servername = "localhost";
        $username = "mahjong";
        $password = "mahjongpwd";

        try {
            $this->conn = new PDO("mysql:host=$servername;dbname=mahjong", $username, $password);
            // set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            $this->log_line("Connection failed: " . $e->getMessage(), true);
        }

        session_start(['cookie_lifetime' => 86400 * 30]);

        if (isset($_SESSION["USER_ID"])) {
            $this->user_id = $_SESSION["USER_ID"];
            $this->game_id = $_SESSION["GAME_ID"];
            $this->is_logged_in = $_SESSION["IS_LOGGED_IN"];
            $this->load_settings();
        } else {
            $this->create_new_session();
        }
    }

    public function __destruct() {
        $this->conn = null;
    }

    public function log_line($txt, $error = false) {
        if ($error) {
            $file = LOGS_PATH . "ERROR.LOG";
        } else {
            $file = LOGS_PATH . "DEBUG.LOG";
        }
        file_put_contents($file, date("Y-m-d H:i:s") . ":" . $txt . "\n", FILE_APPEND | LOCK_EX);
    }

    private function create_new_session() {
        $this->set_standard_settings();
        $this->is_logged_in = false;
        $_SESSION["IS_LOGGED_IN"] = false;
        $this->create_user();
    }

    private function create_user() {
        try {
            $sql = "INSERT INTO users (user_id) ";
            $sql .= "VALUES (null)";
            $this->conn->exec($sql);
            $this->user_id = $this->conn->lastInsertId();
            $_SESSION["USER_ID"] = $this->user_id;

            $this->create_new_game();
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when creating new user: " . $e->getMessage(), true);
        }
    }

    public function login($name, $password) {
        try {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "SELECT password, user_id, current_game FROM users WHERE name = '$name'";
            $result = $this->conn->query($sql)->fetch(PDO::FETCH_ASSOC);
            if ($result && password_verify($password, $result['password'])) {
                $this->delete_user();
                $this->user_id = $result['user_id'];
                $this->game_id = $result['current_game'];
                $this->load_settings();
                $this->is_logged_in = true;
                $_SESSION["USER_ID"] = $this->user_id; 
                $_SESSION["GAME_ID"] = $this->game_id; 
                $_SESSION["IS_LOGGED_IN"] = true; 
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when logging in: " . $e->getMessage(), true);
            return false;
        } 
    }

    public function register($name, $password) {
        try {
            if ($name = "" || $password = "") {
                return false;
            }
            $sql = "SELECT user_id FROM users WHERE name = '$name'";
            $result = $this->conn->query($sql)->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET name = '$name', password = '$pass_hash' WHERE user_id = {$this->user_id}";
                $this->conn->exec($sql);
                $this->is_logged_in = true;
                $_SESSION["IS_LOGGED_IN"] = true; 
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when registering user: " . $e->getMessage(), true);
            return false;
        }
    }

    public function logout() {
        $this->create_new_session();
    }

    public function is_logged_in() {
        return $this->is_logged_in;
    }

    private function delete_user() {
        try {
            $this->delete_game($this->game_id);
            $sql = "DELETE FROM users WHERE user_id = {$this->user_id}";
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when deleting user: " . $e->getMessage(), true);
        }   
    }

    public function get_user_language() {
        return null;
    }

    private function create_new_game() {
        try {
            $now = date("Y-m-d H:i:s");
            $sql = "INSERT INTO games (user_id, start_date, last_saved) ";
            $sql .= "VALUES ('{$this->user_id}', '$now', '$now')";
            $this->conn->exec($sql);
            $this->game_id = $this->conn->lastInsertId();
            $_SESSION["GAME_ID"] = $this->game_id;
            $sql = "UPDATE users SET current_game={$this->game_id} WHERE user_id={$this->user_id}";
            $this->conn->exec($sql);

            $this->update_settings(); //?
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when creating new game: " . $e->getMessage(), true);
        }
    }

    private function set_standard_settings() {
        $this->settings["no_players"] = 4;
        $this->settings["points_distribution"] = 2;
        $this->settings["start_points"] = 2000;
    }

    private function game_updated() {
        try {
            $now = date("Y-m-d H:i:s");
            $sql = "UPDATE games SET last_saved = '$now' WHERE game_id = {$this->game_id}";
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when updating timestamp: " . $e->getMessage(), true);
        }
    }

    private function update_settings() {
        try {
            $sql = "INSERT INTO settings (game_id, setting_key, value) VALUES ";
            foreach($this->settings as $key=>$value) {
                $sql .= "({$this->game_id}, '$key', $value),";
            }
            $sql = substr($sql, 0, -1);
            $sql .= " ON DUPLICATE KEY UPDATE setting_key=VALUES(setting_key), value=VALUES(value)";
            $this->conn->exec($sql);
            $this->game_updated();
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when updating settings: " . $e->getMessage(), true);
        }
    }
    
    private function load_settings() {
        $sql = "SELECT setting_key, value from settings WHERE game_id = {$this->game_id}";
        try {
            foreach($this->conn->query($sql) as $row) {
                $this->settings[$row["setting_key"]] = $row["value"];
            }
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when collecting settings: " . $e->getMessage(), true);
        }
    }
    
    public function undo_round() {
        try {
            $sql = "DELETE s1.* FROM scoreboard s1 ";
            $sql .= "JOIN (SELECT MAX(round) AS max_round FROM scoreboard WHERE game_id = {$this->game_id}) s2 ";
            $sql .= "WHERE s1.game_id = {$this->game_id} AND s1.round = s2.max_round";
            $this->conn->exec($sql);
            $this->game_updated();
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when updating scoreboard: " . $e->getMessage(), true);
        }
    }

    public function update_name($player, $name) {
        try {
            $sql = "INSERT INTO player_names (game_id, player_nr, name) VALUES ";
            $sql .= "({$this->game_id}, $player, '$name') ";
            $sql .= "ON DUPLICATE KEY UPDATE name=VALUES(name)";
            $this->conn->exec($sql);
            $this->game_updated();
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when updating player name: " . $e->getMessage(), true);
        }
    }

    public function get_settings() {
        return json_encode($this->settings);
    }

    public function set_settings($settings) {
        foreach ($settings as $setting => $value) {
            $this->settings[$setting] = $value;
        }
        $this->update_settings();
    }

    public function delete_game($id) {
        try {
            $sql = "DELETE FROM scoreboard WHERE game_id = {$id}";
            $this->conn->exec($sql);
            $sql = "DELETE FROM settings WHERE game_id = {$id}";
            $this->conn->exec($sql);
            $sql = "DELETE FROM player_names WHERE game_id = {$id}";
            $this->conn->exec($sql);
            $sql = "DELETE FROM games WHERE game_id = {$id}";
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when deleting game: " . $e->getMessage(), true);
        }
    }

    public function restart_game($save) {
        try {
            $old_game_id = $this->game_id;
            unset($this->settings["start_wind"]);
            unset($this->settings["start_wind_player"]);
            unset($this->settings["start_player0"]);
            unset($this->settings["start_player1"]);
            unset($this->settings["start_player2"]);
            unset($this->settings["start_player3"]);
            unset($this->settings["start_player4"]);
            $this->create_new_game();
            if (!$save || !$this->is_logged_in) {
                $this->delete_game($old_game_id);
            }
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when restarting game: " . $e->getMessage(), true);
        }
    }

    public function load_game($id, $save) {
        try {
            $old_game_id = $this->game_id;
            $sql = "UPDATE users ";
            $sql .= "SET current_game = $id ";
            $sql .= "WHERE user_id = {$this->user_id}";
            $this->conn->exec($sql);
            if (!$save || !$this->is_logged_in) {
                $this->delete_game($old_game_id);
            }
            $this->game_id = $id;
            $_SESSION["GAME_ID"] = $this->game_id;
            $this->load_settings();
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when loading game: " . $e->getMessage(), true);
        }
    }

    public function get_save_games() {
        try {
            $sql = "SELECT g.game_id, GROUP_CONCAT(p.name SEPARATOR ',') as names, g.start_date, g.last_saved FROM games g ";
            $sql .= "JOIN settings s ON s.game_id = g.game_id AND s.setting_key = 'no_players' ";
            $sql .= "LEFT JOIN player_names p ON p.game_id = g.game_id AND p.player_nr < s.value ";
            $sql .= "WHERE user_id = {$this->user_id} ";
            $sql .= "GROUP BY g.game_id ORDER BY g.last_saved DESC";
            $html = "";
            foreach($this->conn->query($sql) as $row) {
                $html .= "<div class='option";
                if ($row['game_id'] == $this->game_id) {
                    $html .= " disabled";
                }
                $html .= "' data-value='";
                $html .= $row['game_id'] . "'";
                $html .= "><span class='save_names'>";
                $html .= $row['names'] . "</span><span class='save_date'>" . $row['last_saved'];
                $html .= "</span></div>";
            }
            return $html;
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when getting save games: " . $e->getMessage(), true);
        }
    }

    public function get_table() {
        $html = "<div class='mjtable'><div class='head'><div>";
        
        $sql = "SELECT player_nr, name from player_names WHERE game_id = {$this->game_id}"; 
        try {
            $result = $this->conn->query($sql)->fetchAll(PDO::FETCH_UNIQUE);
            for ($i = 0; $i < $this->settings["no_players"]; $i++) {
                $html .= "<div id='player_" . $i . "' onclick='update_name(";
                if ($result && key_exists($i, $result)) {
                    $html .= $i . ")'>" . $result[$i]["name"] . "</div>";
                } else {
                    $html .= $i . ")'>" . $this->standard_player_names[$i] . "</div>";
                }
            }
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when collecting player names: " . $e->getMessage(), true);
        }
        $html .= "</div></div><div class='foot'><div>";
        for($i = 0; $i < $this->settings["no_players"]; $i++) {
            $html .= "<div name='input_cell'><span class='token'>";
            $html .= "<input type='radio' name='mahjong' value='$i' id='mahjong_";
            $html .= "$i' onclick='mahjong_selected($i)'/><label for='mahjong_";
            $html .= "$i'>M</label></span>";
            $html .= "<input type='number' step='1' maxLength='3' name='points' size='3' ";
            $html .= "/></div>";
        }
        $html .= "</div></div><div id='mjtbody'>";
        $html .= $this->get_scoreboard();
        $html .= "</div></div>";
        return $html;
    }
    
    public function get_scoreboard() {
        $player_points = array();
        if (array_key_exists("start_wind_player", $this->settings)) {
            $wind_player = $this->settings["start_wind_player"];
        } else {
            $wind_player = 0;
        }
        if (array_key_exists("start_wind", $this->settings)) {
            $wind = $this->settings["start_wind"];
        } else {
            $wind = 0;
        }
        $html = "";
        for ($i = 0; $i < $this->settings["no_players"]; $i++) {
            if (array_key_exists("start_player" . $i, $this->settings)) {
                $player_points[] = $this->settings["start_player" . $i];
            } else {
                $player_points[] = $this->settings["start_points"];
            }
        }
        $html = $this->get_points_row($player_points, $wind_player, $wind);
        try {
            $sql = "SELECT * FROM scoreboard WHERE game_id = {$this->game_id}";
            foreach($this->conn->query($sql) as $row) {
                $round_points = array();
                for ($i = 0; $i < $this->settings["no_players"]; $i++) {
                    $round_points[] = $row["player" . $i];
                }
                $html .= $this->get_round_row($round_points, $row["mahjong"], $wind_player);
                $transactions = $this->get_transactions($round_points, $row["mahjong"], $wind_player);
                foreach ($transactions as $i=>$point) {
                    $player_points[$i] += $point; 
                }
                $html .= $this->get_transactions_row($transactions, $wind_player);

                if ($wind_player != $row["mahjong"]) { //updates wind player and wind for the next round
                    $wind_player = ($wind_player + 1) % $this->settings["no_players"];
                    if ($wind_player == 0) {
                        if ($this->settings["no_players"] == 5 && $this->settings["fifth_player_pause"] == 1 && $wind == 3) {
                            $wind = 0;
                        } else {
                            $wind = ($wind + 1) % $this->settings["no_players"];
                        }
                    }
                }
                
                $html .= $this->get_points_row($player_points, $wind_player, $wind);
            }

        } catch (PDOException $e) {
            $this->log_line("Something went wrong when collecting scoreboard: " . $e->getMessage(), true);
        }
        return $html;
    }

    private function get_points_row($player_points, $wind_player, $wind) {
        $html = "<div class='points_row'>";
        foreach ($player_points as $i=>$point) {
            $html .= "<div>";
            if ($i == $wind_player) {
                $html .= "<span class='token'>" . $this->get_wind($wind) . "</span>";
            }
            $html .= $point . "</div>";
        }
        $html .= "</div>";
        return $html;
    }

    private function get_round_row($round_points, $mahjong_player, $wind_player) {
    /**
     * Creates a row with round points for the mahjong table.
     * @param {number[]} round_points An array with the points of the current round
     * @return {string} Returns the html for the round row
     */
        $html = "<div class='round_row'>";
        foreach ($round_points as $i=>$point) {
            if ($this->is_player_active($i, $wind_player)) {
                $html .= "<div>";
                if ($i == $mahjong_player) {
                    $html .= "<span class='token'>M</span>";
                }
                $html .= $point . "</div>";
            } else {
                $html .= "<div class='inactive'></div>";
            }
        }
        $html .= "</div>";
        return $html;
    }
    
    private function get_transactions_row($transactions, $wind_player) {
    /**
     * Creates a row with transaction points for the mahjong table.
     * @param {number[]} round_points An array with the transactions of the current round
     * @return {string} Returns the html for the transaction row
     */
        $html = "";
        if ($this->settings["points_distribution"] & 2) {
            $html = "<div class='transaction_row'>";
            foreach ($transactions as $i=>$point) {
                if ($this->is_player_active($i, $wind_player)) {
                    $html .= "<div>" . $point . "</div>";
                } else {
                    $html .= "<div class='inactive'></div>";
                }
            }
            $html .= "</div>";
        } 
        return $html;
    }

    private function get_transactions($round_points, $mahjong_player, $wind_player) {
    /**
     * Calculates the transactions between players based on the round points, mahjong player and wind player
     * @param {number[]} round_points An array with the points of the current round
     * @return {number[]} Returns an array with the resulting transactions
     */
        $transactions = array();
        foreach ($round_points as $i=>$ipoint) { //loop through players
            $transactions[$i] = 0;
            if ($this->settings["points_distribution"] & 2) { // players pay eachother
                foreach ($round_points as $j=>$jpoint) { //loop through opponents
                    if ($i != $j && $this->is_player_active($i, $wind_player) && $this->is_player_active($j, $wind_player)) {
                        $transaction = 0;
                        if ($j == $mahjong_player) { //if opponent got mahjong subtract opponents points
                            $transaction -= $jpoint;
                        } else if ($i == $mahjong_player) { //if player got mahjong add player points
                            $transaction += $ipoint;
                        } else { //if neither got mahjong add player points and subtract opponent points
                            if (!($this->settings["points_distribution"] & 1)) { //do not change points if mode is that only mahjong gets points
                                $transaction += $ipoint - $jpoint;
                            }
                        }
                        if ($i == $wind_player || $j == $wind_player) { //if player or opponent is the wind player, double transaction
                            $transaction *= 2;
                            }
                        $transactions[$i] += $transaction;
                    }
                }
            } else { // players just get points
                if ((!($this->settings["points_distribution"] & 1) || $i == $mahjong_player) && $this->is_player_active($i, $wind_player)) { //if only mahjong gets points mode, only give points to mahjong
                    $transactions[$i] = $ipoint;
                }
            }
        }
        return $transactions;
    }
    
    private function is_player_active($player, $wind_player) {
        if ($player >= $this->settings["no_players"]) {
            return false;
        } else if ($this->settings["no_players"] == 5) {
            if ($this->settings["fifth_player_pause"] == 0) {
                $inactive_player = ($wind_player + 4) % 5;
                return $player != $inactive_player;
            }
            return true;
        } else {
            return true;
        }
    }
    
    private function get_wind($wind) {
        $winds = ["東", "南", "西", "北", "X"];
        return $winds[$wind];
    }

    public function input_points($points, $mahjong) {
        try {
            $sql = "SELECT MAX(round) FROM scoreboard WHERE game_id = {$this->game_id}";
            $round = intval($this->conn->query($sql)->fetchColumn());
            $round += 1;
            
            $values_string = "{$this->game_id}, $round";
            $columns_string = "game_id, round";
            foreach($points as $id=>$point) {
                $values_string .= ", $point";
                $columns_string .= ", player$id";
            }
            $values_string .= ", $mahjong";
            $columns_string .= ", mahjong";
            $sql = "INSERT INTO scoreboard ($columns_string) VALUES ($values_string)";
            $this->conn->exec($sql);
            $this->game_updated();
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when updating scoreboard: " . $e->getMessage(), true);
        }
    }
    
}

