<?php
/**
 * Database Connection Class
 * AR Homes Posadas Farm Resort Reservation System
 */

require_once 'database.php';

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $charset = DB_CHARSET;
    private $conn;
    private $error;

    /**
     * Create database connection
     */
    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ];

        try {
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die("Database Connection Error: " . $this->error);
        }
    }

    /**
     * Get the database connection
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Get last error message
     */
    public function getError() {
        return $this->error;
    }
}
?>
