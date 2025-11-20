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
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);

            if ($this->conn->connect_error) {
                throw new Exception("Database Connection Failed: " . $this->conn->connect_error);
            }

            // Set charset to ensure proper handling of special characters
            $this->conn->set_charset("utf8mb4");
            
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            error_log($this->error); // Log the error
            return false;
        }
    }
}

// Create database connection instance
$db = new db_connect();
if (!$db->connect()) {
    error_log("Failed to connect to database: " . $db->error);
    die("Database connection failed. Please check your configuration.");
}

?>