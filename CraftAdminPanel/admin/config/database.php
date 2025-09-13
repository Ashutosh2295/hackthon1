<?php
// Database configuration
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;
    
    public function __construct() {
        // Use environment variables from Replit
        $database_url = getenv('DATABASE_URL');
        if ($database_url) {
            $db_parts = parse_url($database_url);
            $this->host = $db_parts['host'] ?? 'localhost';
            $this->db_name = ltrim($db_parts['path'] ?? '/neondb', '/');
            $this->username = $db_parts['user'] ?? 'root';
            $this->password = $db_parts['pass'] ?? '';
            $this->port = $db_parts['port'] ?? 5432;
        } else {
            // Fallback for local XAMPP setup
            $this->host = "localhost";
            $this->db_name = "tribal_arts_db";
            $this->username = "root";
            $this->password = "";
            $this->port = "3306";
        }
    }
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            if (getenv('DATABASE_URL')) {
                // PostgreSQL connection for Replit
                $this->conn = new PDO(
                    "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password
                );
            } else {
                // MySQL connection for XAMPP - first try to create database if it doesn't exist
                try {
                    $this->conn = new PDO(
                        "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                        $this->username,
                        $this->password
                    );
                } catch(PDOException $e) {
                    // If database doesn't exist, connect without database and create it
                    if (strpos($e->getMessage(), 'Unknown database') !== false) {
                        $temp_conn = new PDO(
                            "mysql:host=" . $this->host . ";port=" . $this->port,
                            $this->username,
                            $this->password
                        );
                        $temp_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $temp_conn->exec("CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        
                        // Now connect to the created database
                        $this->conn = new PDO(
                            "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                            $this->username,
                            $this->password
                        );
                    } else {
                        throw $e;
                    }
                }
            }
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            return null;
        }
        
        return $this->conn;
    }
}
?>