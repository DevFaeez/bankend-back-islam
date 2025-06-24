<?php
namespace Config {
class Database{
    static function getConnection(): \PDO{
        $port = 1521;
        $host = "localhost:1521/FREEPDB1";
        $username = "system";
        $database = "bank_islam";
        $password = "123456";
        
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
 



