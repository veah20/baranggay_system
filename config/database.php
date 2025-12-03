<?php
/**
 * Database Configuration
 * Barangay Information and Reporting System
 */

if (!class_exists('Database')) {
    class Database {
        private $host;
        private $db_name;
        private $username;
        private $password;
        public $conn;

        public function __construct() {
            $this->host = getenv('DB_HOST') ?: "localhost";
            $this->db_name = getenv('DB_NAME') ?: "birs_db";
            $this->username = getenv('DB_USER') ?: "root";
            $this->password = getenv('DB_PASSWORD') ?: "";
        }

        /**
         * Get database connection
         */
        public function getConnection() {
            if ($this->conn !== null) {
                return $this->conn;
            }

            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                
                // Add SSL for Railway MySQL connections
                if (!empty($this->host) && strpos($this->host, 'railway') !== false) {
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                }
                
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                
                // Explicitly set the connection collation
                $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->conn->exec("SET CHARACTER SET utf8mb4");
                
            } catch(PDOException $exception) {
                error_log("Database Connection Error: " . $exception->getMessage());
                die("Database connection failed. Please try again later. Error: " . htmlspecialchars($exception->getMessage()));
            }

            return $this->conn;
        }

        /**
         * Close database connection
         */
        public function closeConnection() {
            $this->conn = null;
        }

        /**
         * Destructor to ensure connection is closed
         */
        public function __destruct() {
            $this->closeConnection();
        }
    }
}
?>
