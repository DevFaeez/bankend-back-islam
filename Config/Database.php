<?php 
namespace Config {
class Database{
    static function getConnection(): \PDO{
        $port = 3306;
        $host = "localhost";
        $username = "root";
        $database = "bank_islam";
        $password = "root";
        
        try {
            $connection = new \PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $connection;
        } catch (\PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw $e;
        }
        }
    }
}

