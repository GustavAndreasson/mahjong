<?php
class User {
    private $conn;
    private $user_id;
    private $game;

    public __construct($conn, $user_id = null, $game_id = null) {
        $this->conn = $conn;

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
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->log_line("Something went wrong when registering user: " . $e->getMessage(), true);
            return false;
        }
    }
    
}