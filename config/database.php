<?php
 
class Database
{
    private $host     = 'localhost';
    private $db_name  = 'laundry_system';
    private $username = 'root';
    private $password = '';
 
    public $conn;
 
    public function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
 
        try {
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }
}
 
