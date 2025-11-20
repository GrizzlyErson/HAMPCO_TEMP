<?php
// define("db_host", "localhost");
// define("db_user", "root");
// define("db_pass", "");
// define("db_name", "lunatech");

define("db_host", "localhost");
define("db_user", "root");
define("db_pass", "");
define("db_name", "hampco");

class db_connect
{
    public $host = db_host;
    public $user = db_user;
    public $pass = db_pass;
    public $name = db_name;
    public $conn;
    public $error;
    public $mysqli;

    public function connect()
    {
        try {
            // Create connection without strict mode
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);

            if ($this->conn->connect_error) {
                error_log("Database connection failed: " . $this->conn->connect_error);
                throw new Exception("Database connection failed: " . $this->conn->connect_error);
            }

            // Set charset to ensure proper encoding
            if (!$this->conn->set_charset("utf8mb4")) {
                error_log("Error setting charset: " . $this->conn->error);
                throw new Exception("Error setting charset: " . $this->conn->error);
            }

            // Set SQL mode to ensure proper decimal handling
            if (!$this->conn->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")) {
                error_log("Error setting SQL mode: " . $this->conn->error);
                throw new Exception("Error setting SQL mode: " . $this->conn->error);
            }

            return true;
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            $this->error = $e->getMessage();
            return false;
        }
    }
}

// Create database connection instance
$db = new db_connect();
if (!$db->connect()) {
    error_log("Failed to connect to database: " . $db->error);
    die("Database connection failed: " . $db->error);
}
?>