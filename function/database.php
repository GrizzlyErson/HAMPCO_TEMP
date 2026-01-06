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
        $this->run_migrations();
    }

    private function run_migrations() {
        $migration_flag_file = __DIR__ . '/../.migration_weaver_fields.done';

        if (!file_exists($migration_flag_file)) {
            // Check if columns exist before altering
            $result = $this->conn->query("SHOW COLUMNS FROM `member_self_tasks` LIKE 'length_m'");
            if ($result && $result->num_rows == 0) {
                 $this->conn->query("ALTER TABLE `member_self_tasks`
                    ADD COLUMN `length_m` DECIMAL(10,2) DEFAULT NULL,
                    ADD COLUMN `width_in` DECIMAL(10,2) DEFAULT NULL,
                    ADD COLUMN `quantity` INT(11) DEFAULT NULL");
            }
            
            // Create the flag file to prevent it from running again
            file_put_contents($migration_flag_file, 'Migration for weaver fields completed on ' . date('Y-m-d H:i:s'));
        }
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

    public function updateMemberDetails($id, $fullname, $contact, $password = null) {
        $updateFields = ['fullname = ?', 'phone = ?'];
        $params = [$fullname, $contact];
        $paramTypes = 'ss'; // string, string

        if ($password !== null) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateFields[] = 'password = ?';
            $params[] = $hashedPassword;
            $paramTypes .= 's'; // add string for password
        }

        $query = "UPDATE user_member SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $params[] = $id;
        $paramTypes .= 'i'; // add integer for id

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return ['success' => false, 'error' => $this->conn->error];
        }

        $stmt->bind_param($paramTypes, ...$params);

        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            error_log("Failed to execute statement: " . $stmt->error);
            return ['success' => false, 'error' => $stmt->error];
        }
    }
}