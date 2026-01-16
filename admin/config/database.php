<?php
// admin/config/database.php

class Database {
    private static $instance = null;
    private $host = "localhost";
    private $db_name = "anikannx_printtg";
    private $username = "anikannx_printtg";
    private $password = "Mur645519!";
    private $conn;

    private function __construct() {
        // Private constructor для Singleton
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                                     $this->username,
                                     $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $exception) {
                echo "Ошибка подключения: " . $exception->getMessage();
            }
        }

        return $this->conn;
    }

    // Запрет клонирования и десериализации
    private function __clone() {}
    public function __wakeup() {}
}
?>
