<?php
// admin/config/database.php

class Database {
    private $host = "localhost";
    private $db_name = "anikannx_printtg";
    private $username = "anikannx_printtg";
    private $password = "Mur645519!";
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                                 $this->username, 
                                 $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Ошибка подключения: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
?>
