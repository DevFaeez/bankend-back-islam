<?php
namespace Config;

class Database
{
    public static function getConnection()
    {
        $host = "localhost:1521/FREEPDB1";
        $username = "hr";
        $password = "root";

        $conn = oci_connect($username, $password, $host, 'AL32UTF8');

        if (!$conn) {
            $e = oci_error();
            die("❌ OCI8 Connection Failed: " . $e['message']);
        }

        return $conn;
    }
}

