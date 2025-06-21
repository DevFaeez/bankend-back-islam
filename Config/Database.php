<?php
namespace Config {
    class Database
    {
        static function getConnection()
        {
            $host = "localhost:1521/FREEPDB1";
            $username = "hr";
            $password = "root";

            $conn = oci_connect($username, $password, $host);

            if (!$conn) {
                $e = oci_error();
                die("Connection failed: " . $e['message']);
            }

            return $conn;
        }

    }
}




