<?php
class Database {
    public $conn;
    private $pdo;
    private $host = 'localhost';
    private $dbname = 'hampco';
    private $username = 'root';
    private $password = '';

    public function __construct() {
        $this->connectMySQLi();
        $this->connectPDO();
    }

    private function connectMySQLi() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            error_log("MySQLi Connection failed: " . $this->conn->connect_error);
            die("Connection failed. Please try again later.");
        }
    }

    private function connectPDO() {
        try {
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec("SET NAMES utf8");
        } catch(PDOException $e) {
            error_log("PDO Connection failed: " . $e->getMessage());
            die("Connection failed. Please try again later.");
        }
    }

    public function getPDO() {
        return $this->pdo;
    }

    public function getMySQLi() {
        return $this->conn;
    }

    public function check_account($id, $type) {
        if ($type === 'member') {
            $query = "SELECT * FROM user_member WHERE id = ? AND status = 1";
        } else {
            $query = "SELECT * FROM user_admin WHERE id = ? AND status = 1";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
} 